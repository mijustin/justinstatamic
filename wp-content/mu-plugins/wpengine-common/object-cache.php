<?php
/*
Version: 2.0.2 + WPEngine mods
Based on: http://wordpress.org/extend/plugins/memcached/
Original Authors: Ryan Boren, Denis de Bernardy, Matt Martz
Modifications: Jason Cohen, Sean O'Shaughnessy

Install this file to wp-content/object-cache.php
 */

function wpe_oc_active_notice() {
    $class = "error";
    $message = "WARNING: The WPE object caching file has been found on the staging site, and has just been removed. Please be sure to purge the cache on your production site to ensure that there are no issues.";
    echo "<div class=\"$class\"> <p>$message</p> </div>";
}

function wpe_oc_staging_delete() {
    unlink( __FILE__ );
}

if ( isset( $_SERVER["IS_WPE_SNAPSHOT"] ) ) {
    add_action( 'admin_notices', 'wpe_oc_active_notice' );
    add_action( 'admin_init', 'wpe_oc_staging_delete' );
}

if ( !defined( 'WP_CACHE_KEY_SALT' ) ) {
    define( 'WP_CACHE_KEY_SALT', '' );
}

function wp_cache_add($key, $data, $flag = '', $expire = 0) {
    global $wp_object_cache;

    return $wp_object_cache->add($key, $data, $flag, $expire);
}

function wp_cache_incr($key, $n = 1, $flag = '') {
    global $wp_object_cache;

    return $wp_object_cache->incr($key, $n, $flag);
}

function wp_cache_decr($key, $n = 1, $flag = '') {
    global $wp_object_cache;

    return $wp_object_cache->decr($key, $n, $flag);
}

function wp_cache_close() {
    global $wp_object_cache;

    return $wp_object_cache->close();
}

function wp_cache_delete($id, $flag = '') {
    global $wp_object_cache;

    return $wp_object_cache->delete($id, $flag);
}

function wp_cache_flush() {
    global $wp_object_cache;

    return $wp_object_cache->flush();
}

function wp_cache_get($id, $flag = '') {
    global $wp_object_cache;

    return $wp_object_cache->get($id, $flag);
}

function wp_cache_init() {
    global $wp_object_cache;
    $wp_object_cache = new WP_Object_Cache();
}

function wp_cache_replace($key, $data, $flag = '', $expire = 0) {
    global $wp_object_cache;

    return $wp_object_cache->replace($key, $data, $flag, $expire);
}

function wp_cache_set($key, $data, $flag = '', $expire = 0) {
    global $wp_object_cache;

    if ( defined('WP_INSTALLING') == false )
        return $wp_object_cache->set($key, $data, $flag, $expire);
    else
        return $wp_object_cache->delete($key, $flag);
}

function wp_cache_add_global_groups( $groups ) {
    global $wp_object_cache;

    $wp_object_cache->add_global_groups($groups);
}

function wp_cache_add_non_persistent_groups( $groups ) {
    global $wp_object_cache;

    $wp_object_cache->add_non_persistent_groups($groups);
}

function wpe_enable_object_cache() {
    global $wp_object_cache;

    $wp_object_cache->set_cache_enabled(true);
}

function wpe_disable_object_cache() {
    global $wp_object_cache;

    $wp_object_cache->set_cache_enabled(false);
}

class WP_Object_Cache {
    var $global_groups = array ('users', 'userlogins', 'usermeta', 'site-options', 'site-lookup', 'blog-lookup', 'blog-details', 'rss');

    var $no_mc_groups = array( 'comment', 'counts' );

    var $autoload_groups = array ('options');

    var $cache = array();
    var $mc = array();
    var $stats = array();
    var $group_ops = array();

    var $cache_enabled = true;
    var $default_expiration = 600;
    var $mcrouter_prefix = '';

    function is_cache_enabled($group) {
        return ($this->cache_enabled or in_array($group, $this->autoload_groups));
    }

    function add($id, $data, $group = 'default', $expire = 0) {
        if ( !$this->is_cache_enabled($group) ) {
            return false;
        }

        $key = $this->key($id, $group);

        if ( in_array($group, $this->no_mc_groups) ) {
            $this->cache[$key] = $data;
            return true;
        } elseif ( isset($this->cache[$key]) && $this->cache[$key] !== false ) {
            return false;
        }

        $mc =& $this->get_mc($group);
        $expire = $this->convert_expire_time($expire);
        $result = $mc->add($key, $data, false, $expire);

        if ( false !== $result ) {
            @ ++$this->stats['add'];
            $this->group_ops[$group][] = "add $id";
            $this->cache[$key] = $data;
        }

        return $result;
    }

    function add_global_groups($groups) {
        if ( ! is_array($groups) )
            $groups = (array) $groups;

        $this->global_groups = array_merge($this->global_groups, $groups);
        $this->global_groups = array_unique($this->global_groups);
    }

    function add_non_persistent_groups($groups) {
        if ( ! is_array($groups) )
            $groups = (array) $groups;

        $this->no_mc_groups = array_merge($this->no_mc_groups, $groups);
        $this->no_mc_groups = array_unique($this->no_mc_groups);
    }

    function incr($id, $n, $group = 'default') {
        if ( !$this->is_cache_enabled($group) ) {
            return false;
        }
        $key = $this->key($id, $group);
        $mc =& $this->get_mc($group);

        return $mc->increment($key, $n);
    }

    function decr($id, $n, $group = 'default') {
        if ( !$this->is_cache_enabled($group) ) {
            return false;
        }
        $key = $this->key($id, $group);
        $mc =& $this->get_mc($group);

        return $mc->decrement($key, $n);
    }

    function close() {
        foreach ( $this->mc as $bucket => $mc )
            $mc->close();
    }

    function delete($id, $group = 'default') {
        $key = $this->key($id, $group);

        if ( in_array($group, $this->no_mc_groups) ) {
            unset($this->cache[$key]);
            return true;
        }

        $mc =& $this->get_mc($group);

        $result = $mc->delete($key);

        @ ++$this->stats['delete'];
        $this->group_ops[$group][] = "delete $id";

        if ( false !== $result )
            unset($this->cache[$key]);

        return $result;
    }

    function flush() {
        $ret = true;
        foreach ( $this->mc as $bucket => $mc ) {
            if ($this->reset_generation($bucket) === false) {
                syslog(LOG_WARNING, "[WPE] Memcache generation reset failed for $bucket. Performing full flush.");
                $mc->flush();
                $ret &= $this->reset_generation($bucket);
            }
        }
        return $ret;
    }


    function get($id, $group = 'default') {
        if ( !$this->is_cache_enabled($group) ) {
            return false;
        }
        $key = $this->key($id, $group);
        $mc =& $this->get_mc($group);

        if ( isset($this->cache[$key]) )
            $value = $this->cache[$key];
        else if ( in_array($group, $this->no_mc_groups) )
            $value = false;
        else
            $value = $mc->get($key);

        @ ++$this->stats['get'];
        $this->group_ops[$group][] = "get $id";

        if ( NULL === $value )
            $value = false;

        $this->cache[$key] = $value;

        if ( 'checkthedatabaseplease' == $value )
            $value = false;

        return $value;
    }

    function get_multi( $groups ) {
        /*
        format: $get['group-name'] = array( 'key1', 'key2' );
         */
        $return = array();
        if ( !$this->is_cache_enabled($group) ) {
            foreach ( $groups as $group => $ids ) {
                foreach ( $ids as $id ) {
                    $key = $this->key($id, $group);
                    $return[$key] = false;
                }
            }
            return $return;
        }
        foreach ( $groups as $group => $ids ) {
            $mc =& $this->get_mc($group);
            foreach ( $ids as $id ) {
                $key = $this->key($id, $group);
                if ( isset($this->cache[$key]) ) {
                    $return[$key] = $this->cache[$key];
                    continue;
                } else if ( in_array($group, $this->no_mc_groups) ) {
                    $return[$key] = false;
                    continue;
                } else {
                    $return[$key] = $mc->get($key);
                }
            }
            if ( $to_get ) {
                $vals = $mc->get_multi( $to_get );
                $return = array_merge( $return, $vals );
            }
        }
        @ ++$this->stats['get_multi'];
        $this->group_ops[$group][] = "get_multi $id";
        $this->cache = array_merge( $this->cache, $return );
        return $return;
    }

    function key($key, $group) {
        $group = $group ?: 'default';
        $prefix = $this->prefix_for_group($group);
        $bucket = $this->bucket_for_group($group);
        $generation = $this->get_generation($bucket);
        $key =  preg_replace( '/\s+/', '', "v1:" . WP_CACHE_KEY_SALT . "$prefix$group:$key:$generation");
		return $this->mcrouter_prefix . $key;
    }

    function replace($id, $data, $group = 'default', $expire = 0) {
        if ( !$this->is_cache_enabled($group) ) {
            return false;
        }
        $key = $this->key($id, $group);
        $expire = $this->convert_expire_time($expire);
        $mc =& $this->get_mc($group);
        $result = $mc->replace($key, $data, false, $expire);
        if ( false !== $result )
            $this->cache[$key] = $data;
        return $result;
    }

    function set($id, $data, $group = 'default', $expire = 0) {
        if ( !$this->is_cache_enabled($group) ) {
            return true; // Pretend that we added it
        }
         
        $key = $this->key($id, $group);
        if ( isset($this->cache[$key]) && ('checkthedatabaseplease' == $this->cache[$key]) )
            return false;
        $this->cache[$key] = $data;

        if ( in_array($group, $this->no_mc_groups) )
            return true;

        $expire = $this->convert_expire_time($expire);
        $mc =& $this->get_mc($group);
        $result = $mc->set($key, $data, false, $expire);

        return $result;
    }

    function colorize_debug_line($line) {
        $colors = array(
            'get' => 'green',
            'set' => 'purple',
            'add' => 'blue',
            'delete' => 'red');

        $cmd = substr($line, 0, strpos($line, ' '));

        $cmd2 = "<span style='color:{$colors[$cmd]}'>$cmd</span>";

        return $cmd2 . substr($line, strlen($cmd)) . "\n";
    }

    function stats() {
        echo "<p>\n";
        foreach ( $this->stats as $stat => $n ) {
            echo "<strong>$stat</strong> $n";
            echo "<br/>\n";
        }
        echo "</p>\n";
        echo "<h3>Memcached:</h3>";
        foreach ( $this->group_ops as $group => $ops ) {
            if ( !isset($_GET['debug_queries']) && 500 < count($ops) ) {
                $ops = array_slice( $ops, 0, 500 );
                echo "<big>Too many to show! <a href='" . add_query_arg( 'debug_queries', 'true' ) . "'>Show them anyway</a>.</big>\n";
            }
            echo "<h4>$group commands</h4>";
            echo "<pre>\n";
            $lines = array();
            foreach ( $ops as $op ) {
                $lines[] = $this->colorize_debug_line($op);
            }
            print_r($lines);
            echo "</pre>\n";
        }

        if ( $this->debug )
            var_dump($this->memcache_debug);
    }

    function &get_mc($group = 'default') {
        if ( isset($this->mc[$group]) )
            return $this->mc[$group];
        return $this->mc['default'];
    }

    function failure_callback($host, $port) {
        error_log("Connection failure for $host:$port\n", 3, '/tmp/memcached.txt');
    }

    private function prefix_for_group($group) {
        if ( false !== array_search($group, $this->global_groups) )
            $prefix = $this->global_prefix;
        else
            $prefix = $this->blog_prefix;

        return $prefix;
    }

    private function bucket_for_group($group) {
        if (isset($this->mc[$group])) {
            return $group;
        }
        return 'default';
    }

    private function reset_generation($bucket) {
        $this->mc[$bucket]->delete($this->generation_key());
        $this->generation[$bucket] = microtime() . rand(0, PHP_INT_MAX);
        return $this->mc[$bucket]->set($this->generation_key(), $this->generation[$bucket]);
    }

    private function generation_key() {
        if (! defined('WPE_OBJECT_CACHE_GENERATION_PREFIX')) {
            define('WPE_OBJECT_CACHE_GENERATION_PREFIX', 'wpe_generation:');
        }
        $key = WPE_OBJECT_CACHE_GENERATION_PREFIX . $this->customer;
        return $key;
    }

    private function get_generation($bucket) {
        if (isset($this->generation[$bucket])) {
            return $this->generation[$bucket];
        }

        // Attempt to load the generation from memcache. If it's not present, then the entire
        // cache for this blog has been invalidated, so reset to a new generation.
        $this->generation[$bucket] = $this->mc[$bucket]->get($this->generation_key());
        if ($this->generation[$bucket] === false) {
            $this->reset_generation($bucket);
        }
        return $this->generation[$bucket];
    }

    private function convert_expire_time($expire) {
        $expire = ($expire == 0) ? $this->default_expiration : $expire;
        # Memcached treats expiration times over 30 days as Unix Time. Because of this, if
        # a user tries to set wp_cache to over 30 days, we need to convert it.
        if ( $expire > 30 * DAY_IN_SECONDS ) {
                $expire = time() + $expire;
        }
        return $expire;
    }

    function set_cache_enabled($enabled) {
        // Can be used to turn off object caching for a particular request
        $this->cache_enabled = $enabled;
    }

    static public function get_site_cache_key($file_path){
        // this value is used for the object cache generation key
        // get the site name, if not at least get a unique value by creating a md5 sum from the path
        // therefore, this is necessary because either way we will always have a unique value for that site
        // this function is also in object-cache-new.php so update it there if you modify this function
        $patterns = array(
            "#^/nas/wp/www/(?:sites|staging|cluster-(?:[\d]+))/([^/]+)#",
            "#^/nas/content/(?:live|staging)/([^/]+)#"
        );
        foreach ($patterns as $site_pattern) {
            // Check for a matching install
            $result = preg_match($site_pattern, $file_path, $matches);
            if ($result && isset($matches[1])) {
                return $matches[1];
            }
        }
        return md5($file_path); 
    }

    function __construct() {
    	
    	global $memcached_servers;
    	global $mcrouter_server;

    	// get appropriate bucket
    	if ($mcrouter_server && array_key_exists('host', $mcrouter_server) && array_key_exists('port', $mcrouter_server)) {
    		// use mcrouter host and port if the global mcrouter is set
    		$buckets = array("{$mcrouter_server['host']}:{$mcrouter_server['port']}");
    		$this->mcrouter_prefix = '/rep/all/';
    	} else {
		if ($mcrouter_server) {
			// we should be using mcrouter, but we can't
                	error_log('[wpengine] Pod set to use mcrouter but host and port names are not specified in site config.json. Falling back to no replication.');
		}
			
    		if ( isset($memcached_servers) ) {
    			$buckets = $memcached_servers;
    		} else {
    			$buckets = array('unix:///tmp/memcached.sock');
    		}
    	}
    	
    	reset($buckets);
    	if ( is_int(key($buckets)) )
    		$buckets = array('default' => $buckets);

        foreach ( $buckets as $bucket => $servers) {
            $this->mc[$bucket] = new Memcache();
            foreach ( $servers as $server  ) {
                if ( substr( $server, 0, 5) == "unix:" ) {
                    $node = $server;
                    $port = 0;
                } else {
                    list ( $node, $port ) = explode(':', $server);
                    if ( !$port )
                        $port = ini_get('memcache.default_port');
                    $port = intval($port);
                    if ( !$port )
                        $port = 11211;
                }

                $this->mc[$bucket]->addServer($node, $port, false, 1, 1, -1, true, array($this, 'failure_callback'));
                $this->mc[$bucket]->setCompressThreshold(20000, 0.2);
            }
        }

        global $blog_id, $table_prefix;
        $this->global_prefix = ( is_multisite() || defined('CUSTOM_USER_TABLE') && defined('CUSTOM_USER_META_TABLE') ) ? '' : $table_prefix;
        $this->blog_prefix = ( is_multisite() ? $blog_id : $table_prefix ) . ':';

        // try to use the blog name but if we can't locate it, at least use something unique
        $customer = WP_Object_Cache::get_site_cache_key(__FILE__);
        $this->customer = $customer;
        // SO: blog prefix must come before any custom prefix
        $this->global_prefix = $this->global_prefix . ':' . $customer;
        $this->blog_prefix = $this->blog_prefix . ':' . $customer;

        $this->cache_hits =& $this->stats['get'];
        $this->cache_misses =& $this->stats['add'];
    }
} 

<?php

namespace Statamic\API;

use Stringy\Stringy;
use League\Flysystem\Util;

/**
 * Everything to do with file paths
 */
class Path
{
    /**
     * Makes a $path relative to the BASE
     *
     * @param string $path  The path to change
     * @return string
     */
    public static function makeRelative($path)
    {
        $path = self::resolve($path);

        if (strpos($path, BASE) === 0) {
            $path = str_replace(BASE, '', $path);
        }

        return ltrim($path, '/');
    }

    /**
     * Makes a path full, or absolute.
     *
     * Performs a simple concatenation with the Flysytem root folder.
     * It doesn't perform any checks for whether a valid relative path was provided.
     *
     * @param  string $path  The path to change
     * @return string
     */
    public static function makeFull($path)
    {
        return self::assemble(root_path(), $path);
    }

    /**
     * Determine if a given path is absolute or not.
     *
     * Unix based paths beginning with slashes are absolute: /path/to/something
     * Windows based paths beginning with drive letters are absolute: C:\path\to\something
     * Paths without a leading slash are relative: path/to/something
     *
     * @param string $path
     * @return bool
     */
    public static function isAbsolute($path)
    {
        return $path[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $path) > 0;
    }

    /**
     * Makes a $path a valid URL
     *
     * @param string $path  The path to change
     * @return string
     */
    public static function toUrl($path)
    {
        return Str::ensureLeft(self::makeRelative($path), '/');
    }

    /**
     * Resolve the real-ish path.
     *
     * When you need to resolve the dots in a path but the file doesn't
     * exist, PHP's realpath() won't work. Flysystem already has
     * a way to do this. Nice one. flysystem++
     *
     * @param $path
     * @return string
     */
    public static function resolve($path)
    {
        $leadingSlash = Str::startsWith($path, '/');

        $path = Util::normalizeRelativePath(self::tidy($path));

        // Flysystem's method removes the leading slashes. We want to maintain them.
        return $leadingSlash ? Str::ensureLeft($path, '/') : $path;
    }

    /**
     * Cleans up a given $path, removing any flags and order keys (date-based or number-based)
     *
     * Assumes the path will always end with an extension.
     *
     * @param string  $path  Path to clean
     * @return string
     */
    public static function clean($path)
    {
        // Remove draft and hidden flags
        $path = preg_replace('/\/_[_]?/', '/', $path);

        // Strip the order keys
        $segments = explode('/', $path);
        $total_segments = count($segments);
        foreach ($segments as $i => &$segment) {
            // Skip the final segment (the basename) if it doesn't contain two periods.
            // This stops filenames like 404.md from being interpreted with 404 as
            // the order key, resulting in a borked filename.
            if ($i+1 === $total_segments && substr_count($segment, '.') < 2) {
                continue;
            }

            $segment = preg_replace(Pattern::ORDER_KEY, '', $segment);
        }

        return implode('/', $segments);
    }

    /**
     * Assembles a URL from an ordered list of segments
     *
     * @param mixed string  Open ended number of arguments
     * @return string
     */
    public static function assemble($args)
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            $args = $args[0];
        }

        if (! is_array($args) || ! count($args)) {
            return null;
        }

        return self::tidy(join('/', $args));
    }

    /**
     * Is a given $path a page?
     *
     * @param string $path  Path to check
     * @return bool
     */
    public static function isPage($path)
    {
        $ext = pathinfo($path)['extension'];

        return Pattern::endsWith($path, "index.$ext");
    }

    /**
     * Is a given $path an entry?
     *
     * @param string $path  Path to check
     * @return bool
     */
    public static function isEntry($path)
    {
        return ! self::isPage($path);
    }

    /**
     * Get the status of a $path
     *
     * @param $path
     * @return string
     */
    public static function status($path)
    {
        if (self::isDraft($path)) {
            return 'draft';
        } elseif (self::isHidden($path)) {
            return 'hidden';
        }

        return 'live';
    }

    /**
     * Is a given $path a draft?
     *
     * @param string $path  Path to check
     * @return bool
     */
    public static function isDraft($path)
    {
        $ext = pathinfo($path)['extension'];

        $pattern = (self::isPage($path))
            ? "#/__(?:\d+\.)?[\w-]+/(?:\w+\.)?index\.{$ext}$#"
            : "#/__[\w\._-]+\.{$ext}$#";

        return (bool) preg_match($pattern, $path);
    }

    /**
     * Is a given $path hidden?
     *
     * @param string $path  Path to check
     * @return bool
     */
    public static function isHidden($path)
    {
        $ext = pathinfo($path)['extension'];

        $pattern = (self::isPage($path))
            ? "#/_(?!_)(?:\d+\.)?[\w-]+/(?:\w+\.)?index\.{$ext}$#"
            : "#/_(?!_)[\w\._-]+\.{$ext}$#";

        return (bool) preg_match($pattern, $path);
    }

    /**
     * Tidy a path.
     *
     * @param string $path  Path to tidy
     * @return string
     */
    public static function tidy($path)
    {
        // Remove occurrences of "//" in a $path (except when part of a protocol).
        $path = preg_replace('#(^|[^:])//+#', '\\1/', $path);

        // Replace backslashes with forward slashes for consistency between platforms.
        // PHP is capable of understanding Windows paths that use forward slashes.
        return str_replace('\\', '/', $path);
    }

    /**
     * Get the file extension
     *
     * @param string $path
     * @return string|null
     */
    public static function extension($path)
    {
        return array_get(pathinfo($path), 'extension');
    }

    /**
     * Removes the filename from a path
     *
     * eg. `foo/bar/baz/index.md` would return `foo/bar/baz`
     *
     * @param string $path
     * @return string
     */
    public static function directory($path)
    {
        $info = pathinfo($path);

        return $info['dirname'];
    }

    /**
     * Get the folder of a path
     *
     * eg. `foo/bar/baz/index.md` would return `baz`
     *
     * @param string $path
     * @return string mixed
     */
    public static function folder($path)
    {
        $parts = explode('/', self::directory($path));

        return last($parts);
    }

    /**
     * Get filename
     *
     * @param string $path
     * @return string
     */
    public static function filename($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Get safe filename
     *
     * @param string $path
     * @return string
     */
    public static function safeFilename($path)
    {
        $str = Stringy::create(self::filename($path))->toAscii();

        $str = preg_replace(['/[^\w\(\).-]/i', '/(_)\1+/'], '-', $str);
        $str = rtrim($str, '-');
        $str = strtolower($str);

        return (string) $str;
    }

    /**
     * Append timestamp
     *
     * @param string $path
     * @return string
     */
    public static function appendTimestamp($path)
    {
        $extension = self::extension($path);
        $timestamp = time();

        return preg_replace("/(.*)\.({$extension})$/", "$1-{$timestamp}.$2", $path);
    }

    /**
     * Remove the last segment of a path
     *
     * eg. `foo/bar/baz/` would return `foo/bar`
     *
     * @param string $path
     * @return string
     */
    public static function popLastSegment($path)
    {
        $parts = explode('/', $path);
        array_pop($parts);

        return implode('/', $parts);
    }

    /**
     * Swaps the slug of a $path with the $slug provided
     *
     * @param string  $path  Path to modify
     * @param string  $slug  New slug to use
     * @return string
     */
    public static function replaceSlug($path, $slug)
    {
        return self::popLastSegment($path) . '/' . $slug;
    }
}

<?php

namespace Statamic\Addons\Redirects;

use Statamic\API\File;
use Statamic\API\YAML;

class RedirectsLogger
{
    /**
     * The path where the logs are stored.
     *
     * @var string
     */
    private $storagePath;

    public function __construct($storagePath)
    {
        $this->storagePath = $storagePath;
    }

    /**
     * @var array
     */
    private $data = [
        '404' => null,
        'manual' => null,
        'auto' => null,
    ];

    public function log404($url)
    {
        $this->loadData('404');

        $count = isset($this->data['404'][$url]) ? (int) $this->data['404'][$url] : 0;
        $this->data['404'][$url] = ++$count;

        return $this;
    }

    public function logManualRedirect($route)
    {
        $this->logRedirect('manual', $route);

        return $this;
    }

    public function logAutoRedirect($fromUrl)
    {
        $this->logRedirect('auto', $fromUrl);

        return $this;
    }

    public function remove404($url)
    {
        return $this->remove('404', $url);
    }

    public function removeManualRedirect($route)
    {
        return $this->remove('manual', $route);
    }

    public function removeAutoRedirect($url)
    {
        return $this->remove('auto', $url);
    }

    public function get404s()
    {
        $this->loadData('404');

        return $this->data['404'];
    }

    public function getManualRedirects()
    {
        $this->loadData('manual');

        return $this->data['manual'];
    }

    public function getAutoRedirects()
    {
        $this->loadData('auto');

        return $this->data['auto'];
    }

    public function flush()
    {
        foreach ($this->data as $which => $data) {
            if ($data !== null) {
                File::put($this->getYamlFile($which), YAML::dump($data));
            }
        }
    }

    private function remove($which, $url)
    {
        $this->loadData($which);

        if (isset($this->data[$which][$url])) {
            unset($this->data[$which][$url]);
        }

        return $this;
    }

    private function logRedirect($which, $from)
    {
        $this->loadData($which);

        $count = isset($this->data[$which][$from]) ? (int) $this->data[$which][$from] : 0;
        $this->data[$which][$from] = ++$count;
    }

    private function loadData($which)
    {
        if ($this->data[$which] === null) {
            $file = $this->getYamlFile($which);
            $this->data[$which] = $this->parseYaml($file);
        }
    }

    private function getYamlFile($which)
    {
        return $this->storagePath . 'log_' . $which . '.yaml';
    }

    private function parseYaml($file) {
        if (!File::exists($file)) {
            return [];
        }

        try {
            return YAML::parse(File::get($file));
        } catch (\Exception $e) {
            throw new RedirectsLogParseException($file, $e);
        }
    }
}
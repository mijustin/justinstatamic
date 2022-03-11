<?php

namespace Statamic\Addons\Redirects;

use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Events\Data\AddonSettingsSaved;

abstract class RedirectsManager
{
    /**
     * @var array
     */
    protected $redirects;

    /**
     * Path to the YAML file storing the redirects.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * @var RedirectsLogger
     */
    protected $redirectsLogger;

    public function __construct($storagePath, RedirectsLogger $redirectsLogger)
    {
        $this->storagePath = $storagePath;
        $this->redirectsLogger = $redirectsLogger;

        $this->loadRedirects();
    }

    /**
     * Remove a redirect identified by the given route.
     *
     * @param string $route
     *
     * @return $this
     */
    public function remove($route)
    {
        unset($this->redirects[$route]);

        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        return collect($this->redirects)
            ->map(function ($data, $from) {
                return $this->get($from);
            })
            ->values()
            ->all();
    }

    /**
     * Write all redirects to the filesystem.
     */
    public function flush()
    {
        if ($this->redirects !== null) {
            File::put($this->storagePath, YAML::dump($this->redirects));

            // Emit an event that the redirects have been changed.
            // Note: The "AddonSettingsSaved" event is semantically not 100% correct, as we did not change the addon's settings.
            // However, it allows the Spock addon to detect changed redirect YAML files, as Spock is listening to this event by default.
            event(new AddonSettingsSaved($this->storagePath, $this->redirects));
        }

        $this->redirectsLogger->flush();
    }

    /**
     * Check if a redirect with the given route/url exists.
     *
     * @param string $route
     *
     * @return bool
     */
    public function exists($route)
    {
        return isset($this->redirects[$route]);
    }

    abstract public function get($route);

    protected function loadRedirects()
    {
        if ($this->redirects === null) {
            $this->redirects = File::exists($this->storagePath) ? YAML::parse(File::get($this->storagePath)) : [];
        }
    }
}

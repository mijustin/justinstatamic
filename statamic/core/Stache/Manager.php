<?php

namespace Statamic\Stache;

use Statamic\API\Cache;
use Statamic\API\Config;
use Statamic\Events\StacheUpdated;

class Manager
{
    /**
     * Core Stache drivers
     *
     * @var array
     */
    protected $drivers = [
        'Pages',
        'PageFolders',
        'PageStructure',
        'Collections',
        'Globals',
        'Entries',
        'Users',
        'UserGroups',
        'AssetContainers',
        'Taxonomies',
        'Terms',
    ];

    /**
     * @var \Statamic\Stache\Stache
     */
    protected $stache;

    /**
     * @var \Statamic\Stache\UpdateManager
     */
    protected $updater;

    /**
     * @var \Statamic\Stache\Persister
     */
    protected $persister;

    /**
     * @var \Statamic\Stache\Loader
     */
    protected $loader;

    public function __construct(Stache $stache, Loader $loader, UpdateManager $updater, Persister $persister)
    {
        $this->stache = $stache;
        $this->loader = $loader;
        $this->updater = $updater;
        $this->persister = $persister;
    }

    public function registerDrivers()
    {
        collect($this->drivers)->each(function ($driver) {
            $this->stache->registerDriver(
                app('Statamic\Stache\Drivers\\'.$driver.'Driver')
            );
        });
    }

    public function load()
    {
        $this->waitForUpdateToComplete();

        $this->loader->load();
    }

    public function update()
    {
        $locale = site_locale();

        site_locale(default_locale());

        $this->updater->update();

        if ($this->updater->updated()) {
            $this->persister->persist(
                $this->updater->updates()
            );

            event(new StacheUpdated($this->updater->updates(), $this->stache));

            $this->updater->resetUpdateStatus();
        }

        site_locale($locale);
    }

    public function hasConfigChanged()
    {
        if (! Cache::has('stache::config')) {
            return false;
        }

        return $this->stache->buildConfig() !== Cache::get('stache::config');
    }

    protected function waitForUpdateToComplete()
    {
        if (! Config::get('caching.stache_lock_enabled')) {
            return;
        }

        $start = time();

        while ($this->isLocked()) {
            if (time() - $start >= Config::get('caching.stache_lock_wait_length')) {
                throw new TimeoutException;
            }

            sleep(1);
        }
    }

    protected function isLocked()
    {
        $block = app()->runningInConsole();

        return ! $this->stache->lock()->acquire($block);
    }
}

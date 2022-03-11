<?php

namespace Statamic\Addons\Redirects;

use Statamic\Extend\API;

class RedirectsAPI extends API
{
    /**
     * @var ManualRedirectsManager
     */
    private $manualRedirectsManager;

    /**
     * @var RedirectsLogger
     */
    private $redirectsLogger;

    /**
     * @var AutoRedirectsManager
     */
    private $autoRedirectsManager;

    public function __construct(ManualRedirectsManager $manualRedirectsManager, AutoRedirectsManager $autoRedirectsManager, RedirectsLogger $redirectsLogger)
    {
        parent::__construct();

        $this->manualRedirectsManager = $manualRedirectsManager;
        $this->redirectsLogger = $redirectsLogger;
        $this->autoRedirectsManager = $autoRedirectsManager;
    }

    /**
     * @return ManualRedirectsManager
     */
    public function manualRedirectsManager()
    {
        return $this->manualRedirectsManager;
    }

    /**
     * @return AutoRedirectsManager
     */
    public function autoRedirectsManager()
    {
        return $this->autoRedirectsManager;
    }

    /**
     * @return RedirectsLogger
     */
    public function logger()
    {
        return $this->redirectsLogger;
    }
}

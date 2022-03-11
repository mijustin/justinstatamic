<?php

namespace Statamic\Addons\Redirects\Controllers;

use Illuminate\Http\Request;
use Statamic\Addons\Redirects\AutoRedirect;
use Statamic\Addons\Redirects\AutoRedirectsManager;
use Statamic\Addons\Redirects\RedirectsAccessChecker;
use Statamic\Addons\Redirects\RedirectsLogger;
use Statamic\Addons\Redirects\RedirectsManager;

class AutoRedirectsController extends RedirectsController
{
    /**
     * @var RedirectsManager
     */
    private $autoRedirectsManager;

    /**
     * @var RedirectsLogger
     */
    private $redirectsLogger;

    public function __construct(AutoRedirectsManager $autoRedirectsManager, RedirectsLogger $redirectsLogger, RedirectsAccessChecker $redirectsAccessChecker)
    {
        parent::__construct($redirectsAccessChecker);

        $this->autoRedirectsManager = $autoRedirectsManager;
        $this->redirectsLogger = $redirectsLogger;
    }

    public function show()
    {
        return $this->view('auto_index', [
            'title' => $this->trans('common.auto_redirects'),
            'translations' => json_encode($this->trans('common')),
            'columns' => json_encode($this->getColumns()),
        ]);
    }

    public function get(Request $request)
    {
        $items = $this->buildRedirectItems($request);

        return $this->paginatedItemsResponse($items, $this->getColumns(), $request);
    }

    public function delete(Request $request)
    {
        $redirectIds = $request->input('ids');

        foreach ($redirectIds as $redirectId) {
            $route = base64_decode($redirectId);
            $this->autoRedirectsManager->remove($route);
        }

        $this->autoRedirectsManager->flush();

        return ['success' => true];
    }

    private function getColumns()
    {
        $columns = [
            ['value' => 'from', 'header' => $this->trans('common.from')],
            ['value' => 'to', 'header' => $this->trans('common.to')],
        ];

        if ($this->shouldLogRedirects()) {
            $columns[] = ['value' => 'hits', 'header' => $this->trans('common.hits')];
        }

        return $columns;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    private function buildRedirectItems(Request $request)
    {
        $redirects = $this->autoRedirectsManager->all();
        $logs = $this->redirectsLogger->getAutoRedirects();

        $items = collect($redirects)->map(function ($redirect) use ($logs) {
            /** @var AutoRedirect $redirect */
            $id = base64_encode($redirect->getFromUrl());
            return array_merge($redirect->toArray(), [
                'id' => $id,
                'checked' => false,
                'hits' => isset($logs[$redirect->getFromUrl()]) ? $logs[$redirect->getFromUrl()] : 0,
            ]);
        });

        return $this->sortItems($items, $request);
    }

    private function shouldLogRedirects()
    {
        return $this->getConfigBool('log_redirects_enable', true);
    }
}

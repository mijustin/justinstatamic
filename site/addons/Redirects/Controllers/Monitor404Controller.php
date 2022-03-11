<?php

namespace Statamic\Addons\Redirects\Controllers;

use Illuminate\Http\Request;
use Statamic\Addons\Redirects\RedirectsAccessChecker;
use Statamic\Addons\Redirects\RedirectsLogger;

class Monitor404Controller extends RedirectsController
{
    /**
     * @var RedirectsLogger
     */
    private $redirectsLogger;

    public function __construct(RedirectsLogger $redirectsLogger, RedirectsAccessChecker $redirectsAccessChecker)
    {
        parent::__construct($redirectsAccessChecker);

        $this->redirectsLogger = $redirectsLogger;
    }

    public function show()
    {
        return $this->view('404_index', [
            'title' => $this->trans('common.monitor_404'),
            'translations' => json_encode($this->trans('common')),
            'columns' => json_encode($this->getColumns()),
        ]);
    }

    public function get(Request $request)
    {
        $items = $this->buildItems($request);

        return $this->paginatedItemsResponse($items, $this->getColumns(), $request);
    }

    public function delete(Request $request)
    {
        $ids = $request->input('ids');

        foreach ($ids as $id) {
            $url = base64_decode($id);
            $this->redirectsLogger->remove404($url);
        }

        $this->redirectsLogger->flush();

        return ['success' => true];
    }

    private function getColumns()
    {
        return [
            ['value' => 'url', 'header' => 'URL'],
            ['value' => 'hits', 'header' => $this->trans('common.hits')],
        ];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    private function buildItems(Request $request)
    {
        $logs = $this->redirectsLogger->get404s();

        $items = collect($logs)->map(function ($hits, $from) {
            return [
                'id' => base64_encode($from),
                'url' => $from,
                'checked' => false,
                'hits' => $hits,
                'create_redirect_url' => route('redirects.manual.create'),
            ];
        });

        return $this->sortItems($items, $request);
    }
}

<?php

namespace Statamic\Addons\Redirects\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Statamic\Addons\Redirects\RedirectsAccessChecker;
use Statamic\API\Config;
use Statamic\API\User;
use Statamic\Exceptions\UnauthorizedHttpException;
use Statamic\Extend\Controller;
use Statamic\Presenters\PaginationPresenter;

abstract class RedirectsController extends Controller
{
    /**
     * @var RedirectsAccessChecker
     */
    private $redirectsAccessChecker;

    public function __construct(RedirectsAccessChecker $redirectsAccessChecker)
    {
        parent::__construct();

        $this->redirectsAccessChecker = $redirectsAccessChecker;
        $this->checkAccess();
    }

    protected function sortItems(Collection $items, Request $request)
    {
        if (!$request->get('sort')) {
            return $items;
        }

        $method = $request->get('order', 'asc') === 'asc' ? 'sortBy' : 'sortByDesc';

        return $items->$method($request->get('sort'));
    }

    protected function paginatedItemsResponse(Collection $items, array $columns, Request $request)
    {
        $perPage = Config::get('cp.pagination_size');
        $currentPage = (int)$request->get('page', 1);
        $totalCount = $items->count();
        $offset = ($currentPage - 1) * $perPage;
        $items = $items->slice($offset, $perPage);
        $paginator = new LengthAwarePaginator($items, $totalCount, $perPage, $currentPage);

        return [
            'items' => $items->values()->all(),
            'columns' => $columns,
            'pagination' => [
                'totalItems' => $totalCount,
                'itemsPerPage' => $perPage,
                'currentPage' => $currentPage,
                'totalPages' => $paginator->lastPage(),
                'prevPage' => $paginator->previousPageUrl(),
                'nextPage' => $paginator->nextPageUrl(),
                'segments' => array_get($paginator->render(new PaginationPresenter($paginator)), 'segments')
            ],
        ];
    }

    private function checkAccess()
    {
        if (!$this->redirectsAccessChecker->hasAccess(User::getCurrent())) {
            throw new UnauthorizedHttpException(403);
        }
    }
}

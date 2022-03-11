<?php

namespace Statamic\Addons\Redirects\Controllers;

use Illuminate\Http\Request;
use Statamic\Addons\Redirects\ManualRedirect;
use Statamic\Addons\Redirects\ManualRedirectsManager;
use Statamic\Addons\Redirects\RedirectsAccessChecker;
use Statamic\Addons\Redirects\RedirectsLogger;
use Statamic\API\Collection;
use Statamic\API\Config;
use Statamic\API\Content;
use Statamic\API\Fieldset;
use Statamic\API\File;
use Statamic\API\Str;
use Statamic\API\Taxonomy;
use Statamic\API\YAML;
use Statamic\CP\Publish\ProcessesFields;
use Statamic\Extend\Extensible;

class ManualRedirectsController extends RedirectsController
{
    use ProcessesFields;
    use Extensible;

    /**
     * @var ManualRedirectsManager
     */
    private $manualRedirectsManager;

    /**
     * @var RedirectsLogger
     */
    private $redirectsLogger;

    public function __construct(ManualRedirectsManager $manualRedirectsManager, RedirectsLogger $redirectsLogger, RedirectsAccessChecker $redirectsAccessChecker)
    {
        parent::__construct($redirectsAccessChecker);

        $this->manualRedirectsManager = $manualRedirectsManager;
        $this->redirectsLogger = $redirectsLogger;
    }

    public function index()
    {
        return redirect()->route('redirects.manual.show');
    }

    public function show()
    {
        return $this->view('manual_index', [
            'title' => $this->trans('common.manual_redirects'),
            'create_title' => $this->trans('common.manual_redirect_create'),
            'translations' => json_encode($this->trans('common')),
            'columns' => json_encode($this->getColumns()),
        ]);
    }

    public function edit($redirectId)
    {
        $data = $this->buildPublishFormDataFromRedirect($redirectId);

        if ($data === null) {
            abort(404, 'No redirect found');
        }

        $fieldset = $this->getFieldset();

        return $this->view('manual_edit', [
            'id' => $redirectId,
            'title' => $this->trans('common.manual_redirect_edit'),
            'submitUrl' => route('redirects.manual.save'),
            'fieldset' => $fieldset->toPublishArray(),
            'data' => $this->preProcessWithBlankFields($fieldset, $data),
        ]);
    }

    public function save(Request $request)
    {
        $data = $this->processFields($this->getFieldset(), $request->get('fields'), false);
        $routePrevious = isset($data['id']) ? base64_decode($data['id']) : null;

        // If the source URL changed, we treat it as a new redirect and delete the old one.
        if ($routePrevious && $routePrevious !== $data['from']) {
            $this->manualRedirectsManager->remove($routePrevious);
        }

        $targetType = $data['target_type'];

        $redirect = (new ManualRedirect())
            ->setFrom($data['from'])
            ->setTo($data['to_' . $targetType])
            ->setLocale($data['locale'])
            ->setRetainQueryStrings((bool)$data['retain_query_strings'])
            ->setStatusCode($data['status_code']);

        if ($data['timed_activation']) {
            $redirect
                ->setStartDate(isset($data['start_date']) && $data['start_date'] ? new \DateTime($data['start_date']) : null)
                ->setEndDate(isset($data['end_date']) && $data['end_date'] ? new \DateTime($data['end_date']) : null);
            if ($redirect->getStartDate() && $redirect->getEndDate()) {
                $redirect->setStatusCode(302);
            }
        }

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        if (!$request->get('continue') || $request->get('new')) {
            $this->success(trans('cp.saved_success'));
        }

        return [
            'success' => true,
            'redirect' => route('redirects.manual.show'),
            'message' => trans('cp.saved_success'),
        ];
    }

    public function create(Request $request)
    {
        $fieldset = $this->getFieldset();

        $data = $this->preProcessWithBlankFields($fieldset, []);

        // Pre-populate the source?
        if ($request->get('from')) {
            $data['from'] = $request->get('from');
        }

        return $this->view('manual_edit', [
            'id' => null,
            'title' => $this->trans('common.manual_redirect_create'),
            'submitUrl' => route('redirects.manual.save'),
            'fieldset' => $fieldset->toPublishArray(),
            'data' => $data,
        ]);
    }

    public function get(Request $request)
    {
        $items = $this->buildRedirectItems($request);

        return [
            'items' => $items,
            'columns' => $this->getColumns(),
            'pagination' => [
                'totalItems' => count($items),
                'itemsPerPage' => count($items),
                'currentPage' => 1,
                'prevPage' => null,
                'nextPage' => null,
            ],
        ];
    }

    public function delete(Request $request)
    {
        $redirectIds = $request->input('ids');

        foreach ($redirectIds as $redirectId) {
            $route = base64_decode($redirectId);
            $this->manualRedirectsManager->remove($route);
        }

        $this->manualRedirectsManager->flush();

        return ['success' => true];
    }

    public function reorder(Request $request)
    {
        $ids = $request->input('ids');

        foreach ($ids as $position => $redirectId) {
            $route = base64_decode($redirectId);
            $this->manualRedirectsManager->setPosition($route, $position);
        }

        $this->manualRedirectsManager->flush();

        return ['success' => true];
    }

    private function getColumns()
    {
        $columns = [
            ['value' => 'from', 'header' => $this->trans('common.from')],
            ['value' => 'to', 'header' => $this->trans('common.to')],
            ['value' => 'target_type', 'header' => $this->trans('common.target_type')],
            ['value' => 'status_code', 'header' => $this->trans('common.status_code')],
            ['value' => 'locale', 'header' => trans('cp.locale')],
            ['value' => 'start_date', 'header' => $this->trans('common.start_date')],
            ['value' => 'end_date', 'header' => $this->trans('common.end_date')],
        ];

        if ($this->shouldLogRedirects()) {
            $columns[] = ['value' => 'hits', 'header' => $this->trans('common.hits')];
        }

        return $columns;
    }

    private function getFieldset()
    {
        $yaml = File::get($this->getDirectory() . "/resources/fieldsets/manual_redirect.yaml");
        $fields = YAML::parse($yaml);

        // Only suggest entries and terms having URLs.
        $collectionsWithUrl = Collection::all()->filter(function ($collection) {
            return $collection->route();
        });

        if ($collectionsWithUrl->count()) {
            $fields['to_entry']['collection'] = $collectionsWithUrl->keys()->toArray();
        } else {
            unset($fields['to_entry']);
            unset($fields['target_type']['options']['collection']);
        }

        $termsWithUrl = Taxonomy::all()->filter(function ($term) {
            return $term->route();
        });

        if ($termsWithUrl->count()) {
            $fields['to_term']['taxonomy'] = $termsWithUrl->keys()->toArray();
        } else {
            unset($fields['to_term']);
            unset($fields['target_type']['options']['term']);
        }

        // Inject available locales
        foreach (Config::get('system.locales', []) as $key => $data) {
            $fields['locale']['options'][$key] = $data['name'];
        }

        // Translate labels and instructions.
        $namespace = 'fieldsets/manual_redirect';

        $localizedFields = collect($fields)->map(function ($field, $name) use ($namespace) {
            $label = ($name === 'locale') ? trans('cp.locale') : $this->trans(sprintf('%s.%s', $namespace, $name));
            $field['display'] = $label;
            $instructionsKey = sprintf('%s.%s_%s', $namespace, $name, 'instructions');
            $instructions = $this->trans($instructionsKey);
            if ($instructions !== 'addons.Redirects::' . $instructionsKey) {
                $field['instructions'] = $instructions;
            }

            return $field;
        });

        $contents['sections']['main']['fields'] = $localizedFields->toArray();

        return Fieldset::create('manual_redirect', $contents);
    }

    private function buildPublishFormDataFromRedirect($redirectId)
    {
        $route = base64_decode($redirectId);
        $redirect = $this->manualRedirectsManager->get($route);

        if ($redirect === null) {
            return null;
        }
        $data = $redirect->toArray();
        $data['timed_activation'] = ($redirect->getStartDate() || $redirect->getEndDate());
        $data['id'] = $redirectId;

        $targetType = $this->getTargetType($redirect->getTo());
        $data['target_type'] = $targetType;
        $data['to_' . $targetType] = $redirect->getTo();

        return $data;
    }

    private function buildRedirectItems(Request $request)
    {
        $redirects = $this->manualRedirectsManager->all();
        $logs = $this->redirectsLogger->getManualRedirects();
        $dateFormat = Config::get('system.date_format', 'Y-m-d') . ' H:i';

        $items = collect($redirects)->map(function ($redirect) use ($dateFormat, $logs) {
            /** @var ManualRedirect $redirect */
            $id = base64_encode($redirect->getFrom());
            return array_merge($redirect->toArray(), [
                'to' => $this->getUrlFromTarget($redirect->getTo()),
                'target_type' => ucfirst($this->getTargetType($redirect->getTo())),
                'start_date' => $redirect->getStartDate() ? $redirect->getStartDate()->format($dateFormat) : null,
                'end_date' => $redirect->getEndDate() ? $redirect->getEndDate()->format($dateFormat) : null,
                'id' => $id,
                'locale' => strtoupper($redirect->getLocale()),
                'checked' => false,
                'hits' => isset($logs[$redirect->getFrom()]) ? $logs[$redirect->getFrom()] : 0,
                'edit_url' => route('redirects.manual.edit', ['id' => $id])
            ]);
        });

        return $this->sortItems($items, $request)
            ->values()
            ->all();
    }

    private function getUrlFromTarget($target)
    {
        if (Str::startsWith($target, '/') || Str::startsWith($target, 'http')) {
            return $target;
        }

        $content = Content::find($target);
        if ($content) {
            return $content->url();
        }

        return '';
    }

    private function getTargetType($target)
    {
        if (Str::startsWith($target, '/')) {
            return 'url';
        }

        $content = Content::find($target);
        if ($content) {
            return strtolower((new \ReflectionClass($content))->getShortName());
        }

        return 'url';
    }

    private function shouldLogRedirects()
    {
        return $this->getConfigBool('log_redirects_enable', true);
    }
}

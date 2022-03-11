<?php

namespace Statamic\Addons\Redirects;

use Illuminate\Support\Facades\Log;
use Statamic\API\Config;
use Statamic\API\URL;
use Statamic\API\User;
use Statamic\Contracts\Data\Pages\Page;
use Statamic\Events\Data\ContentDeleted;
use Statamic\Events\Data\ContentSaved;
use Statamic\Events\Data\PageMoved;
use Statamic\Events\Data\PageSaved;
use Statamic\Exceptions\RedirectException;
use Statamic\Extend\Listener;
use Statamic\API\Nav;
use Statamic\API\Page as PageAPI;
use Symfony\Component\HttpFoundation\Response;

class RedirectsListener extends Listener
{
    /**
     * The events to be listened for, and the methods to call.
     *
     * @var array
     */
    public $events = [
        'cp.nav.created' => 'addNavItems',
        'cp.add_to_head' => 'addToHead',
        'response.created' => 'onResponseCreated',
        'Statamic\Events\Data\PageMoved' => 'onPageMoved',
        'Statamic\Events\Data\PageSaved' => 'onPageSaved',
        'Statamic\Events\Data\EntrySaved' => 'onContentSaved',
        'Statamic\Events\Data\TermSaved' => 'onContentSaved',
        'Statamic\Events\Data\EntryDeleted' => 'onContentDeleted',
        'Statamic\Events\Data\PageDeleted' => 'onContentDeleted',
        'Statamic\Events\Data\TermDeleted' => 'onContentDeleted',
    ];

    /**
     * Extend the main navigation of the control panel.
     *
     * @param $nav
     */
    public function addNavItems($nav)
    {
        if (!app(RedirectsAccessChecker::class)->hasAccess(User::getCurrent())) {
            return;
        }

        $root = Nav::item('redirects')
            ->title('Redirects')
            ->route('redirects.index')
            ->icon('shuffle');

        $root->add(function ($item) {
            $item->add(Nav::item('redirects.manual')
                ->title($this->trans('common.manual_redirects'))
                ->route('redirects.manual.show'));

            if ($this->getConfigBool('auto_redirect_enable')) {
                $item->add(Nav::item('redirects.auto')
                    ->title($this->trans('common.auto_redirects'))
                    ->route('redirects.auto.show'));
            }

            if ($this->getConfigBool('log_404_enable')) {
                $item->add(Nav::item('redirects.404')
                    ->title($this->trans('common.monitor_404'))
                    ->route('redirects.404.show'));
            }
        });

        $nav->addTo('tools', $root);
    }

    public function addToHead()
    {
        $css = $this->css->url('styles.css');

        return '<link rel="stylesheet" type="text/css" href="' . $css . '">';
    }

    /**
     * Check for redirects if a 404 response is created by Statamic.
     *
     * @param Response $response
     *
     * @throws RedirectException
     */
    public function onResponseCreated(Response $response)
    {
        if ($response->getStatusCode() !== 404) {
            return;
        }

        $request = request();

        app(RedirectsProcessor::class)->redirect($request);

        // If we reach this, no redirect exception has been thrown, so log the 404.
        if ($this->getConfigBool('log_404_enable')) {
            $route = $request->getBaseUrl() . $request->getPathInfo();
            $this->tryLogging404($route);
        }
    }

    public function onPageMoved(PageMoved $event)
    {
        $this->handlePageRedirects($event->page, $event->oldPath, $event->newPath);
    }

    public function onPageSaved(PageSaved $event)
    {
        $originalAttributes = $event->original['attributes'];

        // If the original path is not set, the page is newly created, no redirects needed.
        if (!isset($originalAttributes['path'])) {
            return;
        }

        $oldPath = $originalAttributes['path'];
        $newPath = $event->data->path();

        $this->handlePageRedirects($event->data, $oldPath, $newPath, $event->original);
    }

    public function onContentSaved(ContentSaved $event)
    {
        if (!$this->getConfigBool('auto_redirect_enable')) {
            return;
        }

        $content = $event->data;
        if (!$content->uri()) {
            return;
        }

        foreach ($this->getLocales() as $locale) {
            $localizedContent = $content->in($locale);

            $oldSlug = null;
            if ($locale === Config::getDefaultLocale() && isset($event->original['attributes']['slug'])) {
                $oldSlug = $event->original['attributes']['slug'];
            } else if (isset($event->original['data'][$locale]) && isset($event->original['data'][$locale]['slug'])){
                $oldSlug = $event->original['data'][$locale]['slug'];
            }

            if ($oldSlug === null) {
                continue;
            }

            $slug = $localizedContent->slug();

            if ($slug === $oldSlug) {
                $this->deleteRedirectsOfUrl($localizedContent->url());
                continue;
            }

            $oldUrl = str_replace("/$slug", "/$oldSlug", $localizedContent->url());

            $autoRedirect = (new AutoRedirect())
                ->setFromUrl($oldUrl)
                ->setToUrl($localizedContent->url())
                ->setContentId($content->id());

            app(AutoRedirectsManager::class)->add($autoRedirect);
        }

        app(AutoRedirectsManager::class)->flush();
    }

    public function onContentDeleted(ContentDeleted $event)
    {
        $id = $event->contextualData()['id'];

        app(AutoRedirectsManager::class)
            ->removeRedirectsOfContentId($id)
            ->flush();
    }

    private function handlePageRedirects(Page $page, $oldPath, $newPath, $original = null)
    {
        if (!$this->getConfigBool('auto_redirect_enable')) {
            return;
        }

        $oldUrl = URL::buildFromPath($oldPath);
        $newUrl = URL::buildFromPath($newPath);

        if ($oldUrl === $newUrl) {
            $this->deleteRedirectsOfUrl($newUrl);
        } else {
            $autoRedirect = (new AutoRedirect())
                ->setFromUrl($oldUrl)
                ->setToUrl($newUrl)
                ->setContentId($page->id());

            app(AutoRedirectsManager::class)->add($autoRedirect);

            $this->handlePageRedirectsRecursive($page->id(), $oldUrl, $newUrl);
        }

        // Handle the multi language case.
        foreach ($this->getLocales() as $locale) {
            // The default locale has been handled above.
            if ($locale === Config::getDefaultLocale()) {
                continue;
            }

            $localizedPage = $page->in($locale);

            // When a page has been saved, the original data is available.
            if ($original && isset($original['data'][$locale]) && isset($original['data'][$locale]['slug'])) {
                // Check for changed slugs in the current locale
                $oldSlug = $original['data'][$locale]['slug'];
                $newSlug = $localizedPage->slug();

                if ($newSlug !== $oldSlug) {
                    $oldUrl = $localizedPage->url(); // The localized object still returns the old URL...bug?!
                    $newUrl = str_replace("/$oldSlug", "/$newSlug", $oldUrl);

                    $autoRedirect = (new AutoRedirect())
                        ->setFromUrl($oldUrl)
                        ->setToUrl($newUrl)
                        ->setContentId($page->id());

                    app(AutoRedirectsManager::class)->add($autoRedirect);

                    $this->handlePageRedirectsRecursive($page->id(), $oldUrl, $newUrl, $locale);
                }
            } else {
                // The original is not available, a page has been moved. We only take action if the URLs changed in the default language.
                // We cannot determine the new URL at this point in the request lifecycle. Let Statamic do its magic and store the
                // auto redirect via middleware (@see RedirectsMiddleware).
                if ($oldUrl !== $newUrl) {
                    $oldUrl = $localizedPage->url();
                    app(RedirectsMaintenance::class)->addLocalizedAutoRedirect($page->id(), $oldUrl, $locale);
                }
            }
        }

        app(AutoRedirectsManager::class)->flush();
    }

    private function tryLogging404($route)
    {
        try {
            app(RedirectsLogger::class)
                ->log404($route)
                ->flush();
        } catch (RedirectsLogParseException $e) {
            Log::error($e->getMessage(), ['original' => $e->getPrevious()->getMessage()]);
        }
    }

    private function handlePageRedirectsRecursive($pageId, $oldUrl, $newUrl, $locale = null)
    {
        // Must retrieve page object via find otherwise children are not loaded...
        $page = PageAPI::find($pageId);
        $childPages = $page->children(1);

        if (!$childPages->count()) {
            return;
        }

        foreach ($childPages as $childPage) {
            if ($locale) {
                $childPage = $childPage->in($locale);
            }

            $oldChildUrl = sprintf('%s/%s', $oldUrl, $childPage->slug());
            $newChildUrl = sprintf('%s/%s', $newUrl, $childPage->slug());

            $autoRedirect = (new AutoRedirect())
                ->setFromUrl($oldChildUrl)
                ->setToUrl($newChildUrl)
                ->setContentId($childPage->id());

            app(AutoRedirectsManager::class)->add($autoRedirect);

            $this->handlePageRedirectsRecursive($childPage->id(), $oldChildUrl, $newChildUrl, $locale);
        }
    }

    private function deleteRedirectsOfUrl($url)
    {
        if (app(AutoRedirectsManager::class)->exists($url)) {
            app(AutoRedirectsManager::class)
                ->remove($url)
                ->flush();
        }
    }

    private function getLocales()
    {
        return Config::getLocales();
    }
}

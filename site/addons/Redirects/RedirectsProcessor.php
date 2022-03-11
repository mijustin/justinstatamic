<?php

namespace Statamic\Addons\Redirects;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Log;
use Statamic\API\Config;
use Statamic\API\Content;
use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\Exceptions\RedirectException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectsProcessor
{
    const WILDCARD_NAME = 'any';

    /**
     * @var RedirectsManager
     */
    private $manualRedirectsManager;

    /**
     * @var AutoRedirectsManager
     */
    private $autoRedirectsManager;

    /**
     * @var RedirectsLogger
     */
    private $redirectsLogger;

    /**
     * @var array
     */
    private $routeCollections = [
        'manual' => null,
        'auto' => null,
    ];

    /**
     * @var array
     */
    private $addonConfig;

    public function __construct(
        ManualRedirectsManager $manualRedirectsManager,
        AutoRedirectsManager $autoRedirectsManager,
        RedirectsLogger $redirectsLogger,
        array $addonConfig
    )
    {
        $this->manualRedirectsManager = $manualRedirectsManager;
        $this->redirectsLogger = $redirectsLogger;
        $this->autoRedirectsManager = $autoRedirectsManager;
        $this->addonConfig = collect($addonConfig);
    }

    /**
     * Redirect the request by throwing a RedirectException, if a redirect route is found.
     *
     * Manual redirects take precedence over the auto ones.
     *
     * @param Request $request
     *
     * @throws RedirectException
     */
    public function redirect(Request $request)
    {
        $this->performManualRedirect($request);
        $this->performAutoRedirect($request);
    }

    public function shouldLogRedirect()
    {
        return (bool)$this->addonConfig->get('log_redirects_enable', true);
    }

    private function performAutoRedirect(Request $request)
    {
        $route = $this->matchRedirectRoute('auto', $request);
        if ($route === false) {
            return;
        }

        $redirect = $this->autoRedirectsManager->get($route);
        if ($redirect === null) {
            return;
        }

        if ($this->shouldLogRedirect()) {
            $this->tryLoggingAutoRedirect($route);
        }

        $this->throwRedirectException($redirect->getToUrl(), 301);
    }

    private function performManualRedirect(Request $request)
    {
        $route = $this->matchRedirectRoute('manual', $request);
        if ($route === false) {
            return;
        }

        $redirect = $this->manualRedirectsManager->get($route);
        if ($redirect === null) {
            return;
        }

        // Bail if the request's locale does not match the configured one.
        if ($redirect->getLocale() && $redirect->getLocale() !== site_locale()) {
            return;
        }

        $redirectUrl = $this->normalizeRedirectUrl($redirect->getTo(), $route, $request);

        if (!$redirectUrl) {
            return;
        }

        $statusCode = $redirect->getStatusCode();

        // Check if the redirect is only executed in a time range.
        if ($redirect->getStartDate() || $redirect->getEndDate()) {
            $now = time();
            if ($redirect->getStartDate() && ($redirect->getStartDate()->getTimestamp() > $now)) {
                return;
            }

            if ($redirect->getEndDate() && ($redirect->getEndDate()->getTimestamp() < $now)) {
                return;
            }

            // If start and end date are specified, this is a temporary redirect by design (302).
            $statusCode = ($redirect->getStartDate() && $redirect->getEndDate()) ? 302 : $statusCode;
        }

        if ($redirect->isRetainQueryStrings() && $request->getQueryString()) {
            // If redirect target already contains a query string, concat additional query string with `&`, otherwise default to `?`
            $redirectUrl .= (strpos($redirectUrl, '?') !== false) ? '&' : '?';
            $redirectUrl .= $request->getQueryString();
        }

        if ($this->shouldLogRedirect()) {
            $this->tryLoggingManualRedirect($route);
        }

        $this->throwRedirectException($redirectUrl, $statusCode);
    }

    /**
     * Normalize the given target URL to an URL we can redirect to.
     *
     * @param string $targetUrl The URL we should redirect to
     * @param string $matchedRoute The matched route by the current request
     * @param Request $request
     *
     * @return string|null
     */
    private function normalizeRedirectUrl($targetUrl, $matchedRoute, Request $request)
    {
        // The target URL is relative, check for parameters and replace them.
        if (Str::startsWith($targetUrl, '/')) {
            $wildcardParameter = $this->getWildcardParameter();
            if (strpos($matchedRoute, $wildcardParameter) !== false) {
                // The special {any} parameter captures any number of URL segments.
                $pattern = str_replace(['/', $wildcardParameter], ["\/", '(.*)'], $matchedRoute);
                preg_match('%' . $pattern . '%', $request->getPathInfo(), $matches);

                return str_replace($wildcardParameter, $matches[1], $targetUrl);
            } else if (preg_match_all('%\{(\w+)\}%', $matchedRoute, $matches)) {
                // Any other parameters capture exactly one URL segment.
                $segmentsRoute = explode('/', ltrim($matchedRoute, '/'));
                $segmentsRequestPath = explode('/', ltrim($request->getPathInfo(), '/'));
                $replacements = [];
                foreach ($matches[0] as $parameter) {
                    // Find the position of the placeholder within the route.
                    $pos = array_search($parameter, $segmentsRoute);
                    if ($pos === false) {
                        continue;
                    }
                    $replacements[$parameter] = $segmentsRequestPath[$pos];
                }

                return str_replace($matches[0], $replacements, $targetUrl);
            }

            return $targetUrl;
        } else if (Str::startsWith($targetUrl, 'http')) {
            return $targetUrl;
        }

        /** @var \Statamic\Contracts\Data\Content\Content $content */
        $content = Content::find($targetUrl);
        if ($content && $content->uri()) {
            $localizedContent = $content->in(site_locale());

            return $localizedContent->url();
        }

        return null;
    }

    private function throwRedirectException($url, $statusCode)
    {
        // Need absolute URL because of: https://github.com/statamic/v2-hub/issues/2303
        $absoluteUrl = URL::makeAbsolute($url, Config::getDefaultLocale());

        throw (new RedirectException())
            ->setUrl($absoluteUrl)
            ->setCode($statusCode);
    }

    private function matchRedirectRoute($which, Request $request)
    {
        $this->loadRouteCollections($which, $request);

        try {
            /** @var Route $route */
            $route = $this->routeCollections[$which]->match($request);

            if ($this->doesCurrentLocaleMatchBaseUrl($request)) {
                return $this->getBaseUrlOfCurrentLocale() . $route->getUri();
            }

            return $route->getUri();
        } catch (NotFoundHttpException $e) {
            return false;
        }
    }

    private function loadRouteCollections($which, Request $request)
    {
        if ($this->routeCollections[$which] !== null) {
            return;
        }

        $this->routeCollections[$which] = new RouteCollection();
        $redirects = ($which === 'manual') ? $this->manualRedirectsManager->all() : $this->autoRedirectsManager->all();
        $baseUrl = $this->getBaseUrlOfCurrentLocale();

        foreach ($redirects as $redirect) {
            $data = $redirect->toArray();
            $from = $data['from'];

            // We have to ignore the language prefix for the route, otherwise Statamic's router does not match the request's path.
            if ($this->doesCurrentLocaleMatchBaseUrl($request)) {
                $from = preg_replace("#^{$baseUrl}(\/.*)#", '$1', $from);
            }

            $route = new Route(['GET'], $from, function () {});

            if (strpos($from, $this->getWildcardParameter()) !== false) {
                $route->where(self::WILDCARD_NAME, '(.*)');
            }

            $this->routeCollections[$which]->add($route);
        }
    }

    private function doesCurrentLocaleMatchBaseUrl(Request $request)
    {
        return $request->getBaseUrl() === $this->getBaseUrlOfCurrentLocale();
    }

    private function getWildcardParameter()
    {
        return sprintf('{%s}', self::WILDCARD_NAME);
    }

    private function getBaseUrlOfCurrentLocale()
    {
        $siteUrl = rtrim(Config::getSiteUrl(), '/');

        if (Str::startsWith($siteUrl, '/')) {
            return $siteUrl;
        }

        // https://foo.com    -> /
        // https://foo.com/fr -> /fr
        // https://foo.com/it -> /it
        preg_match('#^https?://.*(/.*)$#', $siteUrl, $matches);

        return count($matches) ? $matches[1] : '/';
    }

    private function tryLoggingAutoRedirect($route)
    {
        try {
            $this->redirectsLogger
                ->logAutoRedirect($route)
                ->flush();
        } catch (RedirectsLogParseException $e) {
            $this->logRedirectsParseException($e);
        }
    }

    private function tryLoggingManualRedirect($route)
    {
        try {
            $this->redirectsLogger
                ->logManualRedirect($route)
                ->flush();
        } catch (RedirectsLogParseException $e) {
            $this->logRedirectsParseException($e);
        }
    }

    private function logRedirectsParseException($e) {
        Log::error($e->getMessage(), ['original' => $e->getPrevious()->getMessage()]);
    }
}

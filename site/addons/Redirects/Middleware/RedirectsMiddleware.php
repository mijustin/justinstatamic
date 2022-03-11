<?php

namespace Statamic\Addons\Redirects\Middleware;

use Closure;
use Statamic\Addons\Redirects\AutoRedirect;
use Statamic\Addons\Redirects\AutoRedirectsManager;
use Statamic\Addons\Redirects\RedirectsMaintenance;
use Statamic\API\Content;

class RedirectsMiddleware
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        /** @var RedirectsMaintenance $redirectsMaintenance */
        $redirectsMaintenance = app(RedirectsMaintenance::class);

        foreach ($redirectsMaintenance->getLocalizedAutoRedirects() as $data) {
            $content = Content::find($data['contentId']);

            $autoRedirect = (new AutoRedirect())
                ->setFromUrl($data['oldUrl'])
                ->setToUrl($content->in($data['locale'])->url())
                ->setContentId($data['contentId']);

            app(AutoRedirectsManager::class)
                ->add($autoRedirect)
                ->flush();
        }
    }
}

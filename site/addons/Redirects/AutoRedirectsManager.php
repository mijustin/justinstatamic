<?php

namespace Statamic\Addons\Redirects;

class AutoRedirectsManager extends RedirectsManager
{
    /**
     * Add or update the given redirect.
     *
     * @param AutoRedirect $redirect
     *
     * @return AutoRedirectsManager
     */
    public function add(AutoRedirect $redirect)
    {
        if ($redirect->getFromUrl() === $redirect->getToUrl()) {
            return $this;
        }

        $data = $redirect->toArray();
        unset($data['from']);

        // If the target is a redirect source, remove it.
        if ($this->exists($redirect->getToUrl())) {
            $this->remove($redirect->getToUrl());
        }

        // If the source is itself a target, optimize existing redirects to directly redirect to the new target.
        collect($this->redirects)
            ->filter(function ($redirectData) use ($redirect) {
                return $redirectData['to'] === $redirect->getFromUrl();
            })
            ->each(function ($redirectData, $fromUrl) use ($redirect) {
               $this->redirects[$fromUrl]['to'] = $redirect->getToUrl();
            });

        $this->redirects[$redirect->getFromUrl()] = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($url)
    {
        parent::remove($url);

        $this->redirectsLogger->removeAutoRedirect($url);

        return $this;
    }

    /**
     * Remove all redirects affected by the given content UUID.
     *
     * @param string $contentId
     *
     * @return AutoRedirectsManager
     */
    public function removeRedirectsOfContentId($contentId)
    {
        collect($this->redirects)
            ->filter(function ($redirect) use ($contentId) {
                return $redirect['content_id'] === $contentId;
            })
            ->each(function ($redirect, $from) {
                $this->remove($from);
            });

        return $this;
    }

    /**
     * Get a redirect by URL.
     *
     * @param string $url
     *
     * @return AutoRedirect
     */
    public function get($url)
    {
        if (!$this->exists($url)) {
            return null;
        }

        $data = $this->redirects[$url];

        return (new AutoRedirect())
            ->setFromUrl($url)
            ->setToUrl($data['to'])
            ->setContentId($data['content_id']);
    }
}

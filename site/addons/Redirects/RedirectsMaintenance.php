<?php

namespace Statamic\Addons\Redirects;

class RedirectsMaintenance
{
    private $localizedAutoRedirects = [];

    public function addLocalizedAutoRedirect($contentId, $oldUrl, $locale)
    {
        $this->localizedAutoRedirects[] = [
            'contentId' => $contentId,
            'oldUrl' => $oldUrl,
            'locale' => $locale,
        ];
    }

    public function getLocalizedAutoRedirects()
    {
        return $this->localizedAutoRedirects;
    }
}

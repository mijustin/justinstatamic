<?php

namespace Statamic\Addons\Redirects;

class AutoRedirect
{
    /**
     * @var string
     */
    private $fromUrl;

    /**
     * @var string
     */
    private $toUrl;

    /**
     * @var string
     */
    private $contentId;

    public function getFromUrl()
    {
        return $this->fromUrl;
    }

    public function setFromUrl($fromUrl)
    {
        $this->fromUrl = $fromUrl;

        return $this;
    }

    public function getToUrl()
    {
        return $this->toUrl;
    }

    public function setToUrl($toUrl)
    {
        $this->toUrl = $toUrl;

        return $this;
    }

    public function getContentId()
    {
        return $this->contentId;
    }

    public function setContentId($contentId)
    {
        $this->contentId = $contentId;

        return $this;
    }

    public function toArray()
    {
        return [
            'from' => $this->fromUrl,
            'to' => $this->toUrl,
            'content_id' => $this->contentId,
        ];
    }
}

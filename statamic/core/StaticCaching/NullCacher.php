<?php

namespace Statamic\StaticCaching;

use Statamic\StaticCaching\Cacher;
use Illuminate\Http\Request;

class NullCacher implements Cacher
{
    public function cachePage(Request $request, $content)
    {
        //
    }

    public function getCachedPage(Request $request)
    {
        //
    }

    public function flush()
    {
        //
    }

    public function invalidateUrls($urls)
    {
        //
    }

    public function invalidateUrl($url)
    {
        //
    }

    public function config($key, $default = null)
    {
        //
    }
}

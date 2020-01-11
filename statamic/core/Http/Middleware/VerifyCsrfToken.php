<?php

namespace Statamic\Http\Middleware;

use Statamic\API\Config;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    public function __construct(Encrypter $encrypter)
    {
        $this->except = Config::get('system.csrf_exclude', []);

        parent::__construct($encrypter);
    }
}

<?php

namespace Statamic\Addons\Redirects;

class RedirectsLogParseException extends \Exception {
    public function __construct($filePath, $previous)
    {
        parent::__construct("Unable to parse redirects log file: '${filePath}'", 0, $previous);
    }
}

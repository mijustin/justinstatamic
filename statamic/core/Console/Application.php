<?php

namespace Statamic\Console;

use Illuminate\Console\Application as Laravel;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class Application extends Laravel
{
    public function call($command, array $parameters = [])
    {
        $this->lastOutput = new BufferedOutput;

        $this->setCatchExceptions(false);

        $result = $this->run(new ArrayInput(compact('command') + $parameters), $this->lastOutput);

        $this->setCatchExceptions(true);

        return $result;
    }
}

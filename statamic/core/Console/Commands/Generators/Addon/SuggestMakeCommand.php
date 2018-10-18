<?php

namespace Statamic\Console\Commands\Generators\Addon;

class SuggestMakeCommand extends GeneratorCommand
{
    /**
     * The type of addon class being generated.
     *
     * @var string
     */
    protected $type = 'suggest';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:suggest {name : Name of your addon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an addon suggest mode file.';
}

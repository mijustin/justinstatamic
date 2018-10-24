<?php

namespace Statamic\Addons\Janitor\Commands;

use Statamic\API\Content;
use Statamic\API\Collection;
use Statamic\Extend\Command;

require_once(__DIR__.'/../vendor/formatting.php');

class CleanCommand extends Command
{
    protected $signature = 'janitor:clean';
    protected $description = 'Clean up content';
    protected $collection;
    protected $field;

    public function handle()
    {
        $this->collection = $this->getCollection();
        $this->field = $this->getField();

        // if ($this->confirm("Do you wish to purge these {$count} fields?")) {
            $this->cleanContent();
        // }
    }

    private function getCollection()
    {
        return $this->choice('Choose a collection', Collection::handles());
    }

    private function getField()
    {
        return $this->ask('What field do you want to clean?');
    }

    private function cleanContent()
    {
        $content = Content::entries($this->collection);

        $count = 0;
        $bar = $this->output->createProgressBar($content->count());
        $bar->setFormat('%current%/%max% Updating <comment>%file%</comment>');

        foreach ($content as $item) {
            $bar->setMessage($item->path(), 'file');

            $cleanme = $item->get($this->field);

            if (is_array($cleanme)) {
                foreach ($cleanme as $key => $field) {
                    if ($field['type'] === 'text') {
                        $cleanme[$key]['text'] = wpautop($field['text']);
                    }
                }
            } else {
                $cleanme = wpautop($value);
            }

            $item->set($this->field, $cleanme);
            $item->save();

            $count++;
            $bar->advance();
        }

        $bar->setFormat(sprintf('<info>[✓]</info> Updated content files: %s/%s', $count, $content->count()));
        $bar->finish();
        $this->output->newLine();
    }
}

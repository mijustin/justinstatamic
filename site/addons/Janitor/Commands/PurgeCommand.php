<?php

namespace Statamic\Addons\Janitor\Commands;

use Statamic\API\Content;
use Statamic\API\Collection;
use Statamic\Extend\Command;

class PurgeCommand extends Command
{
    protected $signature = 'janitor:purge';
    protected $description = 'Purge unnecessary data';
    protected $collection;

    public function handle()
    {
        $this->collection = $this->getCollection();
        $this->purgeFields = $this->getPurgeFields();
        $count = count($this->purgeFields);
        if ($this->confirm("Do you wish to purge these {$count} fields?")) {
            $this->cleanContent();
        }
    }

    private function getCollection()
    {
        return $this->choice('Choose a collection', Collection::handles());
    }

    private function getPurgeFields()
    {
        $input = $this->ask('What fields do you want purge?');
        return array_map('trim', explode(',', $input));
    }

    private function cleanContent()
    {
        $content = Content::entries($this->collection);


        $purgeCount = 0;
        $bar = $this->output->createProgressBar($content->count());
        $bar->setFormat('%current%/%max% Updating <comment>%file%</comment>');

        foreach ($content as $item) {
            $bar->setMessage($item->path(), 'file');

            foreach ($this->purgeFields as $field) {
                $item->remove($field);
                $item->save();
            }

            $purgeCount++;
            $bar->advance();
        }

        $bar->setFormat(sprintf('<info>[✓]</info> Updated content files: %s/%s', $purgeCount, $content->count()));
        $bar->finish();
        $this->output->newLine();
    }
}

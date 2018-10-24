<?php

namespace Statamic\Addons\Bardify\Commands;

use Statamic\API\Content;
use Statamic\API\Fieldset;
use Statamic\Extend\Command;
use Statamic\Console\EnhancesCommands;

class BardifyCommand extends Command
{
    use EnhancesCommands;

    protected $signature = 'bardify';
    protected $description = 'Converts a Replicator field into a Bard field.';
    protected $fieldsets;
    protected $fieldset;
    protected $set;
    protected $field;
    protected $markdown;

    public function handle()
    {
        $this->fieldset = $this->getChosenFieldset();
        $this->replicator = $this->getChosenReplicator();
        $this->set = $this->getChosenSet();
        $this->field = $this->getSetField();

        $this->convertFieldset();
        $this->convertContent();
    }

    private function getSetField()
    {
        $sets = $this->fieldsets()[$this->fieldset]['fieldset']->fields()[$this->replicator]['sets'];

        $firstField = array_keys($sets[$this->set]['fields'])[0];
        $firstFieldConfig = $sets[$this->set]['fields'][$firstField];

        $this->markdown = array_get($firstFieldConfig, 'type', 'text') === 'markdown';

        return $firstField;
    }

    private function getChosenSet()
    {
        $replicator = $this->fieldsets()[$this->fieldset]['fieldset']->fields()[$this->replicator];

        $singleFieldSets = collect(
            array_get($replicator, 'sets', [])
        )->reject(function ($set) {
            return count($set['fields']) > 1;
        })->filter(function ($set) {
            $type = collect($set['fields'])->first()['type'];
            return in_array($type, ['text', 'textarea', 'markdown', 'redactor']);
        })->keys();

        if ($singleFieldSets->isEmpty()) {
            throw new \Exception("There are no single-field replicator sets in the {$this->replicator} field.");
        }

        return $this->choice('Choose a set to be the "text"', $singleFieldSets->all());
    }

    private function getChosenReplicator()
    {
        $choices = $this->fieldsets()[$this->fieldset]['replicators']->all();

        return $this->choice('Choose a Replicator field to be converted to a Bard field', $choices);
    }

    private function getChosenFieldset()
    {
        $choices = $this->fieldsets()->map(function ($item) {
            return $item['fieldset']->name();
        })->values()->all();

        return $this->choice('Choose a fieldset containing a Replicator', $choices);
    }

    private function fieldsets()
    {
        if ($this->fieldsets) {
            return $this->fieldsets;
        }

        return $this->fieldsets = collect(Fieldset::all())->map(function ($fieldset) {
            $replicators = collect($fieldset->fieldsWithPartials())->filter(function ($field) {
                return array_get($field, 'type', 'text') === 'replicator';
            })->map(function ($field, $key) {
                return $key;
            })->values();

            return compact('fieldset', 'replicators');
        })->filter(function ($item) {
            return !$item['replicators']->isEmpty();
        })->keyBy(function ($item) {
            return $item['fieldset']->name();
        });
    }

    private function convertFieldset()
    {
        $fieldset = Fieldset::get($this->fieldset);
        $fields = $fieldset->fieldsWithPartials();

        $fields[$this->replicator] = tap(array_get($fields, $this->replicator), function (&$config) {
            $config['type'] = 'bard';
            unset($config['sets'][$this->set]);

            if ($this->markdown) {
                $config['markdown'] = true;
            }
        });

        $fieldset->fields($fields);
        $fieldset->save();

        $this->checkLine("Removed the <comment>{$this->set}</comment> set from the fieldset.");
    }

    private function convertContent()
    {
        $content = Content::all()->filter(function ($item) {
            return $item->fieldset()->name() === $this->fieldset;
        });

        $convertCount = 0;
        $bar = $this->output->createProgressBar($content->count());
        $bar->setFormat('%current%/%max% Updating <comment>%file%</comment>');

        foreach ($content as $item) {
            $bar->setMessage($item->path(), 'file');
            $existing = $item->get($this->replicator);
            $converted = $this->convertContentField($existing);

            if ($converted !== $existing) {
                $item->set($this->replicator, $converted);
                $item->save();
                $convertCount++;
            }

            $bar->advance();
        }

        $bar->setFormat(sprintf('<info>[✓]</info> Updated content files: %s/%s', $convertCount, $content->count()));
        $bar->finish();
        $this->output->newLine();

        $this->outputTemplateInfo();
    }

    private function convertContentField($replicator)
    {
        return collect($replicator)->map(function ($set) {
            return ($set['type'] === $this->set)
                ? ['type' => 'text', 'text' => $set[$this->field]]
                : $set;
        })->all();
    }

    private function outputTemplateInfo()
    {
        if ($this->set === 'text' && $this->field === 'text') {
            $this->checkLine('No template changes are required because your set and field are both already named text.');
            return;
        }

        $message = collect([
            "<comment>[!] Your templates need to be manually updated.</comment>",
            '',
            "    Any references to your <info>{$this->replicator}</info> field's sets with types of <info>{$this->set}</info> should now be <info>text</info>.",
            "    Both the set type and the field name itself should both be <info>text</info>.",
            '',
            '    For example: ',
            '',
            "    {{ {$this->replicator} }}",
            "        {{ if type === \"<info>{$this->set}</info>\" }} {{ <info>{$this->field}</info> }} {{ /if }}",
            "    {{ /{$this->replicator} }}",
            '',
            '    would become:',
            '',
            "    {{ {$this->replicator} }}",
            "        {{ if type === \"<info>text</info>\" }} {{ <info>text</info> }} {{ /if }}",
            "    {{ /{$this->replicator} }}",
            '',
            '    Everything else can stay the same!',
            '',
        ])->implode("\n");

        $this->line($message);
    }
}

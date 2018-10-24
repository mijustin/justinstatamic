<?php

namespace Statamic\Addons\Bardify\Commands;

use Statamic\API\Content;
use Statamic\API\Fieldset;
use Statamic\Extend\Command;

class ContentCommand extends Command
{
    protected $signature = 'bardify:content';
    protected $description = 'Convert content fields to Bard fields';
    protected $fieldset;
    protected $field;
    protected $markdown;

    public function handle()
    {
        $this->fieldset = $this->getChosenFieldset();
        $this->field = $this->getField();
        $this->convertFieldset();
        $this->convertContent();
        $this->outputTemplateInfo();
    }

    private function getChosenFieldset()
    {
        $choices = collect(Fieldset::all())->map(function ($fieldset) {
            return $fieldset->name();
        })->values()->all();

        return $this->choice('Choose a fieldset', $choices);
    }

    private function getField()
    {
        $field = $this->ask(collect([
            "Please enter a name for the field.\n",
            " Since \"content\" is reserved for the string of data below your YAML front-matter, and Bard needs \n",
            ' to be saved as an array, you need to choose something else. For example, "story" or "body"',
        ])->implode(""));

        if ($field === 'content') {
            $this->error('You must name it something other than "content".');
            return $this->getField();
        }

        if (collect(Fieldset::get($this->fieldset)->fields())->has($field)) {
            $this->error("This fieldset already has a $field field.");
            return $this->getField();
        }

        return $field;
    }

    private function convertFieldset()
    {
        $fieldset = Fieldset::get($this->fieldset);
        $fields = $fieldset->fields();

        $config = [
            'type' => 'bard',
            'sets' => [ // An example set. Bard needs *something*.
                [
                    'type' => 'quote',
                    'quote' => [
                        'display' => 'Quote',
                        'type' => 'text',
                    ],
                    'cite' => [
                        'display' => 'Cite',
                        'type' => 'text',
                    ]
                ]
            ]
        ];

        if ($this->markdown = array_get($fields, 'content.type', 'text') === 'markdown') {
            $config['markdown'] = true;
        }

        $fields[$this->field] = $config;
        unset($fields['content']);

        $fieldset->fields($fields);
        $fieldset->save();

        $this->checkLine("Changed <comment>content</comment> to <comment>{$this->field}</comment> in the fieldset.");
        $this->line("    A <info>quote</info> set was added to the Bard field as an example.");
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

            $item->set($this->field, [
                ['type' => 'text', 'text' => $item->get('content')]
            ]);

            $item->remove('content');
            $item->save();
            $convertCount++;
            $bar->advance();
        }

        $bar->setFormat(sprintf('<info>[✓]</info> Updated content files: %s/%s', $convertCount, $content->count()));
        $bar->finish();
        $this->output->newLine();
    }

    private function outputTemplateInfo()
    {
        $textTag = $this->markdown ? 'text | markdown' : 'text';

        $message = collect([
            "<comment>[!] Your templates need to be manually updated.</comment>",
            '',
            "    Any references to your <info>content</info> field needs to be changed to <info>{$this->field}</info>.",
            "    They should also be treated as an array.",
            '',
            '    For example: ',
            '',
            "    {{ {$this->field} }}",
            '',
            '    would become:',
            '',
            "    {{ <info>{$this->field}</info> }}",
            "        {{ if type === \"text\" }} {{ <info>{$textTag}</info> }} {{ /if }}",
            "    {{ /<info>{$this->field}</info> }}",
            '',
        ])->implode("\n");

        $this->line($message);
    }
}

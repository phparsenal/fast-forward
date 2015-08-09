<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Bookmark;
use phparsenal\fastforward\Settings;

class Add extends AbstractCommand implements CommandInterface
{
    protected $name = 'add';

    /**
     * @param array $argv
     */
    public function run($argv)
    {
        $this->prepareArguments();

        $cli = $this->cli;
        try {
            // Try to create bookmark from arguments
            $cli->arguments->parse();
            $this->addCommand();
        } catch (\Exception $e) {
            // Otherwise ask for the info interactively
            $cli->arguments->usage($cli, $argv);
            $cli->br()->error($e->getMessage());
            $this->addCommandInteractive();
        }
    }

    private function addCommandInteractive()
    {
        if (!$this->client->get(Settings::INTERACTIVE)) {
            return;
        }
        $this->cli->br()->whisper('Running command interactively..');
        $bookmark = new Bookmark();
        $args = $this->cli->arguments->all();
        // Bookmark column/property name => CLImate argument name
        $tableArgMap = array(
            'command' => 'cmd',
            'description' => 'desc',
            'shortcut' => 'shortcut'
        );
        foreach ($tableArgMap as $columnName => $argumentName) {
            /** @var \League\CLImate\Argument\Argument $arg */
            $arg = $args[$argumentName];
            $prefix = '';
            if ($arg->hasPrefix()) {
                $prefix = ' [-' . $arg->prefix() . ']';
            }
            $input = $this->cli->input($arg->description() . $prefix . ":");
            // Writes the argument input to its corresponding bookmark property
            $bookmark->$columnName = $input->prompt();
        }
        $bookmark->save();
        $this->cli->info("New bookmark was saved: " . $bookmark->shortcut);
    }

    private function prepareArguments()
    {
        $this->cli->arguments->add(
            array(
                'cmd' => array(
                    'prefix' => 'c',
                    'description' => 'Command to be saved',
                    'required' => true
                ),
                'desc' => array(
                    'prefix' => 'd',
                    'description' => 'Short description of the command'
                ),
                'shortcut' => array(
                    'prefix' => 's',
                    'description' => 'Shortcut or alias used for searching',
                    'required' => true
                )
            )
        );
    }

    private function addCommand()
    {
        $args = $this->cli->arguments;
        $bookmark = new Bookmark();
        $bookmark->command = $args->get('cmd');
        if ($args->defined('desc')) {
            $bookmark->description = $args->get('desc');
        }
        $bookmark->shortcut = $args->get('shortcut');
        $bookmark->save();
        $this->cli->info("New bookmark was saved: " . $bookmark->shortcut);
    }
}

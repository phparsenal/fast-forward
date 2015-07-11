<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Bookmark;

class Update extends AbstractCommand implements CommandInterface
{
    protected $name = 'update';

    /**
     * @param array $argv
     */
    public function run($argv)
    {
        $this->prepareArguments();
        $cli = $this->cli;
        try {
            $cli->arguments->parse();
            $this->updateCommand();
        } catch (\Exception $e) {
            $cli->arguments->usage($cli, $argv);
            $cli->error($e->getMessage());
            $this->updateCommandInteractive();
        }
    }

    public function updateCommandInteractive()
    {
        $this->cli->br()->whisper('Running command interactively..');
        $bookmark = new Bookmark();
        $tableArgMap = array();
        if ($this->cli->arguments->get('shortcut') === '' || (!($this->cli->arguments->defined('shortcut')))) {
            $tableArgMap['shortcut'] = 'shortcut';
        } else {
            $this->cli->br()->out('Updating shortcut: ' . $this->cli->arguments->get('shortcut'));
        }
        $args = $this->cli->arguments->all();
        $tableArgMap['command'] ='cmd';
        $tableArgMap['description'] = 'desc';
        foreach ($tableArgMap as $columnName => $argumentName) {
            /** @var \League\CLImate\Argument\Argument $arg **/
            $arg = $args[$argumentName];
            $prefix = '';
            if ($arg->hasPrefix()) {
                $prefix = ' [-' . $arg->prefix() . ']';
            }
            $input = $this->cli->input($arg->description() . $prefix . ":");
            $in = $input->prompt();
            if ($argumentName == 'shortcut') {
                $shortcut = $in;
                $bookmark = Bookmark::select()->where('shortcut', $shortcut)->one();
                if (!$bookmark) {
                    $this->cli->br()->error($this->cli->arguments->get('shortcut') . ' does not exist. Run ff with no parameters to view a list of available shortcuts');
                    exit(0);
                }
            }
            if (strlen($in) > 0) {
                $bookmark->$columnName = $in;
            }
        }
        $bookmark->ts_modified = time();
        $bookmark->save();
        $this->cli->info("Bookmark " . $bookmark->shortcut . " has been updated");
    }

    public function prepareArguments()
    {
        $this->cli->arguments->add(
            array(
                'shortcut' => array(
                    'prefix' => 's',
                    'description' => 'Shortcut of command to update',
                    'required' => true
                ),
                'cmd' => array(
                    'prefix' => 'c',
                    'description' => 'New command',
                ),
                'desc' => array(
                    'prefix' => 'd',
                    'description' => 'Short description of the command'
                )
            )
        );
    }

    public function updateCommand()
    {
        $args = $this->cli->arguments;
        $bookmark = Bookmark::select()->where('shortcut', $args->get('shortcut'))->one();
        if (!$bookmark) {
            $this->cli->br()->error($this->cli->arguments->get('shortcut') . ' does not exist. Run ff with no parameters to view a list of available shortcuts');
            exit(0);
        }
        if ($args->defined('desc')) {
            $bookmark->description = $args->get('desc');
        }
        if ($args->defined('cmd')) {
            $bookmark->command = $args->get('cmd');
        }
        $bookmark->ts_modified = time();
        $bookmark->save();
        $this->cli->info("Bookmark " . $bookmark->shortcut . " has been updated");
    }
}
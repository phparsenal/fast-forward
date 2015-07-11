<?php

namespace phparsenal\fastforward\Command;


use nochso\ORM\Model;
use phparsenal\fastforward\Model\Bookmark;

class Delete extends AbstractCommand implements CommandInterface
{
    protected $name = 'delete';

    /**
     * @param array $argv
     */
    public function run($argv)
    {
        $this->prepareArguments();
        $cli = $this->cli;
        try {
            $cli->arguments->parse();
            $this->deleteCommand();
        } catch (\Exception $e) {
            $cli->arguments->usage($cli, $argv);
            $cli->error($e->getMessage());
            $this->deleteCommandInteractive();
        }
    }

    public function prepareArguments()
    {
        $this->cli->arguments->add(
            array(
                'delete' => array(
                    'description' => 'Command to delete a bookmark',
                    'required' => true
                ),
                'shortcut' => array(
                    'description' => 'Shortcut of bookmark to delete',
                    'required' => true
                ),
            )
        );
    }

    public function deleteCommand()
    {
        $bookmark = Bookmark::select()->where('shortcut', $this->cli->arguments->get('shortcut'))->all();
        if ($bookmark->count() <= 0) {
            $this->cli->br()->error($this->cli->arguments->get('shortcut') . ' does not exist. Please try again with a valid shortcut');
            exit(0);
        }
        $bookmark->delete();
        $this->cli->info("Bookmark " . $this->cli->arguments->get('shortcut') . " deleted successfully...");
    }

    public function deleteCommandInteractive()
    {
        $this->cli->br()->whisper('Running command interactively..');
        $args = $this->cli->arguments->all();
        $bookmark = new Bookmark();
        // Bookmark column/property name => CLImate argument name
        $tableArgMap = array(
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
            $shortcut = $input->prompt();
            $bookmark = Bookmark::select()->where('shortcut', $shortcut)->one();
            if ($bookmark === null) {
                $this->cli->br()->error($shortcut . ' does not exist. Please try again with a valid shortcut');
                exit(0);
            }
        }
        $shortcut = $bookmark->shortcut;
        $bookmark->delete();
        $this->cli->info("Bookmark " . $shortcut . " deleted successfully...");
    }
}
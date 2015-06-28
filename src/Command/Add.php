<?php

namespace phparsenal\fastforward\Command;


use phparsenal\fastforward\Model\Bookmark;

class Add extends AbstractCommand implements CommandInterface
{
    protected $name = 'add';

    /**
     * @param array $argv
     */
    public function run($argv)
    {
        $this->prepareArguments();

        try {
            $this->cli->arguments->parse();
        } catch (\Exception $e) {
            $this->cli->arguments->usage($this->cli, $argv);
            $this->cli->br();
            $this->cli->error($e->getMessage());
            return;
        }
        $this->addCommand();
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
        $this->cli->out("New bookmark was saved: " . $bookmark->shortcut);
    }


}
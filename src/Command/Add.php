<?php

namespace phparsenal\fastforward\Command;


use phparsenal\fastforward\Model\Bookmark;

class Add implements CommandInterface
{
    private $name = 'add';

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function run()
    {
        $bookmark = new Bookmark();
        $count = count($args);
        // Get as much as you can
        switch ($count) {
            case 3:
                $bookmark->command = $args[2];
            case 2:
                $bookmark->description = $args[1];
            case 1:
                $bookmark->shortcut = $args[0];
        }

        // Ask for what's left
        switch ($count) {
            case 0:
                $bookmark->shortcut = Streams::prompt("Shortcut for easy searching");
            case 1:
                $bookmark->description = Streams::prompt("The description of the command");
            case 2:
                $bookmark->command = Streams::prompt("Command to be executed");
        }
        $bookmark->save();
        Streams::out("New bookmark was saved: " . $bookmark->shortcut);
    }

}
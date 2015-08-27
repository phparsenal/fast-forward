<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Bookmark;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Run extends InteractiveCommand
{
    protected function configure()
    {
        $this->setName('run')
            ->setDescription('Search and execute a command')
            ->addArgument('shortcut', InputArgument::OPTIONAL, 'Full shortcut or only beginning of it to search', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shortcut = $input->getArgument('shortcut');
        $bookmarks = Bookmark::select()
            ->sortAndLimit($this->getApplication())
            ->like('shortcut', $shortcut . '%')
            ->all()->toArray();

        $match = $this->tryExactMatch($bookmarks, $shortcut);
        if ($match === null) {
            $match = $this->selectMatch($bookmarks);
        }
        if ($match === null) {
            if (!$this->addWhenEmpty()) {
                $this->out->note("There are no bookmarks matching shortcut: '" . $shortcut . "'");
            }
        } else {
            // Run the selected bookmark
            $match->run($this->getApplication(), $this->out);
        }
    }

    /**
     * @param Bookmark[] $bookmarks
     * @param $shortcut
     *
     * @return null|Bookmark
     */
    private function tryExactMatch($bookmarks, $shortcut)
    {
        if (count($bookmarks) === 1) {
            $bm = $bookmarks[0];
            if ($bm->shortcut === $shortcut) {
                return $bm;
            }
        }
        return null;
    }

    /**
     * @param Bookmark[] $bookmarks
     *
     * @return mixed|null
     */
    private function selectMatch($bookmarks)
    {
        if (count($bookmarks) === 0) {
            return null;
        }
        Bookmark::table($this->out, $bookmarks);
        $answer = $this->out->ask('Enter # of command to run');
        if ($answer !== null && ctype_digit($answer) && $answer >= 0 && $answer < count($bookmarks)) {
            return $bookmarks[$answer];
        }
        return null;
    }

    /**
     * @return bool True when there are no bookmarks.
     *
     * @throws \Exception
     */
    private function addWhenEmpty()
    {
        if (Bookmark::select()->count() === 0) {
            $this->out->note("You don't have any commands saved yet. Now showing the help for the add command:");
            $addCommand = $this->getApplication()->find('help');
            $args = array('command' => 'help', 'command_name' => 'add');
            $argsInput = new ArrayInput($args);
            $addCommand->run($argsInput, $this->out);
            return true;
        }
        return false;
    }
}

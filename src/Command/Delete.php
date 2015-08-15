<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Bookmark;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Delete extends InteractiveCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('delete')
            ->setDescription('Delete a command')
            ->addArgument('shortcut', InputArgument::REQUIRED, 'Shortcut of bookmark to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shortcut = $input->getArgument('shortcut');
        $count = Bookmark::select()->where('shortcut', $shortcut)->count();
        if ($count === 0) {
            $output->writeln("'{$shortcut}' does not exist. Please try again with a valid shortcut.");
        } else {
            Bookmark::select()->where('shortcut', $shortcut)->delete();
            $output->writeln("<info>Bookmarks deleted: {$count}</info>");
        }
    }
}

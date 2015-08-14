<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Bookmark;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Add extends Command
{
    protected function configure()
    {
        $this->setName('add')
            ->setDescription('Save a command')
            ->addArgument('shortcut', InputArgument::REQUIRED, 'Shortcut or alias used for searching')
            ->addArgument('cmd', InputArgument::REQUIRED, 'Command to be saved')
            ->addOption('description', 'd', InputOption::VALUE_REQUIRED,
                'Short description of what the command does', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bookmark = new Bookmark();
        $bookmark->command = $input->getArgument('cmd');
        $bookmark->shortcut = $input->getArgument('shortcut');
        $bookmark->description = $input->getOption('description');
        $bookmark->save();
        $output->writeln('New bookmark was saved: ' . $bookmark->shortcut);
    }
}

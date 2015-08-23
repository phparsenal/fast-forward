<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Bookmark;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends InteractiveCommand
{
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('Update a command')
            ->addArgument('shortcut', InputArgument::REQUIRED, 'Shortcut of command to update')
            ->addArgument('cmd', InputArgument::OPTIONAL, 'New command')
            ->addOption('description', 'd', InputOption::VALUE_REQUIRED, 'Short description of the command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shortcut = $input->getArgument('shortcut');
        $command = $input->getArgument('cmd');
        $description = $input->getOption('description');

        $bookmark = Bookmark::select()
            ->where('shortcut', $shortcut)
            ->one();
        if ($bookmark === null) {
            throw new \Exception("'{$shortcut}' does not exist. Please try again with a valid shortcut.");
        }

        if ($command !== null) {
            $bookmark->command = $command;
        }
        if ($description !== null) {
            $bookmark->description = $description;
        }
        $bookmark->ts_modified = time();
        $bookmark->save();
        $output->writeln('   Shortcut: ' . $bookmark->shortcut);
        $output->writeln('    Command: ' . $bookmark->command);
        $output->writeln('Description: ' . $bookmark->description);
        $this->out->success('Bookmark updated.');
    }
}

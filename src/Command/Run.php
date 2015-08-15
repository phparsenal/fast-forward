<?php

namespace phparsenal\fastforward\Command;

use NateDrake\DateHelper\DateFormat;
use phparsenal\fastforward\Client;
use phparsenal\fastforward\Model\Bookmark;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Run extends InteractiveCommand
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

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
            ->sortAndLimit($this->client)
            ->like('shortcut', $shortcut . '%')
            ->all()->toArray();

        $match = $this->tryExactMatch($bookmarks, $shortcut);
        if ($match === null) {
            $match = $this->selectMatch($bookmarks, $input, $output);
        }
        if ($match === null) {
            $this->addWhenEmpty($output);
        } else {
            // Run the selected bookmark
            $match->run($this->client);
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
     * @param Bookmark[]      $bookmarks
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed|null
     */
    private function selectMatch($bookmarks, $input, $output)
    {
        if (count($bookmarks) === 0) {
            return null;
        }
        $this->listBookmarks($bookmarks, $output);
        $helper = $this->getHelper('question');
        $question = new Question('Enter # of command: ');
        $answer = $helper->ask($input, $output, $question);
        if ($answer !== null && ctype_digit($answer) && $answer >= 0 && $answer < count($bookmarks)) {
            return $bookmarks[$answer];
        }
        return null;
    }

    /**
     * @param Bookmark[] $bookmarks
     * @param $output
     */
    private function listBookmarks($bookmarks, $output)
    {
        $table = new Table($output);
        $table->setHeaders(array(
            '#',
            'Shortcut',
            'Description',
            'Command',
            'Hits',
            'Modified',
        ));
        foreach ($bookmarks as $key => $bm) {
            $table->addRow(array(
                $key,
                $bm->shortcut,
                $bm->description,
                $bm->command,
                $bm->hit_count,
                $bm->ts_modified === '' ? 'never' : DateFormat::epochDate($bm->ts_modified, DateFormat::BIG)
            ));
        }
        $table->render();
    }

    private function addWhenEmpty(OutputInterface $output)
    {
        if (Bookmark::select()->count() === 0) {
            $output->writeln("You don't have any commands saved yet. Now showing the help for the add command:");
            $output->writeln('');
            $addCommand = $this->getApplication()->find('help');
            $args = array('command' => 'help', 'command_name' => 'add');
            $argsInput = new ArrayInput($args);
            $addCommand->run($argsInput, $output);
        }
    }
}

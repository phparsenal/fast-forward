<?php

namespace phparsenal\fastforward\Command;

use NateDrake\DateHelper\DateFormat;
use phparsenal\fastforward\Client;
use phparsenal\fastforward\Model\Bookmark;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\OutputStyle;

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

    protected function execute(InputInterface $input, OutputStyle $output)
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
     * @param Bookmark[]     $bookmarks
     * @param InputInterface $input
     * @param OutputStyle    $output
     *
     * @return mixed|null
     */
    private function selectMatch($bookmarks, $input, $output)
    {
        if (count($bookmarks) === 0) {
            return null;
        }
        $this->listBookmarks($bookmarks, $output);
        $answer = $output->ask('Enter # of command to run');
        if ($answer !== null && ctype_digit($answer) && $answer >= 0 && $answer < count($bookmarks)) {
            return $bookmarks[$answer];
        }
        return null;
    }

    /**
     * @param Bookmark[]  $bookmarks
     * @param OutputStyle $output
     */
    private function listBookmarks($bookmarks, $output)
    {
        $headers = array(
            '#',
            'Shortcut',
            'Description',
            'Command',
            'Hits',
            'Modified',
        );
        $rows = array();
        foreach ($bookmarks as $key => $bm) {
            $rows[] = array(
                $key,
                $bm->shortcut,
                $bm->description,
                $bm->command,
                $bm->hit_count,
                $bm->ts_modified === '' ? 'never' : DateFormat::epochDate($bm->ts_modified, DateFormat::BIG),
            );
        }
        $output->table($headers, $rows);
    }

    private function addWhenEmpty(OutputStyle $output)
    {
        if (Bookmark::select()->count() === 0) {
            $output->note("You don't have any commands saved yet. Now showing the help for the add command:");
            $addCommand = $this->getApplication()->find('help');
            $args = array('command' => 'help', 'command_name' => 'add');
            $argsInput = new ArrayInput($args);
            $addCommand->run($argsInput, $output);
        }
    }
}

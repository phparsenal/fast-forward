<?php

namespace phparsenal\fastforward\Command;


use cli\Streams;
use phparsenal\fastforward\Model\Bookmark;

class Run extends AbstractCommand implements CommandInterface
{
    protected $name = 'run';

    /**
     * @param array $argv
     */
    public function run($argv)
    {
        $this->cli->arguments->add(
            array(
                'search' => array(
                    'description' => 'Search term for the shortcut',
                    'defaultValue' => ''
                )
            )
        );
        try {
            $this->cli->arguments->parse();
        } catch (\Exception $e) {
            $this->cli->arguments->usage($this->cli, $argv);
            $this->cli->br();
            $this->cli->error($e->getMessage());
            return;
        }

        // I couldn't figure out how to make CLImate "catch all" into a single argument.
        $this->runBookmark(array_slice($argv, 1));
    }

    private function runBookmark($searchTerms)
    {
        $query = Bookmark::select();
        foreach ($searchTerms as $term) {
            $query->like('shortcut', $term . '%');
        }
        $query->orderDesc('hit_count');
        $bookmarks = $query->all();
        $bm = $this->selectBookmark($bookmarks, $searchTerms);
        if ($bm !== null) {
            $bm->run($this->client);
        }
    }

    /**
     * @param $bookmarks
     * @param $searchTerms
     * @return null
     * @throws \Exception
     */
    private function selectBookmark($bookmarks, $searchTerms)
    {
        if (count($bookmarks) == 1) {
            /** @var Bookmark $bm */
            $bm = $bookmarks->current();
            if (isset($searchTerms[0])) {
                if ($bm->shortcut == $searchTerms[0]) {
                    return $bm;
                }
            }
        }

        $map = array();
        $i = 0;
        $table = new \cli\Table();
        $headers = array('#', 'Shortcut', 'Description', 'Command', 'Hits');
        $table->setHeaders($headers);
        $rows = array();
        foreach ($bookmarks as $id => $bm) {
            $map[$i] = $id;
            $rows[] = array($i, $bm->shortcut, $bm->description, $bm->command, $bm->hit_count);
            $i++;
        }
        $table->setRows($rows);
        $r = new \cli\table\Ascii();
        $r->setCharacters(array(
            'corner' => '',
            'line' => '',
            'border' => ' ',
            'padding' => '',
        ));
        $table->setRenderer($r);
        $table->display();
        Streams::out("Which # do you want to run? ");
        $num = Streams::input();
        if (isset($map[$num])) {
            return $bookmarks[$map[$num]];
        }
        return null;
    }
}
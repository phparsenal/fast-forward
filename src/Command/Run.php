<?php

namespace phparsenal\fastforward\Command;

use phparsenal\fastforward\Model\Bookmark;
use NateDrake\DateHelper\DateFormat;

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
     * @return null|Bookmark
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
        $rows = array();
        $rePattern = "/(" . implode($searchTerms, '|') . ")/i";
        // TODO Make highlighting mode configurable
        $highlightMode = 'invert';
        $reReplacement = "<$highlightMode>\\1</$highlightMode>";
        foreach ($bookmarks as $id => $bm) {
            $map[$i] = $id;
            $rows[] = array(
                '#' => $i,
                'Shortcut' => preg_replace($rePattern, $reReplacement, $bm->shortcut),
                'Description' => $bm->description,
                'Command' => $bm->command,
                'Hits' => $bm->hit_count,
                'Modified' => ($bm->ts_modified !== '') ? DateFormat::epochDate((int)$bm->ts_modified, DateFormat::BIG) : 'never'
            );
            $i++;
        }
        if (!(count($rows))) {
            $this->cli->out('No bookmarks saved. You will now be prompted to add a bookmark!');
            $add = new Add($this->client);
            $add->run(array());
        } else {
            $this->client->getCLI()->table($rows);
            $input = $this->client->getCLI()->input("Which # do you want to run?");
            $input->accept(function ($response) use ($map) {
                return isset($map[$response]);
            });
            $num = $input->prompt();
            if (isset($map[$num])) {
                return $bookmarks[$map[$num]];
            }
        }
        return null;
    }
}

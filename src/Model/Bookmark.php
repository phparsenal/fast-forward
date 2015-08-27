<?php

namespace phparsenal\fastforward\Model;

use NateDrake\DateHelper\DateFormat;
use nochso\ORM\Model;
use phparsenal\fastforward\Client;
use phparsenal\fastforward\OS;
use phparsenal\fastforward\Settings;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\StyleInterface;

class Bookmark extends Model
{
    protected static $_tableName = 'bookmark';

    #region Table columns
    /**
     * Primary key of the bookmark
     *
     * @var int
     */
    public $id;

    /**
     * A short name by which you find the bookmark
     *
     * @var string
     */
    public $shortcut = '';

    /**
     * Describes what the bookmark is or does
     *
     * @var string
     */
    public $description = '';

    /**
     * The command to be execute
     *
     * @var string
     */
    public $command = '';

    /**
     * The amount of times this bookmark was opened
     *
     * @var int
     */
    public $hit_count = 0;

    /**
     * UTC timestamp
     *
     * @var int
     */
    public $ts_created;

    /**
     * UTC timestamp of last modification
     *
     * @var int
     */
    public $ts_modified = '';
    #endregion

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (strlen($this->shortcut) === 0) {
            throw new \Exception('Shortcut must not be empty.');
        }
        if (strlen($this->command) === 0) {
            throw new \Exception('Command must not be empty.');
        }
        if ($this->ts_created === null) {
            $this->ts_created = time();
        }
        parent::save();
    }

    /**
     * @param Client      $client
     * @param OutputStyle $output
     *
     * @throws \Exception
     */
    public function run($client, OutputStyle $output)
    {
        $this->hit_count++;
        $output->success("Running '" . $this->shortcut . "' for the " . $client->ordinal($this->hit_count) . ' time.');
        $command = $client->getSettings()->parseIdentifiers($this->command);
        switch (OS::getType()) {
            case OS::LINUX:
                $output->writeln('cmd:' . $command);
                break;
            case OS::WINDOWS:
                file_put_contents($client->getBatchPath(), $command);
                break;
        }
        $this->save();
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function sortAndLimit($client)
    {
        // Make sure we have a valid column to sort by
        $sortColumn = $client->getSetting(Settings::SORT);
        $columnMap = $this->toAssoc();
        if (!isset($columnMap[$sortColumn])) {
            $sortColumn = 'hit_count';
        }
        // Large hit counts and latest time stamps come first
        if ($sortColumn === 'hit_count' || substr($sortColumn, 0, 3) === 'ts_') {
            $this->orderDesc($sortColumn);
        } else {
            $this->orderAsc($sortColumn);
        }

        // Only limit when set
        $maxRows = $client->getSetting(Settings::LIMIT);
        if ($maxRows > 0) {
            $this->limit($maxRows);
        }
        return $this;
    }

    /**
     * @param StyleInterface $out
     * @param Bookmark[]     $bookmarks
     * @param array          $extra
     */
    public static function table(StyleInterface $out, $bookmarks, $extra = array())
    {
        $headers = array_merge(self::getTableHeaders(), $extra);
        $out->table($headers, self::getTableRows($bookmarks, $extra));
    }

    private static function getTableHeaders()
    {
        return array(
            '#',
            'Shortcut',
            'Description',
            'Command',
            'Hits',
            'Modified',
        );
    }

    /**
     * @param Bookmark[] $bookmarks
     * @param array      $extra
     *
     * @return array
     */
    private static function getTableRows($bookmarks, $extra = array())
    {
        $rows = array();
        foreach ($bookmarks as $key => $bm) {
            $row = array(
                $key,
                $bm->shortcut,
                $bm->description,
                $bm->command,
                $bm->hit_count,
                $bm->ts_modified === '' ? 'never' : DateFormat::epochDate($bm->ts_modified, DateFormat::BIG),
            );
            foreach ($extra as $key) {
                $row[] = $bm->extra($key);
            }
            $rows[] = $row;
        }
        return $rows;
    }
}

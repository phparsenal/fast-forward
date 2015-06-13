<?php
namespace phparsenal\fastforward\Model;

use cli\Streams;
use nochso\ORM\Model;
use phparsenal\fastforward\Client;

class Bookmark extends Model
{
    protected static $_tableName = 'bookmark';

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
     * Ensure ts_created is set
     */
    public function save()
    {
        if ($this->ts_created == null) {
            $this->ts_created = time();
        }
        parent::save();
    }

    /**
     * @param Client $client
     */
    public function run($client)
    {
        file_put_contents($client->getBatchPath(), $this->command);
        $this->hit_count++;
        $this->save();
        Streams::out("Running '" . $this->shortcut . "' for the " . $client->ordinal($this->hit_count) . " time.\n");
        Streams::out($this->command . "\n");
    }
}

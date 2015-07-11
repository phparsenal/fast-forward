<?php
namespace phparsenal\fastforward\Model;

use nochso\ORM\Model;
use phparsenal\fastforward\Client;
use phparsenal\fastforward\OS;

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
        $client->getCLI()->info("Running '" . $this->shortcut . "' for the " . $client->ordinal($this->hit_count) . " time.");
        switch (OS::getType()) {
            case OS::LINUX:
                // Disable Ansi to keep the output clean
                $client->getCLI()->forceAnsiOff();
                $client->getCLI()->br()->out("cmd:" . $this->command)->br();
                break;
            case OS::WINDOWS:
                file_put_contents($client->getBatchPath(), $this->command);
                break;
        }
        $this->hit_count++;
        $this->save();
    }
}

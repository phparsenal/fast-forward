<?php
namespace phparsenal\fastforward;

use cli\Streams;
use nochso\ORM\DBA\DBA;

class Client
{
    /**
     * @var string
     */
    private $folder;

    /**
     * @var string
     */
    private $batchPath;

    /**
     * @var array
     */
    private $args;

    function __construct()
    {
        $this->init();
    }

    /**
     * Get folder path and connect to the database
     */
    private function init()
    {
        $this->folder = dirname(dirname(__FILE__));
        chdir($this->folder);

        // Prevent the previous command from being executed in case anything fails later on
        $this->batchPath = $this->folder . DIRECTORY_SEPARATOR . 'cli-launch.temp.bat';
        file_put_contents($this->batchPath, '');
        DBA::connect('sqlite:' . $this->folder . '/db.sqlite', '', '');
        $this->ensureSchema();
    }

    /**
     * @param array $argv
     */
    public function run($argv)
    {
        $this->args = $argv;

        if (count($this->args) > 1) {
            // ff add <args>
            if ($this->args[1] == "add") {
                $this->addBookmark(array_slice($this->args, 2));
            } else {
                // ff <search>
                $this->runBookmark(array_slice($this->args, 1));
            }
        } else {
            // Show a list and let the user decide
            // ff
            $this->runBookmark(array());
        }
    }

    public function addBookmark($args)
    {
        $bookmark = new Model\Bookmark();
        $count = count($args);
        // Get as much as you can
        switch ($count) {
            case 3:
                $bookmark->command = $args[2];
            case 2:
                $bookmark->description = $args[1];
            case 1:
                $bookmark->shortcut = $args[0];
        }

        // Ask for what's left
        switch ($count) {
            case 0:
                $bookmark->shortcut = Streams::prompt("Shortcut for easy searching");
            case 1:
                $bookmark->description = Streams::prompt("The description of the command");
            case 2:
                $bookmark->command = Streams::prompt("Command to be executed");
        }
        $bookmark->save();
        Streams::out("New bookmark was saved: " . $bookmark->shortcut);
    }

    /**
     * @param $args
     */
    public function runBookmark($args)
    {
        $query = Model\Bookmark::select();
        foreach ($args as $arg) {
            $query->like('shortcut', $arg . '%');
        }
        $query->orderDesc('hit_count');
        $bookmarks = $query->all();
        $bm = $this->selectBookmark($bookmarks, $args);
        if ($bm !== null) {
            $bm->run($this);
        }
    }

    /**
     * @param $bookmarks
     * @param array $args
     * @return Model\Bookmark|null
     * @throws Exception
     */
    public function selectBookmark($bookmarks, $args)
    {
        if (count($bookmarks) == 1) {
            /** @var Model\Bookmark $bm */
            $bm = $bookmarks->current();
            if(isset($args[0])) {
                if ($bm->shortcut == $args[0]) {
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
            'corner'  => '',
            'line'    => '',
            'border'  => '',
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

    /**
     * Prepares the database when it is new.
     */
    public function ensureSchema()
    {
        $sql = "SELECT COUNT(*) FROM sqlite_master";
        $count = (int)DBA::execute($sql)->fetchColumn();
        if ($count !== 0) {
            return;
        }
        Streams::out("Database is new. Trying to set up database schema..\n");
        $schemaPath = "asset/model.sql";
        $exit = false;
        if (!is_file($schemaPath)) {
            Streams::out("Schema file could not be found: $schemaPath\n");
            Streams::out("Please make sure that you have this file.\n");
            Streams::out("\nExiting.\n");
            exit;
        }
        $schemaSql = file_get_contents($schemaPath);
        if ($schemaSql === false) {
            Streams::out("Unable to read schema file: " . $schemaPath . "\n");
            Streams::out("\nExiting.\n");
            exit;
        }
        $schemaSqlList = explode(';', $schemaSql);
        $count = count($schemaSqlList);
        foreach ($schemaSqlList as $key => $singleSql) {
            echo "\r" . ($key + 1) . '/' . $count . ' ';
            $statement = DBA::prepare($singleSql);
            if ($statement->execute() === true) {
                Streams::out("Ok.");
            } else {
                Streams::out("Failed:\n");
                var_dump($statement->errorInfo());
                Streams::out("\nWhile trying to run:\n");
                Streams::out($singleSql . "\n");
                Streams::out("Exiting.\n");
                exit;
            }
        }
        Streams::out("\nDatabase is ready.\n");
    }

    /**
     * Converts an integer to its ordinal number
     *
     * <code>
     * ordinal(1) === "1st"
     * ordinal(32) === "32nd"
     * </code>
     *
     * @param int $number
     * @return string
     */
    public function ordinal($number)
    {
        if(is_int($number)) {
            $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
            if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
                return $number . 'th';
            } else {
                return $number . $ends[$number % 10];
            }
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getBatchPath()
    {
        return $this->batchPath;
    }
}
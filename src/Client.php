<?php
namespace phparsenal\fastforward;

use League\CLImate\CLImate;
use nochso\ORM\DBA\DBA;
use phparsenal\fastforward\Command\AbstractCommand;
use phparsenal\fastforward\Command\Add;
use phparsenal\fastforward\Command\Run;

class Client
{
    const FF_VERSION = '0.1.0';
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

    /**
     * @var CLImate
     */
    private $cli;

    /**
     * @var AbstractCommand[]
     */
    private $commands = array();

    /**
     * Get folder path and connect to the database
     */
    public function init()
    {
        $this->cli = new CLImate();
        $this->cli->description('fast-forward ' . self::FF_VERSION);
        if (OS::isType(OS::LINUX)) {
            $this->cli->forceAnsiOn();
        }
        $this->folder = dirname(dirname(__FILE__));
        chdir($this->folder);

        // Prevent the previous command from being executed in case anything fails later on
        $this->batchPath = $this->folder . DIRECTORY_SEPARATOR . 'cli-launch.temp.bat';
        file_put_contents($this->batchPath, '');
        DBA::connect('sqlite:' . $this->folder . '/db.sqlite', '', '');
        $migration = new Migration($this);
        $migration->run();
    }

    /**
     * @param array $argv
     */
    public function run($argv)
    {
        $this->args = $argv;

        // Build a list of available commands
        $run = new Run($this);
        /** @var AbstractCommand[] $commands */
        $commands = array(
            new Add($this),
            $run
        );
        foreach ($commands as $command) {
            $this->commands[$command->getName()] = $command;
        }

        // Look for a matching command
        $commandFound = false;
        if (count($this->args) > 1) {
            $needle = $this->args[1];
            if (isset($this->commands[$needle])) {
                $this->commands[$needle]->run($this->args);
                $commandFound = true;
            }
        }

        // Otherwise run the default "run" command
        if (!$commandFound) {
            $run->run($this->args);
        }
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
        if (is_int($number)) {
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

    /**
     * @return CLImate
     */
    public function getCLI()
    {
        return $this->cli;
    }
}

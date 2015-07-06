<?php
namespace phparsenal\fastforward;

use League\CLImate\CLImate;
use nochso\ORM\DBA\DBA;
use phparsenal\fastforward\Command\AbstractCommand;
use phparsenal\fastforward\Command\Add;
use phparsenal\fastforward\Command\Run;
use phparsenal\fastforward\Command\Set;
use phparsenal\fastforward\Model\Setting;

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
            new Set($this),
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
        // Cast only to int when it's a string with digits only
        if (is_string($number) && ctype_digit($number)) {
            $number = (int)$number;
        }
        if (is_int($number)) {
            $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
            if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
                return $number . 'th';
            } else {
                return $number . $ends[$number % 10];
            }
        }
        return false;
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

    /**
     * Saves a setting as a key/value pair
     *
     * @param string $key Any string that does not contain spaces
     * @param string $value
     * @throws \Exception
     */
    public function set($key, $value)
    {
        if (strpos($key, ' ') !== false) {
            throw new \Exception('Error while trying to save setting "' . $key . '": Key name must not contain spaces.');
        }
        $setting = $this->get($key, true);
        if ($setting === null) {
            $setting = new Setting();
            $setting->key = $key;
        }

        if ($setting->value === null) {
            $this->cli
                ->out("Inserting new setting:")
                ->out("$key = $value");
        } elseif ($setting->value !== $value) {
            $this->cli
                ->out("Changing setting:")
                ->out("$key = {$setting->value} --> <bold>$value</bold>");
        } else {
            $this->cli
                ->out("Setting already up-to-date:")
                ->out("$key = $value");
        }

        $setting->value = $value;
        $setting->save();
    }

    /**
     * Return the string or Model value for $key
     *
     * @param string $key
     * @param bool $returnModel Returns a model instance when true
     * @return null|string|Setting
     */
    public function get($key, $returnModel = false)
    {
        $setting = Setting::select()->eq('key', $key)->one();
        if ($returnModel || $setting === null) {
            return $setting;
        }
        return $setting->value;
    }
}

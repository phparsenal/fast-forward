<?php

namespace phparsenal\fastforward;

use nochso\ORM\DBA\DBA;
use phparsenal\fastforward\Command\Add;
use phparsenal\fastforward\Command\Delete;
use phparsenal\fastforward\Command\Run;
use phparsenal\fastforward\Command\Set;
use phparsenal\fastforward\Command\Update;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

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
     * @var \Symfony\Component\Console\Style\OutputStyle
     */
    private $output;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * Get folder path and connect to the database
     */
    public function init()
    {
        $this->folder = dirname(dirname(__FILE__));
        chdir($this->folder);
        DBA::connect('sqlite:' . $this->folder . '/db.sqlite', '', '');
        $this->settings = new Settings($this);

        $migration = new Migration($this);
        $migration->run();

        // Prevent the previous command from being executed in case anything fails later on
        $this->batchPath = $this->folder . DIRECTORY_SEPARATOR . 'cli-launch.temp.bat';
        file_put_contents($this->batchPath, '');
    }

    public function run()
    {
        $application = new Application('fast-forward', self::FF_VERSION);
        $run = new Run($this);
        $application->add($run);
        $application->setDefaultCommand($run->getName());
        $application->add(new Add());
        $application->add(new Delete());
        $application->add(new Set($this));
        $application->add(new Update());
        $argvIn = $this->prepareArgv();
        $this->output = new ConsoleStyle($argvIn, new ConsoleOutput());
        $application->run($argvIn, $this->output);
    }

    /**
     * Returns a InputInterface imposing non-interactive mode.
     *
     * This will append the --no-interaction option if ff.interactive is disabled.
     *
     * @return ArgvInput
     */
    private function prepareArgv()
    {
        $argv = $_SERVER['argv'];
        $input = null;
        if (!$this->get(Settings::INTERACTIVE)) {
            if (!in_array('-n', $argv) && !in_array('--no-interaction', $argv)) {
                $argv[] = '-n';
            }
        }
        return new ArgvInput($argv);
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
     *
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
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Saves a setting as a key/value pair
     *
     * @param string $key   Any string that does not contain spaces
     * @param string $value
     *
     * @throws \Exception
     */
    public function set($key, $value)
    {
        $this->settings->set($key, $value);
    }

    /**
     * Return the string or Model value for $key
     *
     * @param string $key
     * @param bool   $returnModel Returns a model instance when true
     *
     * @return null|string|\phparsenal\fastforward\Model\Setting
     */
    public function get($key, $returnModel = false)
    {
        return $this->settings->get($key, $returnModel);
    }

    /**
     * @return \Symfony\Component\Console\Style\OutputStyle
     */
    public function getOutput()
    {
        return $this->output;
    }
}

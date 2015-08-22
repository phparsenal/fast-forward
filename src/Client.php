<?php

namespace phparsenal\fastforward;

use nochso\ORM\DBA\DBA;
use phparsenal\fastforward\Command\Add;
use phparsenal\fastforward\Command\Delete;
use phparsenal\fastforward\Command\Export;
use phparsenal\fastforward\Command\Import;
use phparsenal\fastforward\Command\Run;
use phparsenal\fastforward\Command\Set;
use phparsenal\fastforward\Command\Update;
use phparsenal\fastforward\Console\ConsoleStyle;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Client extends Application
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
     * @var \Symfony\Component\Console\Style\OutputStyle
     */
    private $output;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * Constructor.
     *
     * @param string $name    The name of the application
     * @param string $version The version of the application
     *
     * @api
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
    }

    public function init(OutputInterface $output = null, $dbPath = null)
    {
        $this->setDefaultCommand('run');
        if ($output === null) {
            $output = new ConsoleOutput();
        }
        $this->output = new ConsoleStyle(new ArgvInput(), $output);
        $this->folder = dirname(dirname(__FILE__));
        chdir($this->folder);
        if ($dbPath === null) {
            $dbPath = $this->folder . '/db.sqlite';
        }
        DBA::connect('sqlite:' . $dbPath, '', '');
        $this->settings = new Settings($this);

        $migration = new Migration($this);
        $migration->run();

        // Prevent the previous command from being executed in case anything fails later on
        $this->batchPath = $this->folder . DIRECTORY_SEPARATOR . 'cli-launch.temp.bat';
        file_put_contents($this->batchPath, '');
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws \Exception When doRun returns Exception
     *
     * @api
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleStyle($input, new ConsoleOutput());
        }
        $this->output = $output;
        return parent::run($input, $output);
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return Command[] An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        $defaults = array(
            new Run(),
            new Add(),
            new Delete(),
            new Set(),
            new Update(),
            new Import(),
            new Export(),
        );
        return array_merge(parent::getDefaultCommands(), $defaults);
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
    public function setSetting($key, $value)
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
    public function getSetting($key, $returnModel = false)
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

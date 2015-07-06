<?php
/**
 * Created by PhpStorm.
 * User: amblin
 * Date: 05/07/15
 * Time: 15:21
 */

namespace phparsenal\fastforward\Command;


use phparsenal\fastforward\Model\Setting;

/**
 * Save and list settings
 *
 * TODO Allow importing of many settings at once
 * TODO Use some kind of namespacing? e.g. ff.* for global, add.* for certain commands
 * TODO Allow the user to include these as variables in commands, e.g. $user.home will be replaced with its value
 * during run time
 */
class Set extends AbstractCommand implements CommandInterface
{

    protected $name = 'set';

    /**
     * @param array $argv
     */
    public function run($argv)
    {
        $this->prepareArguments();
        try {
            $args = $this->cli->arguments;
            $args->parse();
        } catch (\Exception $e) {
            $this->cli->arguments->usage($this->cli, $argv);
            return;
        }
        $key = $args->get('key');
        $value = $args->get('value');
        if ($key !== null && $value !== null) {
            $this->client->set($key, $value);
            return;
        }
        if ($args->defined('list')) {
            $this->listAll();
            return;
        }
        $this->import($argv);
    }

    private function prepareArguments()
    {
        $this->cli->arguments->add(
            array(
                'list' => array(
                    'prefix' => 'l',
                    'longPrefix' => 'list',
                    'description' => "Show a list of all current settings. Save to file: ff set -l > file.txt",
                    'noValue' => true
                ),
                'set' => array(
                    'description' => 'Command to set a variable',
                    'required' => true
                ),
                'key' => array(
                    'description' => 'Name or key of the setting',
                ),
                'value' => array(
                    'description' => 'Value to be set',
                ),
                'file' => array(
                    'prefix' => 'i',
                    'longPrefix' => 'import',
                    'description' => 'Import from the specified file',
                )
            )
        );
    }

    public function listAll()
    {
        $this->cli->forceAnsiOff();
        $settings = Setting::select()->orderAsc('key')->all();
        foreach ($settings as $setting) {
            $this->cli->out($setting->key . ' ' . $setting->value);
        }
    }

    /**
     * @param array $argv
     * @throws \Exception
     */
    private function import($argv)
    {
        $args = $this->cli->arguments;
        $lines = array();
        if ($args->defined('file')) {
            $lines = $this->getLinesFile($args);
        } else {
            $this->cli->arguments->usage($this->cli, $argv);
            $lines = $this->getLinesStdin();
        }
        $this->addLines($lines);
    }

    /**
     * @return array
     */
    private function getLinesStdin()
    {
        $this->cli->info('Reading settings from stdin..')->br();
        $h = fopen('php://stdin', 'r');
        $lines = array();
        while (!feof($h)) {
            $lines[] = fgets($h);
        }
        fclose($h);
        return $lines;
    }

    /**
     * @param $args
     * @return array
     */
    private function getLinesFile($args)
    {
        $this->cli->out('Reading settings from file: ' . $args->get('file'));
        $lines = file($args->get('file'));
        return $lines;
    }

    /**
     * @param $lines
     * @throws \Exception
     */
    private function addLines($lines)
    {
        foreach ($lines as $line) {
            if (preg_match('/^([^ ]+) (.*)/', $line, $matches)) {
                $this->client->set($matches[1], $matches[2]);
            } elseif (trim($line) !== '') {
                $this->cli->out('Line ignored: ' . $line);
            }
        }
    }
}
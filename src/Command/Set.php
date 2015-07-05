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
            $key = $args->get('key');
            $value = $args->get('value');

            if ($key !== null && $value !== null) {
                $this->set($key, $value);
                return;
            }
            if ($args->defined('list')) {
                $this->listAll();
                return;
            }
            throw new \Exception();
        } catch (\Exception $e) {
            $this->cli->arguments->usage($this->cli, $argv);
        }
    }

    private function prepareArguments()
    {
        $this->cli->arguments->add(
            array(
                'list' => array(
                    'prefix' => 'l',
                    'longPrefix' => 'list',
                    'description' => 'Show a list of all current settings',
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
                )
            )
        );
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        $oldValue = $this->client->get($key);
        if ($oldValue === null) {
            $this->cli
                ->out("Inserting new setting:")
                ->out("$key = $value");
        } elseif ($oldValue !== $value) {
            $this->cli
                ->out("Changing setting:")
                ->out("$key = $oldValue --> <bold>$value</bold>");
        } else {
            $this->cli
                ->out("Setting already up-to-date:")
                ->out("$key = $value");
        }
        $this->client->set($key, $value);
    }

    public function listAll()
    {
        $settings = Setting::select()->orderAsc('key')->all();
        foreach ($settings as $setting) {
            $this->cli->out('"' . $setting->key . '" "' . $setting->value . '"');
        }
    }
}
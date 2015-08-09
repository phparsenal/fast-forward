<?php

namespace phparsenal\fastforward;

use phparsenal\fastforward\Model\Bookmark;
use phparsenal\fastforward\Model\Setting;
use Respect\Validation\Exceptions\NestedValidationExceptionInterface;
use Respect\Validation\Validator as v;

class Settings
{
    /** @var Client */
    private $client;

    private $supportedSettings = array();

    const LIMIT = 'ff.limit';
    const SORT = 'ff.sort';
    const INTERACTIVE = 'ff.interactive';
    const COLOR = 'ff.color';
    const DATABASE_VERSION = 'ff.db.version';

    public function __construct(Client $client)
    {
        $this->client = $client;
        $sortColumns = array_keys(Bookmark::select()->toAssoc());
        $this->supportedSettings = array(
            'ff.limit' => array(
                'desc' => 'Limit amount of results (> 0 or 0 for no limit)',
                'validation' => array(v::int()->notEmpty()->min(0, true)),
                'default' => 0,
            ),
            'ff.sort' => array(
                'desc' => 'Sort order of results (' . implode($sortColumns, ', ') . ')',
                'validation' => array(v::in($sortColumns)->notEmpty()),
                'default' => 'hit_count',
            ),
            'ff.interactive' => array(
                'desc' => 'Ask for missing input interactively (0 never, 1 always)',
                'validation' => array(v::in(array('0', '1'))->notEmpty()),
                'default' => '1',
            ),
            'ff.color' => array(
                'desc' => 'Enable color output on supported systems (0/1)',
                'validation' => array(v::in(array('0', '1'))->notEmpty()),
                'default' => 1,
            ),
        );
    }

    /**
     * Saves a setting as a key/value pair
     *
     * Settings specific to fast-forward start with a `ff.` prefix.
     *
     * You can use the constants of this class to avoid looking up the key names.
     *
     * @param string $key   Unique key name.<br>
     *                      Must contain only letters (a-z, A-Z), digits (0-9) and "."
     * @param string $value
     *
     * @throws \Exception
     */
    public function set($key, $value)
    {
        $cli = $this->client->getCLI();
        try {
            v::string()
                ->alnum('.')
                ->noWhitespace()
                ->notEmpty()
                ->assert($key);
        } catch (NestedValidationExceptionInterface $e) {
            $cli->error($e->getFullMessage());
            return;
        }
        $setting = $this->get($key, true);
        if ($setting === null) {
            $setting = new Setting();
            $setting->key = $key;
        }
        $oldValue = $setting->value;
        $setting->value = $value;
        if (!$this->validate($setting)) {
            return;
        }

        if ($oldValue === null) {
            $cli->out('Inserting new setting:')
                ->out("$key = $value");
        } elseif ($oldValue !== $value) {
            $cli->out('Changing setting:')
                ->out("$key = {$oldValue} --> <bold>$value</bold>");
        } else {
            $cli->out('Setting already up-to-date:')
                ->out("$key = $value");
        }
        $setting->save();
    }

    /**
     * Return the string or Model value for $key
     *
     * @param string $key
     * @param bool   $returnModel Returns a model instance when true
     *
     * @return null|string|Setting
     */
    public function get($key, $returnModel = false)
    {
        $setting = Setting::select()
            ->eq('key', $key)
            ->one();
        if ($setting === null) {
            if (isset($this->supportedSettings[$key]['default'])) {
                $setting = new Setting();
                $setting->key = $key;
                $setting->value = $this->supportedSettings[$key]['default'];
            }
        }
        if ($returnModel || $setting === null) {
            return $setting;
        }
        return $setting->value;
    }

    /**
     * @param Setting $setting
     *
     * @return bool
     */
    public function validate(Setting $setting)
    {
        if (!isset($this->supportedSettings[$setting->key])) {
            return true;
        }
        $info = $this->supportedSettings[$setting->key];
        if (!isset($info['validation'])) {
            return true;
        }
        /** @var Validator $validator */
        foreach ($info['validation'] as $validator) {
            try {
                $validator->assert($setting->value);
            } catch (NestedValidationExceptionInterface $exception) {
                $this->client->getCLI()->error($exception->getFullMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * Shows support info for all or specified setting(s)
     *
     * @param null|string $key Key/name of a specific setting. Leave empty for
     *                         all settings.
     */
    public function showSupportedSettings($key = null)
    {
        $settings = array();
        if ($key !== null && isset($this->supportedSettings[$key])) {
            $settings[$key] = $this->supportedSettings[$key];
        } else {
            $settings = $this->supportedSettings;
        }

        $currentSettings = Setting::select()
            ->in('key', array_keys($settings))
            ->all();

        $cli = $this->client->getCLI();
        $cli->br();
        $cli->info('Supported settings [default]:');

        foreach ($settings as $key => $info) {
            $cli->inline($key);
            if (isset($currentSettings[$key])) {
                $cli->inline(' = <bold>' . $currentSettings[$key]->value . '</bold>');
            }
            if (isset($info['default'])) {
                $cli->inline(' [' . $info['default'] . ']');
            }
            $cli->br()->tab()->out($info['desc']);
        }
        $cli->br();
    }
}

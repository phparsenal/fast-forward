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
                'validation' => array(v::int()->min(0, true)),
                'default' => '0',
            ),
            'ff.sort' => array(
                'desc' => 'Sort order of results (' . implode($sortColumns, ', ') . ')',
                'validation' => array(v::in($sortColumns)->notEmpty()),
                'default' => 'hit_count',
            ),
            'ff.interactive' => array(
                'desc' => 'Ask for missing input interactively (0 never, 1 always)',
                'validation' => array(v::in(array('0', '1'))),
                'default' => '1',
            ),
            'ff.color' => array(
                'desc' => 'Enable color output on supported systems (0/1)',
                'validation' => array(v::in(array('0', '1'))),
                'default' => '1',
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
        $out = $this->client->getOutput();
        try {
            v::string()
                ->alnum('.')
                ->noWhitespace()
                ->notEmpty()
                ->assert($key);
        } catch (NestedValidationExceptionInterface $e) {
            $out->error($e->getFullMessage());
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
            $out->writeln("Inserting new setting:\n$key = <options=bold>$value</>");
        } elseif ($oldValue !== $value) {
            $out->writeln("Changing setting:\n$key = {$oldValue} --> <options=bold>$value</>");
        } else {
            $out->writeln("Setting already up-to-date:\n$key = $value");
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
                $this->client->getOutput()->error($exception->getFullMessage());
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

        $out = $this->client->getOutput();
        $out->section('Supported settings [default]');

        foreach ($settings as $key => $info) {
            $out->write($key);
            if (isset($currentSettings[$key])) {
                $out->write(' = ' . $currentSettings[$key]->value);
            }
            if (isset($info['default'])) {
                $out->write(' [' . $info['default'] . ']');
            }
            $out->newLine();
            $out->writeln($info['desc']);
            $out->newLine();
        }
    }

    /**
     * Replace setting identifiers with their values.
     *
     * Surround the key/name of a setting with an @ to replace it with its
     * current or default value.
     *
     * e.g. "weather @location@" turns into "weather tokio"
     *
     * @param string $template
     *
     * @return string
     */
    public function parseIdentifiers($template)
    {
        $out = $template;
        $token = '@';
        $pattern = "/$token([a-zA-Z0-9.]+)$token/";
        if (preg_match_all($pattern, $template, $matches)) {
            $candidateKeys = $matches[1];
            foreach ($candidateKeys as $key) {
                $value = $this->get($key);
                if ($value !== null) {
                    $out = str_replace($token . $key . $token, $value, $out);
                }
            }
        }
        return $out;
    }
}

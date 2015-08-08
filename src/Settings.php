<?php

namespace phparsenal\fastforward;

use phparsenal\fastforward\Model\Setting;

class Settings
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
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
        if (strpos($key, ' ') !== false) {
            throw new \Exception('Error while trying to save setting "' . $key . '": Key name must not contain spaces.');
        }
        $setting = $this->get($key, true);
        if ($setting === null) {
            $setting = new Setting();
            $setting->key = $key;
        }
        $cli = $this->client->getCLI();
        if ($setting->value === null) {
            $cli->out('Inserting new setting:')
                ->out("$key = $value");
        } elseif ($setting->value !== $value) {
            $cli->out('Changing setting:')
                ->out("$key = {$setting->value} --> <bold>$value</bold>");
        } else {
            $cli->out('Setting already up-to-date:')
                ->out("$key = $value");
        }

        $setting->value = $value;
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
        if ($returnModel || $setting === null) {
            return $setting;
        }
        return $setting->value;
    }
}

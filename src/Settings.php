<?php

namespace phparsenal\fastforward;

use phparsenal\fastforward\Model\Setting;
use Respect\Validation\Exceptions\NestedValidationExceptionInterface;
use Respect\Validation\Validator as v;

class Settings
{
    /** @var Client */
    private $client;

    private $knownSettings = array();

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->knownSettings['ff.maxrows'] = array(
            'desc' => 'Limit amount of results',
            'validation' => array(v::int()->min(0, true)),
        );
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
        $oldValue = $setting->value;
        $setting->value = $value;
        if (!$this->validate($setting)) {
            return;
        }
        $cli = $this->client->getCLI();
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
        if (!isset($this->knownSettings[$setting->key])) {
            return true;
        }
        $info = $this->knownSettings[$setting->key];
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
}

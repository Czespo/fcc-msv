<?php

declare(strict_types=1);

namespace App\Application\Settings;

class Settings implements SettingsInterface
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key = '')
    {
        return (empty($key)) ? $this->settings : $this->settings[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function require(string $key)
    {
        if (array_key_exists($key, $this->settings))
        {
            return $this->settings[$key];
        }

        throw new Exception("Required key `$key` does not exist!");
    }
}

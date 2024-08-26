<?php

declare(strict_types=1);

namespace App\Application\Settings;

interface SettingsInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key = '');

    /**
     * Throws an exception if a value for $key has not been set.
     * 
     * @param string $key
     * @return mixed
     */
    public function require(string $key);
}

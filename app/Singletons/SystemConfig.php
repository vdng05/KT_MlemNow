<?php

namespace App\Singletons;

use App\Models\Setting;

class SystemConfig
{
    private static $instance = null;
    private $configs = [];

    private function __construct()
    {
        // Query DB đúng 1 lần duy nhất khi class này được gọi lần đầu
        $settings = Setting::all();
        foreach ($settings as $setting) {
            $this->configs[$setting->key] = $setting->value;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key, $default = null)
    {
        return $this->configs[$key] ?? $default;
    }
}
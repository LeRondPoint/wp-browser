<?php
// for phar
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    $loader = require_once(__DIR__.'/vendor/autoload.php');
    $loader->add('Codeception', __DIR__ . '/src');
    $loader->register(true);
} elseif (file_exists(__DIR__.'/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
}

// function not autoloaded in PHP, thus its a good place for them
if (!function_exists ('codecept_debug'))
{
    function codecept_debug($data)
    {
        \Codeception\Util\Debug::debug($data);
    }
}

if (!function_exists ('codecept_root_dir'))
{
    function codecept_root_dir($appendPath = '')
    {
        return \Codeception\Configuration::projectDir() . $appendPath;
    }
}

if (!function_exists ('codecept_output_dir'))
{
    function codecept_output_dir($appendPath = '')
    {
        return \Codeception\Configuration::outputDir() . $appendPath;
    }
}

if (!function_exists ('codecept_log_dir'))
{
    function codecept_log_dir($appendPath = '')
    {
        return \Codeception\Configuration::outputDir() . $appendPath;
    }
}

if (!function_exists ('codecept_data_dir'))
{
    function codecept_data_dir($appendPath = '')
    {
        return \Codeception\Configuration::dataDir() . $appendPath;
    }
}

if (!function_exists ('codecept_relative_path'))
{
    function codecept_relative_path($path)
    {
        return substr($path, strlen(codecept_root_dir()));
    }
}

<?php
/**
 * This is the configuration file for the unit tests.
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 * For example to change MySQL username and password your `config.local.php` should
 * contain the following:
 *
 *     $config['databases']['mysql']['username'] = 'username';
 *     $config['databases']['mysql']['password'] = 'password';
 */

$config = [
    'databases' => [
        'mysql' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=yii2_rest_test',
            'username' => 'travis',
            'password' => '',
        ],
    ],
    'fixtures' => require(__DIR__ . '/fixtures.php'),
];

if (is_file(__DIR__ . '/config.local.php')) {
    require(__DIR__ . '/config.local.php');
}

return $config;
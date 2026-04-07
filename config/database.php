<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'pgsql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'petraakad' => [
            'driver' => 'pgsql',
            'url' => env('AKAD_DB_URL'),
            'host' => env('DB_HOST_SIA', '127.0.0.1'),
            'port' => env('DB_PORT_SIA', '5432'),
            'database' => env('DB_DATABASE_SIA', 'forge'),
            'username' => env('DB_USERNAME_SIA', 'forge'),
            'password' => env('DB_PASSWORD_SIA', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'disable',
        ],

        'petramutu' => [
            'driver' => 'pgsql',
            'url' => env('MUTU_DB_URL'),
            'host' => env('DB_HOST_MUTU', '127.0.0.1'),
            'port' => env('DB_PORT_MUTU', '5432'),
            'database' => env('DB_DATABASE_MUTU', 'forge'),
            'username' => env('DB_USERNAME_MUTU', 'forge'),
            'password' => env('DB_PASSWORD_MUTU', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'disable',
        ],

        'petrasip' => [
            'driver' => 'pgsql',
            'url' => env('SIP_DB_URL'),
            'host' => env('DB_HOST_SIP', '127.0.0.1'),
            'port' => env('DB_PORT_SIP', '5432'),
            'database' => env('DB_DATABASE_SIP', 'forge'),
            'username' => env('DB_USERNAME_SIP', 'forge'),
            'password' => env('DB_PASSWORD_SIP', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'disable',
        ],

        'neosimMhs' => [
            'driver' => 'pgsql',
            'url' => env('NEOSIM_DB_URL'),
            'write' => [],
            'read' => [
                'port' => env('NEOSIM_DB_PORT_READ', '5432'),
            ],
            'sticky' => true, // untuk menghindari replication lag setelah write
            'host' => env('NEOSIM_DB_HOST', '127.0.0.1'),
            'port' => env('NEOSIM_DB_PORT', '5432'),
            'database' => env('NEOSIM_DB_DATABASE', 'forge'),
            'username' => env('NEOSIM_DB_USERNAME', 'forge'),
            'password' => env('NEOSIM_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'akademik',
            'sslmode' => 'prefer',
        ],

        'neosimRef' => [
            'driver' => 'pgsql',
            'url' => env('NEOSIM_DB_URL'),
            'write' => [],
            'read' => [
                'port' => env('NEOSIM_DB_PORT_READ', '5432'),
            ],
            'sticky' => true, // untuk menghindari replication lag setelah write
            'host' => env('NEOSIM_DB_HOST', '127.0.0.1'),
            'port' => env('NEOSIM_DB_PORT', '5432'),
            'database' => env('NEOSIM_DB_DATABASE', 'forge'),
            'username' => env('NEOSIM_DB_USERNAME', 'forge'),
            'password' => env('NEOSIM_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'ref',
            'sslmode' => 'prefer',
        ],

        'neosimPeg' => [
            'driver' => 'pgsql',
            'url' => env('NEOSIM_DB_URL'),
            'write' => [],
            'read' => [
                'port' => env('NEOSIM_DB_PORT_READ', '5432'),
            ],
            'sticky' => true, // untuk menghindari replication lag setelah write
            'host' => env('NEOSIM_DB_HOST', '127.0.0.1'),
            'port' => env('NEOSIM_DB_PORT', '5432'),
            'database' => env('NEOSIM_DB_DATABASE', 'forge'),
            'username' => env('NEOSIM_DB_USERNAME', 'forge'),
            'password' => env('NEOSIM_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'pegawai',
            'sslmode' => 'prefer',
        ],

        'neosimAkad' => [
            'driver' => 'pgsql',
            'url' => env('NEOSIM_DB_URL'),
            'write' => [],
            'read' => [
                'port' => env('NEOSIM_DB_PORT_READ', '5432'),
            ],
            'sticky' => true, // untuk menghindari replication lag setelah write
            'host' => env('NEOSIM_DB_HOST', '127.0.0.1'),
            'port' => env('NEOSIM_DB_PORT', '5432'),
            'database' => env('NEOSIM_DB_DATABASE', 'forge'),
            'username' => env('NEOSIM_DB_USERNAME', 'forge'),
            'password' => env('NEOSIM_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'akademik',
            'sslmode' => 'prefer',
        ],

        'gate' => [
            'driver' => 'pgsql',
            'url' => env('GATE_DB_URL'),
            'write' => [],
            'read' => [
                'port' => env('GATE_DB_PORT_READ', '5432'),
            ],
            'sticky' => true, // untuk menghindari replication lag setelah write
            'host' => env('DB_HOST_GATE', '127.0.0.1'),
            'port' => env('DB_PORT_GATE', '5432'),
            'database' => env('DB_DATABASE_GATE', 'forge'),
            'username' => env('DB_USERNAME_GATE', 'forge'),
            'password' => env('DB_PASSWORD_GATE', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'gate',
            'sslmode' => 'prefer',
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Pdo\Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'write' => [],
            'read' => [
                'port' => env('DB_PORT_READ', '5432'),
            ],
            'sticky' => true, // untuk menghindari replication lag setelah write
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('DB_SCHEMA', 'public'),
            'sslmode' => 'prefer',
        ],

        'bak' => [
            'driver' => 'sqlsrv',
            'url' => env('BAK_DB_URL'),
            'host' => env('BAK_DB_HOST', 'localhost'),
            'port' => env('BAK_DB_PORT', '1433'),
            'database' => env('BAK_DB_DATABASE', 'forge'),
            'username' => env('BAK_DB_USERNAME', 'forge'),
            'password' => env('BAK_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('BAK_DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('BAK_DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

        'cache_auth' => [
            'url' => env('REDIS_URL_AUTH', env('REDIS_URL')),
            'host' => env('REDIS_HOST_AUTH', env('REDIS_HOST', '127.0.0.1')),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

        'session' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => '0',
            'prefix' => 's:',
        ],

    ],

];

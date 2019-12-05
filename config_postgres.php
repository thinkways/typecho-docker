#!/usr/bin/env php
<?php
$confpath = '/var/www/config.php';
$config = array();
$config['DB_HOST'] = env('DB_HOST', 'postgres');
$config['DB_PORT'] = env('DB_PORT', 5432);
$config['DB_NAME'] = env('DB_NAME', 'typecho');
$config['DB_USER'] = env('DB_USER');
$config['DB_PASS'] = env('DB_PASS');
$config['SINGLE_USER_MODE'] = env('SINGLE_USER_MODE', false);
if(dbcheckconn($config)){
    $pdo = dbconnect($config);
    if(!dbcheckdb($config)){
        echo 'Database not found, creating.'. PHP_EOL ;
        $pdo = dbconnect($config);
        $pdo -> exec('CREATE DATABASE ' . ($config['DB_NAME']) . ' WITH OWNER ' . ($config['DB_USER']));
        unset($pdo);
        $pdo = dbexist($config);
        $pdo->exec("CREATE EXTENSION IF NOT EXISTS pg_trgm");
        unset($pdo);
}

function env($name, $default = null)
{
    $v = getenv($name) ?: $default;
    
    if ($v === null) {
        error('The env ' . $name . ' does not exist'). PHP_EOL ;
    }
    
    return $v;
}

function error($text)
{
    echo 'Error: ' . $text . PHP_EOL;
    exit(1);
}

function dbconnect($config)
{
    $map = array('host' => 'HOST', 'port' => 'PORT');
    $dsn = 'pgsql:';
    foreach ($map as $d => $h) {
        if (isset($config['DB_' . $h])) {
            $dsn .= $d . '=' . $config['DB_' . $h] . ';';
        }
    }
    $pdo = new \PDO($dsn, $config['DB_USER'], $config['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function dbcheckconn($config)
{
    try {
        dbconnect($config);
        return true;
    }
    catch (PDOException $e) {
        return false;
    }
}

function dbexist($config)
{
    $map = array('host' => 'HOST', 'port' => 'PORT' , 'dbname' =>'NAME');
    $dsn = 'pgsql:';
    foreach ($map as $d => $h) {
        if (isset($config['DB_' . $h])) {
            $dsn .= $d . '=' . $config['DB_' . $h] . ';';
        }
    }
    $pdo = new \PDO($dsn, $config['DB_USER'], $config['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
function dbcheckdb($config)
{
    try {
        dbexist($config);
        return true;
    }
    catch (PDOException $e) {
        echo $e;
        return false;
    }
}

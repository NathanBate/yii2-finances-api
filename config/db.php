<?php

$db_driver = getenv('DB_DRIVER');
$db_server = getenv('DB_SERVER');
$db_user = getenv('DB_USER');
$db_password = getenv('DB_PASSWORD');
$db_database = getenv('DB_DATABASE');
$db_table_prefix = getenv('DB_TABLE_PREFIX');
$db_port = getenv('DB_PORT');

if (($db_port === null) || ($db_port == '')) {
    $db_port = 3306;
}

return [
    'class' => 'yii\db\Connection',
    'dsn' => "$db_driver:host=$db_server;dbname=$db_database;",
    'username' => "$db_user",
    'password' => "$db_password",
    'tablePrefix' => "$db_table_prefix",
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];

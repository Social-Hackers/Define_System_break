<?php

namespace DefineSystem;


function sessionSettings($connection = null) {

    Session::setSessionStyle();
    if (! empty($connection) && $connection instanceof Db) {
        createDbSessionTable($connection);
        Session::setDbSessionConnection($connection);
    }
    if (SESSIOIN_START) {
        Session::activate();
    }
}

function createDbSessionTable($connection) {

    $table_name = DB_SESSION_TABLE_NAME;
    $statement = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
        `id` VARCHAR(250) NOT NULL,
        `key` VARCHAR(250) NOT NULL,
        `value` VARCHAR(250) NOT  NULL,
        `expired` DATETIME NOT NULL,
        PRIMARY KEY (`id`, `key`)
    )";
    $connection->exec($statement);
}

function insertDbSession($connection, $session_id, $key, $value) {

    $connection->insert([
        'id', 'key', 'value', 'expired'
    ], DB_SESSION_TABLE_NAME, true);
    $connection->values([
        [$session_id, $key, $value, date('Y-m-d H:i:s', time() + SESSION_EXPIRED)]
    ]);
    $connection->result();
}

function getDbSession($connection, $session_id, $key) {

    $connection->setBindValues(false);
    $connection->select([
        'value'
    ], DB_SESSION_TABLE_NAME);
    $connection->where([
        ['id' => $session_id],
        ['key' => $key],
        ['expired' => 'NOW()', '>', 'Dummy', false]
    ]);
    $result = $connection->result();
    return $result ? $result[0]->value : false;
}

function deleteDbSession($connection, $session_id, $key) {

    $connection->delete(DB_SESSION_TABLE_NAME);
    $connection->where([
        ['id' => $session_id],
        ['key' => $key]
    ]);
    $connection->result();
}


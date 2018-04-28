<?php

namespace lib;

/**
 * Class Connection
 *  Class for work with database
 */
class Connection
{

    public $dbName, $dbHost, $dbUser, $dbPassw, $error, $connection;

    function __construct()
    {
        $this->error = '';
        include(__DIR__ . '/config.php');
        if (empty($config['db']) || empty($config['db']['host']) || empty($config['db']['name']) || empty($config['db']['user'])) {
            $this->error = 'Пустое подключение к базе в конфиге.';
        }

        $this->dbHost = $config['db']['host'];
        $this->dbName = $config['db']['name'];
        $this->dbUser = $config['db']['user'];
        $this->dbPassw = $config['db']['passw'];
    }

    /**
     * Connection with database in PDO
     *
     * @return bool
     */
    public function connect()
    {
        if (!empty($this->error)) {
            return false;
        }

        try {
            $this->connection = new \PDO("mysql:dbname=$this->dbName;host=$this->dbHost", $this->dbUser, $this->dbPassw);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->connection->exec("set names utf8");
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Sql query in database
     *
     * @param $sql
     * @param array $params
     * @param string $typeOutputMassive
     * @return array|bool
     */
    public function getSqlResult($sql, $params = [], $typeResult = '')
    {
        if (empty($sql)) {
            $this->error = 'Пустой запрос.';
            return false;
        }

        $statement = $this->connection->prepare($sql);

        if (empty($params)) {
            $statement->execute();
        } else {
            $statement->execute($params);
        }

        if ($typeResult == 'assoc') {
            $statement->setFetchMode(\PDO::FETCH_ASSOC);
        } elseif ($typeResult == 'num') {
            $statement->setFetchMode(\PDO::FETCH_NUM);
        } else {
            return true;
        }

        $result = [];
        while ($res = $statement->fetch()) {
            $result[] = $res;
        }

        return $result;
    }


}
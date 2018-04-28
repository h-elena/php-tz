<?php

namespace lib;

use \lib\Connection;

class Users
{

    const url = 'https://api.github.com/users';

    public $table;
    public $error;
    public $db;

    function __construct()
    {
        spl_autoload_extensions('.php');
        spl_autoload_register(function ($class) {
            include_once __DIR__ . '/../' . $class . '.php';
        });

        $this->table = 'user';
    }

    /**
     * Connection on base
     *
     * @return bool
     */
    protected function connect()
    {
        $this->db = new Connection();
        if (!$this->db->connect()) {
            $this->error = $this->db->error;
            return false;
        }

        return true;
    }

    /**
     * Get information from url
     *
     * @return mixed
     */
    protected function getFile()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $html = curl_exec($ch);
        if (!empty($html)) {
            return $html;
        } else {
            $this->error = 'Ошибка curl: ' . curl_error($ch);
        }
    }

    /**
     * Add user on base
     *
     * @param $fields
     */
    protected function addUser($fields)
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE `github_id`=:github_id";
        $res = $this->db->getSqlResult($sql, ['github_id' => (int)$fields->id], 'assoc');
        if (!empty($res) && is_array($res)) {
            if (current($res)['github_login'] != $fields->login) {
                $params = [
                    'github_id' => (int)$fields->id,
                    'github_login' => filter_var(
                        $fields->login,
                        FILTER_VALIDATE_REGEXP,
                        array('options' => array('regexp' => '/[a-z0-9\-\_]/iu'))
                    )
                ];
                $sql = "UPDATE `user` SET `github_login`=:github_login WHERE `github_id`=:github_id";
                $this->db->getSqlResult($sql, $params);
            }
        } else {
            $params = [
                'github_id' => (int)$fields->id,
                'github_login' => filter_var(
                    $fields->login,
                    FILTER_VALIDATE_REGEXP,
                    array('options' => array('regexp' => '/[a-z0-9\-\_]/iu'))
                )
            ];
            $sql = "INSERT INTO `user`(`github_id`, `github_login`) VALUES (:github_id, :github_login)";
            $this->db->getSqlResult($sql, $params);
        }
    }

    /**
     * Run application
     *
     * @return bool
     */
    public function addUsers()
    {
        if (!$html = $this->getFile()) {
            echo $this->error;
            return false;
        }

        $jsonArray = json_decode($html);

        if (is_array($jsonArray)) {
            if (!$this->connect()) {
                echo $this->error;
                return false;
            }

            foreach ($jsonArray as $item) {
                if ((int)$item->id > 0) {
                    $this->addUser($item);
                }
            }
        }

        return true;
    }
}
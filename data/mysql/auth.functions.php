<?php

class AuthFunctions extends \Data\DataHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function existsRestorePasswordCodeByUserID($userID)
    {
        $query = "select * from `restore_pwd_codes` where `user_id`=?;";
        $params = array('s', $userID);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function insertRestorePasswordCode($userID, $code)
    {
        $query = "insert into `restore_pwd_codes` (`id`, `user_id`, `code`, `expires_at`) values (UUID(),?,?,CURRENT_TIMESTAMP() + INTERVAL 10 minute);";
        $params = array(
            'ss', $userID, $code
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function updateRestorePasswordCodeByUserID($userID, $code)
    {
        $query = "update `restore_pwd_codes` set `code`=?, `created_at`=CURRENT_TIMESTAMP(), `expires_at`=CURRENT_TIMESTAMP() + INTERVAL 10 minute where `user_id`=?;";
        $params = array(
            'ss', $code, $userID
        );

        return $this->executeNonQuery($query, $params);
    }

    public function getRestorePasswordCodeByCode($code, $email)
    {
        $query = "select RPC.* from `restore_pwd_codes` RPC inner join `users` U on RPC.`user_id`=U.`id` where RPC.`code` = ? and U.`email`=? and U.`visible`=1;";
        $params = array('ss', $code, $email);
        return $this->executeResultQuery($query, $params);
    }

    public function deleteRestorePasswordCodeByUserID($userID)
    {
        $query = "delete from `restore_pwd_codes` where `user_id`=?";
        $params = array('s', $userID);

        return $this->executeNonQuery($query, $params);
    }
}
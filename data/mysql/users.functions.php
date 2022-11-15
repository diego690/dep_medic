<?php

class UsersFunctions extends \Data\DataHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getUserByID($id)
    {
        $query = "select U.`id` 'user_id', U.`email` 'username', U.`role`, U.`visible`, U.`avatar`, P.* from `users` U inner join `persons` P on U.`person_id`=P.`id` where U.`id` = ? limit 1;";
        $user = $this->executeResultQuery($query, ['s', $id]);
        if (!$user) {
            return false;
        }

        return $user->fetch_object();
    }

    public function getAreaByUserID($id)
    {
        $query = "select UA.*, A.`name`, A.`campus` from `user_areas` UA inner join `areas` A on UA.`area_id`=A.`id` where UA.`user_id` = ? limit 1;";
        $user = $this->executeResultQuery($query, ['s', $id]);
        if ($user->num_rows > 0) {
            return $user->fetch_object();
        } else {
            return null;
        }
    }

    public function getUserByUsername($username)
    {
        $query = "select U.`id` 'user_id', U.`email` 'username', U.`role`, U.`visible`, U.`avatar`, P.* from `users` U inner join `persons` P on U.`person_id`=P.`id` where U.`email` = ? and U.`visible` = 1 limit 1;";
        $user = $this->executeResultQuery($query, ['s', $username]);
        if (!$user) {
            return false;
        }

        return $user->fetch_object();
    }

    public function getUserByCredentials($username, $password)
    {
        $query = "select U.`id` 'user_id', U.`email` 'username', U.`password`, U.`role`, U.`visible`, U.`avatar`, P.* from `users` U inner join `persons` P on U.`person_id`=P.`id` where U.`email` = ? and U.`visible` = 1;";
        $user = $this->executeResultQuery($query, ['s', $username]);
        if (!$user) {
            return false;
        }

        $obj = $user->fetch_object();
        if (!$obj) {
            return false;
        }

        if (!$this->password_check($password, $obj->password)) {
            return false;
        }

        return $obj;
    }

    public function existsUser($username, $userID = "")
    {
        $query = "select * from `users` where `email`=? and `id` <> ? and `visible` = 1;";
        $params = array('ss', $username, $userID);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function validateLogin($password)
    {
        $query = "select * from `users` where `id`=? and `visible` = 1;";
        $user = $this->executeResultQuery($query, ['s', $_SESSION["user_id"]]);
        if (!$user) {
            return false;
        }

        $obj = $user->fetch_object();
        if (!$obj) {
            return false;
        }

        if (!$this->password_check($password, $obj->pwd)) {
            return false;
        }

        return $obj;
    }

    public function getAllUsers()
    {
        $query = "select * from `users` where `visible` = 1 order by `id`";
        return $this->executeResultQuery($query, null);
    }

    public function password_encrypt($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function password_check($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function changePassword($userID, $newPwd)
    {
        $newPwd = $this->password_encrypt($newPwd);
        $query = "update `users` set `password`=? where `id`=?";
        $params = array('ss', $newPwd, $userID);

        return $this->executeNonQuery($query, $params);
    }

    public function updateProfileByUserID($userID, $address, $phone, $civil_state, $avatar, $birth_date, $email)
    {
        $query = "update `users` set `avatar`=?, `updated_at`=CURRENT_TIMESTAMP() where `id`=?;";
        $params = array('ss', $avatar, $userID);
        $this->executeNonQuery($query, $params);

        $query = "select P.* from `users` U inner join `persons` P on U.`person_id`=P.`id` where U.`id`=? limit 1;";
        $params = array('s', $userID);
        $personID = $this->executeResultQuery($query, $params)->fetch_object()->id;

        $query = "update `persons` set `address`=?, `phone`=?, `civil_state`=?, `birth_date`=?, `email`=?, `updated_at`=CURRENT_TIMESTAMP() where `id`=?;";
        $params = array('ssssss', $address, $phone, $civil_state, $birth_date, $email, $personID);
        $this->executeNonQuery($query, $params);

        return true;
    }

    public function existsUserPerson($username, $identification)
    {
        $query = "select * from `users` where `email` = ? and `visible` = 1;";
        $params = array('s', $username);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            $query = "select * from `persons` where `identification` = ?;";
            $params = array('s', $identification);
            $result = $this->executeResultQuery($query, $params);
            if ($result->num_rows > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function existsPerson($identification)
    {
        $query = "select * from `persons` where `identification` = ?;";
        $params = array('s', $identification);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function createPerson($id, $name, $last_name, $identification, $email, $phone, $birth_date, $civil_state, $address, $sex)
    {
        $query = "insert into `persons` (`id`, `name`, `last_name`, `identification`, `email`, `phone`, `birth_date`, `civil_state`, `address`, `sex`) values (?,?,?,?,?,?,?,?,?,?);";
        $params = array(
            'ssssssssss', $id, $name, $last_name, $identification, $email, $phone, $birth_date, $civil_state, $address, $sex
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function createUser($id, $personID, $username, $role, $pwd)
    {
        $pwd = $this->password_encrypt($pwd);
        $query = "insert into `users` (`id`, `person_id`, `email`, `role`, `password`) values (?,?,?,?,?);";
        $params = array(
            'sssss', $id, $personID, $username, $role, $pwd
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function deletePerson($id)
    {
        $query = "delete from `persons` where `id`=?";
        $params = array('s', $id);

        return $this->executeNonQuery($query, $params);
    }

    public function deleteUser($id)
    {
        $query = "delete from `users` where `id`=?";
        $params = array('s', $id);

        return $this->executeNonQuery($query, $params);
    }

    public function getDoctors($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit ".$limit.",".$offset;
        $query = "select U.`id`, U.`email`, U.`active`, P.`name`, P.`last_name`, A.`name` 'area', A.`campus` from `users` U inner join `persons` P on U.`person_id`=P.`id` inner join `user_areas` UA on UA.`user_id`=U.`id` inner join `areas` A on UA.`area_id`=A.`id` where U.`visible` = 1 and U.`role` = 'DR' ".$searchValue." ".$orderBy." ".$limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function activateUserByID($userID)
    {
        $query = "update `users` set `active`=1 where `id`=?";
        $params = array('s', $userID);

        return $this->executeNonQuery($query, $params);
    }

    public function deactivateUserByID($userID)
    {
        $query = "update `users` set `active`=0 where `id`=?";
        $params = array('s', $userID);

        return $this->executeNonQuery($query, $params);
    }

    public function getEmployees($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit ".$limit.",".$offset;
        $query = "select U.`id`, U.`email`, U.`active`, P.`name`, P.`last_name`, O.`name` 'occupation', "
                . "P.`identification` from `users` U inner join `persons` P on U.`person_id`=P.`id` "
                . "inner join `user_occupation` UO on UO.`user_id`=U.`id` inner join `occupations` "
                . "O on UO.`occupation_id`=O.`id` where U.`visible` = 1 and U.`role` "
                . "= 'US' ".$searchValue." ".$orderBy." ".$limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function getStudents($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit ".$limit.",".$offset;
        $query = "select U.`id`, U.`email`, U.`active`, P.`name`, P.`last_name`, C.`name` 'career', UC.`semester` from `users` U inner join `persons` P on U.`person_id`=P.`id` inner join `user_career` UC on UC.`user_id`=U.`id` inner join `careers` C on UC.`career_id`=C.`id` where U.`visible` = 1 and U.`role` = 'US' ".$searchValue." ".$orderBy." ".$limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function isUserAnEmployee($userID)
    {
        $query = "select * from `user_occupation` where `user_id`=?;";
        $params = array('s', $userID);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }
}
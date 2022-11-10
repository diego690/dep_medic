<?php

class ReportFunctions extends \Data\DataHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getDoctors()
    {
        $query = "select * from `users` where `role` = 'DR' and `visible` = 1;";
        return $this->executeResultQuery($query, null);
    }

    public function getAreaByUserID($userID)
    {
        $query = "select A.`name`, A.`campus` from `users` U inner join `user_areas` UA on U.`id`=UA.`user_id` inner join `areas` A on UA.`area_id`=A.`id` where U.`id` = ? limit 1;";
        return $this->executeResultQuery($query, ['s', $userID])->fetch_object();
    }

    public function getCountStudents()
    {
        $query = "select count(*) 'count' from `user_career` UC inner join `users` U on UC.`user_id`=U.`id` where U.`active` = 1 and U.`visible` = 1;";
        $result = $this->executeResultQuery($query, null)->fetch_object();
        return (empty($result)) ? 0 : ((empty($result->count)) ? 0 : $result->count);
    }

    public function getCountEmployees()
    {
        $query = "select count(*) 'count' from `user_occupation` UO inner join `users` U on UO.`user_id`=U.`id` where U.`active` = 1 and U.`visible` = 1;";
        $result = $this->executeResultQuery($query, null)->fetch_object();
        return (empty($result)) ? 0 : ((empty($result->count)) ? 0 : $result->count);
    }

    public function getCountKins()
    {
        $query = "select count(*) 'count' from `user_kinship` UK inner join `users` U on UK.`employee_id`=U.`id` where U.`active` = 1 and U.`visible` = 1;";
        $result = $this->executeResultQuery($query, null)->fetch_object();
        return (empty($result)) ? 0 : ((empty($result->count)) ? 0 : $result->count);
    }
}
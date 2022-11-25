<?php

class AdminFunctions extends \Data\DataHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function createUserArea($userID, $areaID)
    {
        $query = "insert into `user_areas` (`user_id`, `area_id`) values (?,?);";
        $params = array(
            'ss', $userID, $areaID
        );

        return $this->executeInsertQuery($query, $params);
    }

    //Faculties

    public function existsFaculty($name, $facultyID = "")
    {
        $query = "select * from `faculties` where `name` = ? and `id` <> ? and `visible` = 1;";
        $params = array('ss', $name, $facultyID);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function createFaculty($name, $description)
    {
        $query = "insert into `faculties` (`name`, `description`) values (?,?);";
        $params = array(
            'ss', $name, $description
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function getFaculties($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit ".$limit.",".$offset;
        $query = "select F.*, count(C.`id`) 'careers_count' from `faculties` F left join `careers` C on F.`id`=C.`faculty_id` where F.`visible` = 1 ".$searchValue." group by F.`id` ".$orderBy." ".$limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function getFacultyByID($facultyID)
    {
        $query = "select * from `faculties` where `id` = ?";
        return $this->executeResultQuery($query, ['s', $facultyID]);
    }

    public function deleteFacultyByID($facultyID)
    {
        $query = "update `faculties` set `visible`=0 where `id`=?";
        $params = array('s', $facultyID);

        return $this->executeNonQuery($query, $params);
    }

    public function updateFaculty($facultyID, $name, $description)
    {
        $query = "update `faculties` set `name`=?, `description`=?, `updated_at`=CURRENT_TIMESTAMP() where `id` = ?;";
        $params = array(
            'sss', $name, $description, $facultyID
        );

        return $this->executeNonQuery($query, $params);
    }

    //Careers

    public function existsCareer($name, $careerID = "")
    {
        $query = "select * from `careers` where `name` = ? and `id` <> ? and `visible` = 1;";
        $params = array('ss', $name, $careerID);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function createCareer($facultyID, $name, $description)
    {
        $query = "insert into `careers` (`faculty_id`, `name`, `description`) values (?,?,?);";
        $params = array(
            'sss', $facultyID, $name, $description
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function getCareers($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit ".$limit.",".$offset;
        $query = "select C.*, F.`name` 'faculty' from `careers` C inner join `faculties` F on F.`id`=C.`faculty_id` where F.`visible` = 1 and C.`visible` = 1 ".$searchValue." ".$orderBy." ".$limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function getCareerByID($careerID)
    {
        $query = "select * from `careers` where `id` = ?";
        return $this->executeResultQuery($query, ['s', $careerID]);
    }

    public function deleteCareerByID($careerID)
    {
        $query = "update `careers` set `visible`=0 where `id`=?";
        $params = array('s', $careerID);

        return $this->executeNonQuery($query, $params);
    }

    public function updateCareer($careerID, $facultyID, $name, $description)
    {
        $query = "update `careers` set `faculty_id`=?, `name`=?, `description`=?, `updated_at`=CURRENT_TIMESTAMP() where `id` = ?;";
        $params = array(
            'ssss', $facultyID, $name, $description, $careerID
        );

        return $this->executeNonQuery($query, $params);
    }

    //Occupations

    public function existsOccupation($name, $occupationID = "")
    {
        $query = "select * from `occupations` where `name` = ? and `id` <> ? and `visible` = 1;";
        $params = array('ss', $name, $occupationID);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function createOccupation($name, $description)
    {
        $query = "insert into `occupations` (`name`, `description`) values (?,?);";
        $params = array(
            'ss', $name, $description
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function getOccupations($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit ".$limit.",".$offset;
        $query = "select * from `occupations` where `visible` = 1 ".$searchValue." ".$orderBy." ".$limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function getOccupationByID($occupationID)
    {
        $query = "select * from `occupations` where `id` = ?";
        return $this->executeResultQuery($query, ['s', $occupationID]);
    }

    public function deleteOccupationByID($occupationID)
    {
        $query = "update `occupations` set `visible`=0 where `id`=?";
        $params = array('s', $occupationID);

        return $this->executeNonQuery($query, $params);
    }

    public function updateOccupation($occupationID, $name, $description)
    {
        $query = "update `occupations` set `name`=?, `description`=?, `updated_at`=CURRENT_TIMESTAMP() where `id` = ?;";
        $params = array(
            'sss', $name, $description, $occupationID
        );

        return $this->executeNonQuery($query, $params);
    }
}
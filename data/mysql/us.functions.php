<?php

class UsFunctions extends \Data\DataHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function existsPerson($identification)
    {
        $query = "select * from `persons` where `identification` = ? limit 1;";
        $params = array('s', $identification);
        return $this->executeResultQuery($query, $params);
    }

    public function isFamiliarRequestLimited()
    {
        $query = "select * from `familiar_requests` where `user_id` = ?;";
        $params = array('s', $_SESSION["dep_user_id"]);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function isAllowedFamiliar($personID)
    {
        $query = "select * from `users` where `person_id` = ? limit 1;";
        $params = array('s', $personID);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            $result = $result->fetch_object();
            if ($result->role == "US") {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function insertFamiliarRequest($requestID, $userID, $type, $kin, $personID = null)
    {
        $query = "insert into `familiar_requests` (`id`,`user_id`, `type`, `kin`, `person_id`) values (?,?,?,?,?);";
        $params = array(
            'sssss', $requestID, $userID, $type, $kin, $personID
        );
        return $this->executeInsertQuery($query, $params);
    }

    public function insertFamiliarRequestDetail($requestID, $name, $lastname, $identification, $email, $phone, $birth_date, $civil_state, $address, $backup_doc, $sex)
    {
        $query = "insert into `familiar_request_details` (`request_id`, `name`, `lastname`, `identification`, `email`, `phone`, `birth_date`, `civil_state`, `address`, `backup_doc`, `sex`) values (?,?,?,?,?,?,?,?,?,?,?);";
        $params = array(
            'sssssssssss', $requestID, $name, $lastname, $identification, $email, $phone, $birth_date, $civil_state, $address, $backup_doc, $sex
        );
        return $this->executeInsertQuery($query, $params);
    }

    public function deleteFamiliarRequest($id)
    {
        $query = "delete from `familiar_requests` where `id`=?";
        $params = array('s', $id);

        return $this->executeNonQuery($query, $params);
    }

    public function existsFamiliarByUserID($userID, $personID)
    {
        $query = "select * from `user_kinship` where `employee_id` = ? and `kinsman_id` = ? limit 1;";
        $params = array('ss', $userID, $personID);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getMyFamiliars($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select P.*, UK.`kin` from `user_kinship` UK inner join `persons` P on UK.`kinsman_id`=P.`id` where UK.`employee_id` = ? and P.`active` = 1 " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $_SESSION["dep_user_id"]]);
    }

    public function getMyFamiliarRequests($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "SELECT case when FR.`person_id` IS not NULL then P.`id` ELSE null END 'id', case when FR.`person_id` IS not NULL then P.`name` ELSE FRD.`name` END 'name', case when FR.`person_id` IS not NULL then P.`last_name` ELSE FRD.`lastname` END 'last_name', case when FR.`person_id` IS not NULL then P.`identification` ELSE FRD.`identification` END 'identification', case when FR.`person_id` IS not NULL then P.`email` ELSE FRD.`email` END 'email', case when FR.`person_id` IS not NULL then P.`phone` ELSE FRD.`phone` END 'phone', case when FR.`person_id` IS not NULL then P.`birth_date` ELSE FRD.`birth_date` END 'birth_date', case when FR.`person_id` IS not NULL then P.`civil_state` ELSE FRD.`civil_state` END 'civil_state', case when FR.`person_id` IS not NULL then P.`address` ELSE FRD.`address` END 'address', case when FR.`person_id` IS not NULL then null ELSE FRD.`backup_doc` END 'backup_doc', FR.`kin`, FR.`id` 'request_id', FR.`created_at` from `familiar_requests` FR left join `familiar_request_details` FRD on FR.`id`=FRD.`request_id` left JOIN `persons` P ON FR.`person_id`=P.`id` where FR.`user_id` = ? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $_SESSION["dep_user_id"]]);
    }

    public function getFamiliarRequestByID($requestID)
    {
        $query = "select * from `familiar_requests` where `id` = ?";
        return $this->executeResultQuery($query, ['s', $requestID]);
    }

    public function deleteFamiliarRequestByID($requestID)
    {
        $query = "delete from `familiar_requests` where `id`=?";
        $params = array('s', $requestID);
        $this->executeNonQuery($query, $params);

        $query = "delete from `familiar_request_details` where `request_id`=?";
        $params = array('s', $requestID);
        $this->executeNonQuery($query, $params);
        return true;
    }

    public function isAppointmentRequestLimited()
    {
        $query = "select count(*) 'count' from `user_kinship` where `employee_id` = ?";
        $params = array('s', $_SESSION["dep_user_id"]);
        $count = $this->executeResultQuery($query, $params)->fetch_object()->count;

        if ($_SESSION["dep_user_is_employee"] == true) {
            $query = "select T.* from `turn` T where T.`created_by` = ? and T.`status` = 'CR';";
        } else {
            $query = "select T.* from `turn` T inner join `users` U on T.`person_id`=U.`person_id` where U.`id` = ? and T.`status` = 'CR';";
        }
        $params = array('s', $_SESSION["dep_user_id"]);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > $count) {
            return true;
        } else {
            return false;
        }
    }

    public function getMyPersonID()
    {
        $query = "select * from `users` where `id` = ?";
        return $this->executeResultQuery($query, ['s', $_SESSION["dep_user_id"]])->fetch_object()->person_id;
    }

    public function existsActiveAppointmentByPersonID($personID)
    {
        $query = "select * from `turn` where `person_id` = ? and (`status` = 'CR' or `status` = 'CO');";
        $params = array('s', $personID);
        $result = $this->executeResultQuery($query, $params);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getScheduleByAreaID($areaID)
    {
        $query = "select * from `schedule_settings` where `area_id` = ? limit 1;";
        return $this->executeResultQuery($query, ['s', $areaID])->fetch_object();
    }

    public function insertRequestAppointment($areaID, $personID, $date, $init_time, $status, $type, $description)
    {
        $query = "select `duration` from `schedule_settings` where `area_id` = ? limit 1;";
        $duration = $this->executeResultQuery($query, ['s', $areaID])->fetch_object()->duration;
        $end_time = date("G:i:s", (strtotime($init_time) + $duration));

        $query = "insert into `turn` (`area_id`, `person_id`, `date`, `init_time`, `end_time`, `status`, `type`, `description`,`created_by`) values (?,?,?,?,?,?,?,?,?);";
        $params = array(
            'sssssssss', $areaID, $personID, $date, $init_time, $end_time, $status, $type, $description, $_SESSION["dep_user_id"]
        );
        return $this->executeInsertQuery($query, $params);
    }

    public function getConfirmedTurnsByAreaDateType($areaID, $date, $type)
    {
        $query = "select * from `turn` where `area_id` = ? and `date` = ? and `type` = ? and `status` = 'CO';";
        return $this->executeResultQuery($query, ['sss', $areaID,$date,$type]);
    }

    public function getMyAppointmentRequests($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $personID = $this->getMyPersonID();
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select T.`id`, A.`name` 'area', CONCAT(A.`name`,', Campus ',A.`campus`) 'full_area', P.`identification`, CONCAT(P.`name`,' ',P.`last_name`) 'fullname', T.`date`, T.`init_time` 'time', T.`status`, T.`type`, T.`description`, T.`created_at` from `turn` T inner join `areas` A on T.`area_id`=A.`id` inner join `persons` P on T.`person_id`=P.`id` where (T.`created_by`=? or T.`person_id`=?) and (T.`status`='CR' or T.`status`='CO') " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["ss", $_SESSION["dep_user_id"], $personID]);
    }
    public function getMyRecipeRequests($orderBy = "", $limit = null, $offset = null)
    {
        $personID = $this->getMyPersonID();
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "SELECT re.`id` as id, re.`created_at` as date, rd.`product` as product, rd.`quantity` as quantity, "
                . "rd.`indications` as indications FROM `recipes` re INNER JOIN `recipe_details` rd "
                . "on re.`id`=rd.`recipe_id` where re.`person_id`=?";
        return $this->executeResultQuery($query, ["s", $personID]);
    }

    public function getAppointmentRequestByID($requestID)
    {
        $query = "select * from `turn` where `id` = ?;";
        return $this->executeResultQuery($query, ['s', $requestID]);
    }

    public function deleteAppointmentRequestByID($requestID)
    {
        $query = "delete from `turn` where `id`=? and `status`='CR';";
        $params = array('s', $requestID);
        return $this->executeNonQuery($query, $params);
    }
}

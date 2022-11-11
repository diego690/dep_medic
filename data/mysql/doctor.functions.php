<?php

class DoctorFunctions extends \Data\DataHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function createUserKinship($employeeID, $kinsmanID, $kin)
    {
        $query = "insert into `user_kinship` (`employee_id`, `kinsman_id`, `kin`) values (?,?,?);";
        $params = array(
            'sss', $employeeID, $kinsmanID, $kin
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function createUserOccupation($userID, $occupationID)
    {
        $query = "insert into `user_occupation` (`user_id`, `occupation_id`) values (?,?);";
        $params = array(
            'ss', $userID, $occupationID
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function createUserCareer($userID, $careerID, $semester)
    {
        $query = "insert into `user_career` (`user_id`, `career_id`, `semester`) values (?,?,?);";
        $params = array(
            'ssi', $userID, $careerID, $semester
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function getFamiliarRequests($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "SELECT case when FR.`person_id` IS not NULL then P.`id` ELSE null END 'id', case when FR.`person_id` IS not NULL then P.`name` ELSE FRD.`name` END 'name', case when FR.`person_id` IS not NULL then P.`last_name` ELSE FRD.`lastname` END 'last_name', case when FR.`person_id` IS not NULL then P.`identification` ELSE FRD.`identification` END 'identification', case when FR.`person_id` IS not NULL then P.`email` ELSE FRD.`email` END 'email', case when FR.`person_id` IS not NULL then P.`phone` ELSE FRD.`phone` END 'phone', case when FR.`person_id` IS not NULL then P.`birth_date` ELSE FRD.`birth_date` END 'birth_date', case when FR.`person_id` IS not NULL then P.`civil_state` ELSE FRD.`civil_state` END 'civil_state', case when FR.`person_id` IS not NULL then P.`address` ELSE FRD.`address` END 'address', case when FR.`person_id` IS not NULL then null ELSE FRD.`backup_doc` END 'backup_doc', FR.`kin`, FR.`id` 'request_id', FR.`created_at`, CONCAT(P2.`identification`,' - ',P2.`name`,' ',P2.`last_name`) 'employee' from `familiar_requests` FR left join `familiar_request_details` FRD on FR.`id`=FRD.`request_id` left JOIN `persons` P ON FR.`person_id`=P.`id` inner join `users` U on FR.`user_id`=U.`id` inner join `persons` P2 on U.`person_id`=P2.`id` where 1=1 " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function getPatients($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select P.* from `persons` P LEFT JOIN `users` U ON P.`id`=U.`person_id` WHERE (U.`role`='US' OR U.`id` IS NULL) " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function deleteFamiliarRequestByID($requestID)
    {
        $query = "delete from `familiar_request_details` where `request_id`=?;";
        $this->executeNonQuery($query, ['s', $requestID]);

        $query = "delete from `familiar_requests` where `id`=?;";
        return $this->executeNonQuery($query, ['s', $requestID]);
    }

    public function getFamiliarRequestDetailByID($requestID)
    {
        $query = "select * from `familiar_request_details` where `request_id` = ?";
        return $this->executeResultQuery($query, ['s', $requestID]);
    }

    public function createDoc($personID, $name, $url)
    {
        $query = "insert into `docs` (`id`,`person_id`, `name`, `url_doc`) values (UUID(),?,?,?);";
        $params = array(
            'sss', $personID, $name, $url
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function deleteUserKinship($employeeID, $kinsmanID)
    {
        $query = "delete from `user_kinship` where `employee_id`=? and `kinsman_id`=?;";
        $this->executeNonQuery($query, ['ss', $employeeID, $kinsmanID]);
    }

    public function getMyArea()
    {
        $query = "select * from `user_areas` where `user_id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $_SESSION["dep_user_id"]]);
        if ($result->num_rows > 0) {
            return $result->fetch_object()->area_id;
        } else {
            return null;
        }
    }

    public function getConfirmedAppointmentsByAreaID($areaID)
    {
        $query = "select T.*, CONCAT(P.`name`,' ',P.`last_name`) 'patient' from `turn` T inner join `persons` P on T.`person_id`=P.`id` where T.`status` = 'CO' and T.`area_id`=? AND CONCAT(T.`date`,' ',T.`end_time`) > NOW();";
        return $this->executeResultQuery($query, ['s', $areaID]);
    }

    public function updateConfirmedAppointmentsToNoAttended()
    {
        $query = "update `turn` set `status`='NA' where CONCAT(`date`,' ',`end_time`) < NOW() and `status`='CO';";
        return $this->executeNonQuery($query, null);
    }

    public function updateConfirmedAppointmentsToAttended()
    {
        $query = "update `turn` set `status`='AT' where CONCAT(`date`,' ',`end_time`) < NOW() and `status`='CO' AND (`id` IN (SELECT `turn_id` FROM `nursing_data`) OR `id` IN (SELECT `turn_id` FROM `dental_consultation`) OR `id` IN (SELECT `turn_id` FROM `medical_consultation`));";
        return $this->executeNonQuery($query, null);
    }

    public function deleteExpiredCreatedAppointments()
    {
        $query = "delete from `turn` where CONCAT(`date`,' ',`init_time`) < NOW() and `status`='CR';";
        return $this->executeNonQuery($query, null);
    }

    public function getAppointmentByID($appointmentID)
    {
        $query = "select * from `turn` where `id`=? limit 1;";
        return $this->executeResultQuery($query, ['s', $appointmentID]);
    }

    public function cancelAppointmentByID($appointmentID)
    {
        $query = "update `turn` set `status`='CA', `checked_by`=? where `id`=?;";
        return $this->executeNonQuery($query, ['ss', $_SESSION["dep_user_id"], $appointmentID]);
    }

    public function getAppointmentRequests($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select T.`id`, A.`name` 'area', CONCAT(A.`name`,', Campus ',A.`campus`) 'full_area', P.`identification`, CONCAT(P.`name`,' ',P.`last_name`) 'fullname', T.`date`, T.`init_time` 'time', T.`status`, T.`type`, T.`description`, T.`created_at` from `turn` T inner join `areas` A on T.`area_id`=A.`id` inner join `persons` P on T.`person_id`=P.`id` where T.`status`='CR' " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function acceptAppointmentRequestByID($appointmentID)
    {
        $query = "update `turn` set `status`='CO', `checked_by`=? where `id`=? and `status`='CR';";
        return $this->executeNonQuery($query, ['ss', $_SESSION["dep_user_id"], $appointmentID]);
    }

    public function declineAppointmentRequestByDateTime($date, $time)
    {
        $query = "delete from `turn` where `date`=? and `init_time`=? and `status`='CR';";
        return $this->executeNonQuery($query, ['ss', $date, $time]);
    }

    public function declineAppointmentRequestByID($appointmentID)
    {
        $query = "delete from `turn` where `id`=? and `status`='CR';";
        return $this->executeNonQuery($query, ['s', $appointmentID]);
    }

    public function existsConfirmedAppointmentWithSameDateTime($date, $time)
    {
        $query = "select * from `turn` where `date`=? and `init_time`=? and `status`='CO';";
        $result = $this->executeResultQuery($query, ['ss', $date, $time]);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function insertAppointment($areaID, $personID, $date, $init_time, $status, $type, $description)
    {
        try {
            $query = "select `duration` from `schedule_settings` where `area_id` = ? limit 1;";
            $duration = $this->executeResultQuery($query, ['s', $areaID])->fetch_object()->duration;
            $end_time = date("G:i:s", (strtotime($init_time) + $duration));

            $query = "select `days`, `hours_p`, `hours_t` from `schedule_settings` where `area_id` = ? limit 1;";
            $hours = $this->executeResultQuery($query, ['s', $areaID])->fetch_object();
            $hours_p = json_decode($hours->hours_p)[0];
            $hours_t = json_decode($hours->hours_t)[0];
            $days = json_decode($hours->days);
            if (in_array(date("N", (strtotime($date))), $days)) {
                if ((strtotime($init_time) >= strtotime($hours_p->start) && strtotime($init_time) <= strtotime($hours_p->end)) || (strtotime($init_time) >= strtotime($hours_t->start) && strtotime($init_time) <= strtotime($hours_t->end))) {
                    if (!$this->existsConfirmedAppointmentWithSameDateTime($date, $init_time)) {
                        $query = "insert into `turn` (`id`, `area_id`, `person_id`, `date`, `init_time`, `end_time`, `status`, `type`, `description`,`created_by`) values (UUID(),?,?,?,?,?,?,?,?,?);";
                        $params = array(
                            'sssssssss', $areaID, $personID, $date, $init_time, $end_time, $status, $type, $description, $_SESSION["dep_user_id"]
                        );
                        return $this->executeInsertQuery($query, $params);
                    } else {
                        return 0;
                    }
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function getConfirmedAppointments($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $whereQ = "";
        $area = $this->getMyArea();
        if ($area) {
            if ($_SESSION["dep_user_area"] != 1) {
                $whereQ = " and T.`area_id` = '" . $area . "' ";
            } else {
                $whereQ = " and T.`type` = 'P' ";
            }
        }

        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select T.*, CONCAT(P.`name`,' ',P.`last_name`) 'patient', P.`identification` from `turn` T inner join `persons` P on T.`person_id`=P.`id` where T.`status` = 'CO' " . $whereQ . " AND CONCAT(T.`date`,' ',T.`end_time`) > NOW() " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function getSettingsByAreaID($area)
    {
        $query = "select * from `settings` where `area_id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $area]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return false;
        }
    }

    public function updateMeetLinkByAreaID($areaID, $meet_link)
    {
        $query = "update `settings` set `meet_link`=? where `area_id`=?;";
        return $this->executeNonQuery($query, ['ss', $meet_link, $areaID]);
    }

    public function getPatientsInvolvedInAppointmentsByAreaID($area)
    {
        $query = "select T.*, P.`email` 'patient_email', P.`name` 'patient_name', P.`last_name` 'patient_lastname' from `turn` T inner join `persons` P on T.`person_id`=P.`id` where T.`area_id`=? and T.`type`='T' and T.`status`='CO' and CONCAT(T.`date`,' ',T.`end_time`) > NOW();";
        return $this->executeResultQuery($query, ['s', $area]);
    }

    //ATTEND PATIENTS

    public function getPatientDataByID($patientID)
    {
        $query = "select P.*, U.`avatar`, U.`email` 'access_email', U.`id` 'user_id' from `persons` P left join `users` U on U.`person_id`=P.`id` where P.`id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $patientID]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }

    public function getPatientTypeByUserID($userID)
    {
        $query = "select UC.*, C.`name` from `user_career` UC inner join `careers` C on UC.`career_id`=C.`id` where UC.`user_id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $userID]);
        if ($result->num_rows > 0) {
            $result = $result->fetch_object();
            return "Estudiante - ".$result->name;
        }

        $query = "select UO.*, O.`name` from `user_occupation` UO inner join `occupations` O on UO.`occupation_id`=O.`id` where UO.`user_id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $userID]);
        if ($result->num_rows > 0) {
            $result = $result->fetch_object();
            return $result->name;
        } else {
            return "";
        }
    }

    public function getNursingData($personID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `nursing_data` WHERE `person_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $personID]);
    }

    public function getPatientDocs($personID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `docs` WHERE `person_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $personID]);
    }

    public function getMedicalHistory($personID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `medical_history` WHERE `person_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $personID]);
    }

    public function getMedicalConsultation($medicalHistoryID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `medical_consultation` WHERE `medicalhistory_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $medicalHistoryID]);
    }

    public function getMedicalEvolve($medicalHistoryID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `medical_evolve` WHERE `medicalhistory_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $medicalHistoryID]);
    }
    public function getMedicalExam($medicalHistoryID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `medical_exams` WHERE `medicalhistory_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $medicalHistoryID]);
    }
    public function getMedicalExam2($medicalHistoryID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `examen_patients` WHERE `medical_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $medicalHistoryID]);
    }

    public function getDentalHistory($personID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `dental_history` WHERE `person_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $personID]);
    }

    public function getDentalConsultation($dentalHistoryID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `dental_consultation` WHERE `dentalhistory_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $dentalHistoryID]);
    }

    public function getDentalEvolve($dentalHistoryID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `dental_evolve` WHERE `dentalhistory_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $dentalHistoryID]);
    }

    public function getRecipeData($personID, $searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit " . $limit . "," . $offset;
        $query = "select * from `recipes` WHERE `person_id`=? " . $searchValue . " " . $orderBy . " " . $limitClause;
        return $this->executeResultQuery($query, ["s", $personID]);
    }

    //NURSING

    public function insertNursingData($turnID, $personID, $weight, $pressure, $temperature, $heartFrequency, $oxygen, $height, $breathingFrequency, $imc)
    {
        $query = "insert into `nursing_data` (`id`, `turn_id`, `person_id`, `weight`, `pressure`,`temperature`,`heart_frequency`,`oxygen`,`height`,`breathing_frequency`,`imc`,`created_by`) values (UUID(),?,?,?,?,?,?,?,?,?,?,?);";
        $params = array(
            'ssdddddddds', $turnID, $personID, $weight, $pressure, $temperature, $heartFrequency, $oxygen, $height, $breathingFrequency, $imc, $_SESSION["dep_user_id"]
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function verifyTurnByID($turnID)
    {
        $query = "select * from `turn` where `id`=? and (`status`='CO' or `status`='AT') and CONCAT(`date`,' ',`end_time`) > NOW() limit 1;";
        $result = $this->executeResultQuery($query, ['s', $turnID]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }

    public function getNursingDataByID($id)
    {
        $query = "select * from `nursing_data` where `id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $id]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }

    public function getMedicalHistoryByID($id)
    {
        $query = "select * from `medical_history` where `id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $id]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }

    // MEDICAL HISTORY

    public function getMedicalHistoryByPatientID($patientID)
    {
        $query = "select * from `medical_history` where `person_id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $patientID]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }

    public function existsMedicalHistoryByPatientID($patientID)
    {
        $query = "select * from `medical_history` where `person_id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $patientID]);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function insertMedicalHistory($personID, $app, $apf, $ago, $allergies, $habits, $pressure, $heartFrequency, $weight, $height, $imc)
    {
        $query = "insert into `medical_history` (`id`, `person_id`, `app`, `apf`, `ago`,`allergies`,`habits`,`pressure`,`heart_frequency`,`weight`,`height`,`imc`,`updated_by`) values (UUID(),?,?,?,?,?,?,?,?,?,?,?,?);";
        $params = array(
            'ssssssddddds', $personID, $app, $apf, $ago, $allergies, $habits, $pressure, $heartFrequency, $weight, $height, $imc, $_SESSION["dep_user_id"]
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function updateMedicalHistory($personID, $app, $apf, $ago, $allergies, $habits, $pressure, $heartFrequency, $weight, $height, $imc)
    {
        $query = "update `medical_history` set `app`=?, `apf`=?, `ago`=?,`allergies`=?,`habits`=?,`pressure`=?,`heart_frequency`=?,`weight`=?,`height`=?,`imc`=?,`updated_by`=?,`updated_at`=CURRENT_TIMESTAMP() where `person_id`=?;";
        $params = array(
            'sssssdddddss', $app, $apf, $ago, $allergies, $habits, $pressure, $heartFrequency, $weight, $height, $imc, $_SESSION["dep_user_id"], $personID
        );

        return $this->executeNonQuery($query, $params);
    }

    // MEDICAL CONSULTATION

    public function insertMedicalConsultation($historyID, $turnID, $reason, $headNeck, $thorax, $abdomen, $extremities, $diagnostic, $treatment)
    {
        $query = "insert into `medical_consultation` (`id`, `medicalhistory_id`,`turn_id`, `reason`, `head_neck`, `thorax`,`abdomen`,`extremities`,`diagnostic`,`treatment`,`created_by`) values (UUID(),?,?,?,?,?,?,?,?,?,?);";
        $params = array(
            'ssssssssss', $historyID, $turnID, $reason, $headNeck, $thorax, $abdomen, $extremities, $diagnostic, $treatment, $_SESSION["dep_user_id"]
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function getMedicalConsultationByID($id)
    {
        $query = "select * from `medical_consultation` where `id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $id]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }

    // MEDICAL EVOLVE

    public function insertMedicalEvolve($historyID, $date, $evolve_notes, $prescription)
    {
        $query = "insert into `medical_evolve` (`id`, `medicalhistory_id`,`date`, `evolve_notes`, `prescription`) values (UUID(),?,?,?,?);";
        $params = array(
            'ssss', $historyID, $date, $evolve_notes, $prescription
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function getMedicalEvolveByID($id)
    {
        $query = "select * from `medical_evolve` where `id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $id]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }
    public function getMedicalExamByID($id)
    {
        $query = "select * from `examen_patients` where `id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $id]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }

    public function getMedicalEvolveByDate($medicalhistory_id, $date)
    {
        $query = "select * from `medical_evolve` where `medicalhistory_id`=? and `date`<=? order by `date` asc;";
        return $this->executeResultQuery($query, ['ss', $medicalhistory_id, $date]);
    }
    public function getMedicalExamByDate($medicalhistory_id, $date)
    {
        $query = "select me.`id` as id, me.`created_at` as fecha, te.`type_exam` as exam, ce.`category` as category from `examen_patients` me inner join `details_exams` de on me.id=de.id_exam inner join `types_exam` te on de.id_type_exam=te.id inner join `category_exam` ce on te.`id_category`=ce.`id` where me.`id`=? and me.`created_at`<=? order by me.`created_at` asc;";
        return $this->executeResultQuery($query, ['ss', $medicalhistory_id, $date]);
    }

    // RECIPE

    public function insertRecipe($id, $personID)
    {
        $query = "insert into `recipes` (`id`, `person_id`, `created_by`) values (?,?,?);";
        $params = array(
            'sss', $id, $personID, $_SESSION["dep_user_id"]
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function insertRecipeDetails($recipeID, $product, $quantity, $indications, $kit_quantity)
    {
        $query = "insert into `recipe_details` (`id`, `recipe_id`, `product`, `quantity`, `indications`, `kit_quantity`) values (UUID(),?,?,?,?,?);";
        $params = array(
            'ssisi', $recipeID, $product, $quantity, $indications, $kit_quantity
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function deleteRecipe($id)
    {
        $query = "delete from `recipe_details` where `recipe_id`=?;";
        $this->executeNonQuery($query, ["s", $id]);

        $query = "delete from `recipes` where `id`=?;";
        return $this->executeNonQuery($query, ["s", $id]);
    }

    public function getRecipeByID($id)
    {
        $query = "select * from `recipes` where `id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $id]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }

    public function getRecipeDetailsByID($id)
    {
        $query = "select * from `recipe_details` where `recipe_id`=?;";
        return $this->executeResultQuery($query, ['s', $id]);
    }

    // PRODUCTS

    public function existsProduct($name, $id = "")
    {
        $query = "select * from `products` where `name`=? and `id`<>? and `visible`=1;";
        $result = $this->executeResultQuery($query, ['ss', $name, $id]);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function createProduct($id, $name, $image, $units, $description)
    {
        $query = "insert into `products` (`id`, `name`, `image`, `units`, `description`) values (?,?,?,?,?);";
        $params = array(
            'sssis', $id, $name, $image, $units, $description
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function auditProduct($action, $product_id, $details)
    {
        $query = "insert into `log_products` (`user_id`, `action`, `product_id`, `details`) values (?,?,?,?);";
        $params = array(
            'ssss', $_SESSION["dep_user_id"], $action, $product_id, $details
        );

        return $this->executeInsertQuery($query, $params);
    }

    public function getProductByID($productID)
    {
        $query = "select * from `products` where `id` = ?";
        $result = $this->executeResultQuery($query, ['s', $productID]);
        if ($result->num_rows > 0) {
            return $result->fetch_object();
        } else {
            return null;
        }
    }

    public function updateProduct($productID, $name, $image, $description)
    {
        $query = "update `products` set `name`=?, `image`=?, `description`=?, `updated_at`=CURRENT_TIMESTAMP() where `id` = ?;";
        $params = array(
            'ssss', $name, $image, $description, $productID
        );

        return $this->executeNonQuery($query, $params);
    }

    public function getProducts($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit ".$limit.",".$offset;
        $query = "select * from `products` where `visible` = 1 ".$searchValue." ".$orderBy." ".$limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function deleteProductByID($productID)
    {
        $query = "update `products` set `visible`=0 where `id` = ?;";
        $params = array('s', $productID);
        return $this->executeNonQuery($query, $params);
    }

    public function increaseProductStockByID($productID, $units)
    {
        $query = "update `products` set `units`=? where `id` = ?;";
        $params = array('is', $units, $productID);

        return $this->executeNonQuery($query, $params);
    }

    public function decreaseProductStockByID($productID, $units)
    {
        $query = "update `products` set `units`=`units`-? where `id` = ?;";
        $params = array('is', $units, $productID);

        return $this->executeNonQuery($query, $params);
    }

    public function getAuditProducts($searchValue, $orderBy = "", $limit = null, $offset = null)
    {
        $limitClause = (is_null($limit) || is_null($offset)) ? "" : " limit ".$limit.",".$offset;
        $query = "select U.`email`, CONCAT(P.`name`, ' ', P.`last_name`) 'user', L.`action`, PR.`name` 'product', L.`datetime`, L.`details` from `log_products` L inner join `users` U on L.`user_id`=U.`id` inner join `persons` P on U.`person_id`=P.`id` inner join `products` PR on L.`product_id`=PR.`id` where 1 = 1 ".$searchValue." ".$orderBy." ".$limitClause;
        return $this->executeResultQuery($query, null);
    }

    public function getHematologias()
    {
        $query = "select * from `types_exam` where `id_category` = 1 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getInfectologias()
    {
        $query = "select * from `types_exam` where `id_category` = 2 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getInmunologias(){
        $query = "select * from `types_exam` where `id_category` = 3 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getBiologiaMolecular(){
        $query = "select * from `types_exam` where `id_category` = 4 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getNivelesFarmacosDrogas(){
        $query = "select * from `types_exam` where `id_category` = 5 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getHormonas(){
        $query = "select * from `types_exam` where `id_category` = 6 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getOrinas(){
        $query = "select * from `types_exam` where `id_category` = 7 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getHeces(){
        $query = "select * from `types_exam` where `id_category` = 8 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getBioquimicas(){
        $query = "select * from `types_exam` where `id_category` = 9 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getMarcadorTumor(){
        $query = "select * from `types_exam` where `id_category` = 10 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getEsputo(){
        $query = "select * from `types_exam` where `id_category` = 11 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getEnzimas(){
        $query = "select * from `types_exam` where `id_category` = 12 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getBacteorologias(){
        $query = "select * from `types_exam` where `id_category` = 13 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getElectrolitos(){
        $query = "select * from `types_exam` where `id_category` = 14 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getGasesS(){
        $query = "select * from `types_exam` where `id_category` = 15 ;";
        return $this->executeResultQuery($query, null);
    }
    public function getOtros(){
        $query = "select * from `types_exam` where `id_category` = 16 ;";
        return $this->executeResultQuery($query, null);
    }

    // MEDICAL EXAM

    public function insertMedicalExam($historyID, $date)
    {
        $query = "insert into `examen_patients`(`medical_id`,`created_at`) values (?,?);";
        $params = array(
            'ss', $historyID, $date
        );
        return $this->executeInsertQuery($query,$params);
    }
    public function insertDetailsExam($exam_type)
    {
        $query = "call sp_insert_details('".$exam_type."');";

        return $this->executeResultQuery($query,null);
    }
}

<?php

class PanelFunctions extends \Data\DataHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getCurrentTimestamp()
    {
        $query = "select CURRENT_TIMESTAMP() 'now';";
        return $this->executeResultQuery($query, null)->fetch_object()->now;
    }

    public function getAreas()
    {
        $query = "select * from `areas`;";
        return $this->executeResultQuery($query, null);
    }

    public function getMyArea()
    {
        $query = "select * from `user_areas` where `user_id`=? limit 1;";
        $result = $this->executeResultQuery($query, ['s', $_SESSION["dep_user_id"]]);
        if ($result->num_rows > 0) {
            $result = $result->fetch_object()->area_id;
            if (in_array($result, array("2584c89d-f1a4-416a-8041-42bfb1dc1616","5f583780-d850-4f33-87de-14c099e0dbec"))) {
                return 1;
            } else if (in_array($result, array("8838dd8e-99d2-4f87-8fbf-4d8e8952136b","32e42172-64a2-4d7e-8173-cac3080b9afa"))) {
                return 2;
            } else {
                return 3;
            }
        } else {
            return null;
        }
    }

    public function getFaculties()
    {
        $query = "select * from `faculties`;";
        return $this->executeResultQuery($query, null);
    }

    public function getCareersByFaculty($facultyID)
    {
        $query = "select * from `careers` where `faculty_id`=?;";
        return $this->executeResultQuery($query, ['s', $facultyID]);
    }

    public function getOccupations()
    {
        $query = "select * from `occupations`;";
        return $this->executeResultQuery($query, null);
    }
}
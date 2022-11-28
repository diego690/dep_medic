<?php
require_once(__DIR__ . "/system/loader.php");
require_once(__DIR__ . "/system/security/session.php");
require_once(__DIR__ . "/data/mysql/users.functions.php");
require_once(__DIR__ . "/data/mysql/admin.functions.php");
require_once(__DIR__ . "/data/mysql/us.functions.php");
require_once(__DIR__ . "/data/mysql/panel.functions.php");
require_once(__DIR__ . "/data/mysql/doctor.functions.php");
require_once(__DIR__ . "/system/helpers/mail_sender.php");

$usersFunctions = new UsersFunctions();
$adminFunctions = new AdminFunctions();
$usFunctions = new UsFunctions();
$panelFunctions = new PanelFunctions();
$doctorFunctions = new DoctorFunctions();

$result = (object)[
    "status" => 500,
    "error" => "Error: Parámetros faltantes o incorrectos",
    "data" => null
];

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "get_doctors":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (U.`email` like '%" . $searchValue . "%' or P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%' or A.`name` like '%" . $searchValue . "%' or A.`campus` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $usersFunctions->getDoctors("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $usersFunctions->getDoctors($searchQuery)->num_rows;

                // Fetch records
                $doctors = $usersFunctions->getDoctors($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . ", `active` desc " : " order by `active` desc "), $row, $rowperpage);
                $result_data = array();
                while ($r = $doctors->fetch_object()) {
                    $columnActions = "<center>";
                    if ($r->active == 0) {
                        $columnActions .= "
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Habilitar cuenta\" onclick=\"openModalActivate('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"user-check\"></i>
                            </a>
                        ";
                    } else {
                        $columnActions .= "
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Deshabilitar cuenta\" onclick=\"openModalDeactivate('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"user-minus\"></i>
                            </a>
                        ";
                    }
                    $columnActions .= "</center>";
                    $rData = array(
                        "email" => $r->email,
                        "name" => $r->name,
                        "last_name" => $r->last_name,
                        "area" => $r->area,
                        "campus" => $r->campus,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "activate_user":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                if (isset($_POST["user_id"])) {
                    $userID = base64_decode(strrev(urldecode($_POST["user_id"])));
                    if (!empty($usersFunctions->getUserByID($userID))) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La cuenta ha sido habilitada"
                        );
                        unset($result->error);
                        $usersFunctions->activateUserByID($userID);
                    } else {
                        $result->error = "Error: Usuario no encontrado";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "deactivate_user":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                if (isset($_POST["user_id"])) {
                    $userID = base64_decode(strrev(urldecode($_POST["user_id"])));
                    if (!empty($usersFunctions->getUserByID($userID))) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La cuenta ha sido deshabilitada"
                        );
                        unset($result->error);
                        $usersFunctions->deactivateUserByID($userID);
                    } else {
                        $result->error = "Error: Usuario no encontrado";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_faculties":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (F.`name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $adminFunctions->getFaculties("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $adminFunctions->getFaculties($searchQuery)->num_rows;

                // Fetch records
                $faculties = $adminFunctions->getFaculties($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : ""), $row, $rowperpage);
                $result_data = array();
                while ($r = $faculties->fetch_object()) {
                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Editar\" onclick=\"window.location = '/" . BASE_URL . "faculties/edit/" . urlencode(strrev(base64_encode($r->id))) . "';\">
                                <i class=\"align-middle\" data-feather=\"edit-3\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar\" onclick=\"openModalDelete('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"trash-2\" style=\"color: red;\"></i>
                            </a>
                        </center>";
                    $rData = array(
                        "name" => $r->name,
                        "careers_count" => "<center>" . $r->careers_count . "</center>",
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "delete_faculty":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                if (isset($_POST["faculty_id"])) {
                    $facultyID = base64_decode(strrev(urldecode($_POST["faculty_id"])));
                    if ($adminFunctions->getFacultyByID($facultyID)->num_rows > 0) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La facultad ha sido eliminada"
                        );
                        unset($result->error);
                        $adminFunctions->deleteFacultyByID($facultyID);
                    } else {
                        $result->error = "Error: Facultad no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_careers":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (C.`name` like '%" . $searchValue . "%' or F.`name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $adminFunctions->getCareers("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $adminFunctions->getCareers($searchQuery)->num_rows;

                // Fetch records
                $careers = $adminFunctions->getCareers($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : ""), $row, $rowperpage);
                $result_data = array();
                while ($r = $careers->fetch_object()) {
                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Editar\" onclick=\"window.location = '/" . BASE_URL . "careers/edit/" . urlencode(strrev(base64_encode($r->id))) . "';\">
                                <i class=\"align-middle\" data-feather=\"edit-3\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar\" onclick=\"openModalDelete('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"trash-2\" style=\"color: red;\"></i>
                            </a>
                        </center>";
                    $rData = array(
                        "name" => $r->name,
                        "faculty" => $r->faculty,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "delete_career":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                if (isset($_POST["career_id"])) {
                    $careerID = base64_decode(strrev(urldecode($_POST["career_id"])));
                    if ($adminFunctions->getCareerByID($careerID)->num_rows > 0) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La carrera ha sido eliminada"
                        );
                        unset($result->error);
                        $adminFunctions->deleteCareerByID($careerID);
                    } else {
                        $result->error = "Error: Carrera no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_occupations":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (`name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $adminFunctions->getOccupations("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $adminFunctions->getOccupations($searchQuery)->num_rows;

                // Fetch records
                $occupations = $adminFunctions->getOccupations($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : ""), $row, $rowperpage);
                $result_data = array();
                while ($r = $occupations->fetch_object()) {
                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Editar\" onclick=\"window.location = '/" . BASE_URL . "occupations/edit/" . urlencode(strrev(base64_encode($r->id))) . "';\">
                                <i class=\"align-middle\" data-feather=\"edit-3\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar\" onclick=\"openModalDelete('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"trash-2\" style=\"color: red;\"></i>
                            </a>
                        </center>";
                    $rData = array(
                        "name" => $r->name,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "delete_occupation":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                if (isset($_POST["occupation_id"])) {
                    $occupationID = base64_decode(strrev(urldecode($_POST["occupation_id"])));
                    if ($adminFunctions->getOccupationByID($occupationID)->num_rows > 0) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La ocupación ha sido eliminada"
                        );
                        unset($result->error);
                        $adminFunctions->deleteOccupationByID($occupationID);
                    } else {
                        $result->error = "Error: Ocupación no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_employees":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (U.`email` like '%" . $searchValue . "%' or P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%' or O.`name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $usersFunctions->getEmployees("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $usersFunctions->getEmployees($searchQuery)->num_rows;

                // Fetch records
                $doctors = $usersFunctions->getEmployees($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . ", `active` desc " : " order by `active` desc "), $row, $rowperpage);
                $result_data = array();
                while ($r = $doctors->fetch_object()) {
                    $columnActions = "<center>";
                    if ($r->active == 0) {
                        $columnActions .= "
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Habilitar cuenta\" onclick=\"openModalActivate('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"user-check\"></i>
                            </a>
                        ";
                    } else {
                        $columnActions .= "
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Deshabilitar cuenta\" onclick=\"openModalDeactivate('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"user-minus\"></i>
                            </a>
                        ";
                    }
                    $columnActions .= "</center>";
                    $rData = array(
                        "email" => $r->email,
                        "name" => $r->name,
                        "last_name" => $r->last_name,
                        "occupation" => $r->occupation,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_students":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (U.`email` like '%" . $searchValue . "%' or P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%' or C.`name` like '%" . $searchValue . "%' or UC.`semester` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $usersFunctions->getStudents("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $usersFunctions->getStudents($searchQuery)->num_rows;

                // Fetch records
                $doctors = $usersFunctions->getStudents($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . ", `active` desc " : " order by `active` desc "), $row, $rowperpage);
                $result_data = array();
                while ($r = $doctors->fetch_object()) {
                    $columnActions = "<center>";
                    if ($r->active == 0) {
                        $columnActions .= "
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Habilitar cuenta\" onclick=\"openModalActivate('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"user-check\"></i>
                            </a>
                        ";
                    } else {
                        $columnActions .= "
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Deshabilitar cuenta\" onclick=\"openModalDeactivate('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"user-minus\"></i>
                            </a>
                        ";
                    }
                    $columnActions .= "</center>";
                    $rData = array(
                        "email" => $r->email,
                        "name" => $r->name,
                        "last_name" => $r->last_name,
                        "career" => $r->career,
                        "semester" => $r->semester,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "exist_person":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "US") {
                if (isset($_POST["identification"]) && strlen($_POST["identification"]) >= 10 && strlen($_POST["identification"]) <= 15) {
                    $result->status = 200;
                    unset($result->error);

                    $person = $usFunctions->existsPerson($_POST["identification"]);
                    if ($person->num_rows > 0) {
                        $person = $person->fetch_object();

                        if ($usFunctions->existsFamiliarByUserID($_SESSION["dep_user_id"], $person->id)) {
                            $result = (object)[
                                "status" => 500,
                                "error" => "Error: La persona a buscar ya consta como su familiar",
                                "data" => null
                            ];
                        } else {
                            if ($_POST["identification"] != $_SESSION["dep_user_identification"] && $usFunctions->isAllowedFamiliar($person->id)) {
                                $result->data = array(
                                    "name" => $person->name,
                                    "last_name" => $person->last_name,
                                    "civil_state" => $person->civil_state,
                                    "birth_date" => date("m/d/Y", strtotime($person->birth_date)),
                                    "sex" => $person->sex,
                                    "phone" => $person->phone,
                                    "email" => $person->email,
                                    "address" => $person->address
                                );
                            } else {
                                $result->data = "-1";
                            }
                        }
                    }
                } else {
                    $result->error = "Error: Número de cédula no válido";
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_my_familiars":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "US" && $_SESSION["dep_user_is_employee"] == true) {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (P.`identification` like '%" . $searchValue . "%' or P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%' or UK.`kin` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $usFunctions->getMyFamiliars("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $usFunctions->getMyFamiliars($searchQuery)->num_rows;

                // Fetch records
                $familiars = $usFunctions->getMyFamiliars($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : ""), $row, $rowperpage);
                $result_data = array();
                while ($r = $familiars->fetch_object()) {
                    $d = array(
                        "identification" => $r->identification,
                        "kin" => $r->kin,
                        "fullname" => $r->name . " " . $r->last_name,
                        "civil_state" => $r->civil_state,
                        "birth_date" => date("m/d/Y", strtotime($r->birth_date)),
                        "phone" => $r->phone,
                        "email" => $r->email,
                        "address" => $r->address
                    );

                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Ver detalles\" onclick=\"openModalViewMyFamiliar('" . urlencode(strrev(base64_encode(json_encode($d)))) . "');\">
                                <i class=\"align-middle\" data-feather=\"eye\"></i>
                            </a>
                        </center>
                    ";
                    $rData = array(
                        "identification" => $r->identification,
                        "name" => $r->name,
                        "last_name" => $r->last_name,
                        "kin" => $r->kin,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_my_familiar_requests":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "US" && $_SESSION["dep_user_is_employee"] == true) {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and ((P.`identification` like '%" . $searchValue . "%' or FRD.`identification` like '%" . $searchValue . "%') or (P.`name` like '%" . $searchValue . "%' or FRD.`name` like '%" . $searchValue . "%') or (P.`last_name` like '%" . $searchValue . "%' or FRD.`lastname` like '%" . $searchValue . "%') or FR.`kin` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $usFunctions->getMyFamiliarRequests("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $usFunctions->getMyFamiliarRequests($searchQuery)->num_rows;

                // Fetch records
                $familiars = $usFunctions->getMyFamiliarRequests($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : ""), $row, $rowperpage);
                $result_data = array();
                while ($r = $familiars->fetch_object()) {
                    $d = array(
                        "created_at" => date("m/d/Y G:i:s", strtotime($r->created_at)),
                        "identification" => $r->identification,
                        "kin" => $r->kin,
                        "fullname" => $r->name . " " . $r->last_name,
                        "civil_state" => $r->civil_state,
                        "birth_date" => (($r->birth_date != null) ? date("m/d/Y", strtotime($r->birth_date)) : ""),
                        "phone" => $r->phone,
                        "email" => $r->email,
                        "address" => $r->address,
                        "backup_doc" => $r->backup_doc
                    );

                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Ver detalles\" onclick=\"openModalViewRequest('" . urlencode(strrev(base64_encode(json_encode($d)))) . "');\">
                                <i class=\"align-middle\" data-feather=\"eye\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar solicitud\" onclick=\"openModalDelete('" . urlencode(strrev(base64_encode($r->request_id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"trash-2\" style=\"color: red;\"></i>
                            </a>
                        </center>
                    ";
                    $rData = array(
                        "identification" => $r->identification,
                        "name" => $r->name,
                        "last_name" => $r->last_name,
                        "kin" => $r->kin,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "delete_familiar_request":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "US" && $_SESSION["dep_user_is_employee"] == true) {
                if (isset($_POST["request_id"])) {
                    $requestID = base64_decode(strrev(urldecode($_POST["request_id"])));
                    if ($usFunctions->getFamiliarRequestByID($requestID)->num_rows > 0) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La solicitud ha sido eliminada"
                        );
                        unset($result->error);
                        $usFunctions->deleteFamiliarRequestByID($requestID);
                    } else {
                        $result->error = "Error: Solicitud no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "load_available_schedule":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "US") {
                if ((isset($_POST["type"]) && !empty($_POST["type"])) && (isset($_POST["area"]) && !empty($_POST["area"]))) {
                    $areaID = base64_decode(strrev(urldecode($_POST["area"])));

                    $result->status = 200;
                    $result->data = array(
                        "days" => array(),
                        "hours" => array(),
                        "duration" => 20
                    );
                    unset($result->error);

                    $schedule = $usFunctions->getScheduleByAreaID($areaID);
                    if (!empty($schedule)) {
                        $result->data["days"] = json_decode($schedule->days);
                        if ($_POST["type"] == "P") {
                            $result->data["hours"] = json_decode($schedule->hours_p);
                        } else {
                            $result->data["hours"] = json_decode($schedule->hours_t);
                        }
                        $result->data["duration"] = $schedule->duration;
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "load_disabled_hours":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "US") {
                if ((isset($_POST["type"]) && !empty($_POST["type"])) && (isset($_POST["area"]) && !empty($_POST["area"])) && (isset($_POST["date"]) && !empty($_POST["date"]))) {
                    $areaID = base64_decode(strrev(urldecode($_POST["area"])));

                    $result->status = 200;
                    $result->data = array();
                    unset($result->error);

                    $turns = $usFunctions->getConfirmedTurnsByAreaDateType($areaID, $_POST["date"], $_POST["type"]);
                    while ($row = $turns->fetch_object()) {
                        array_push($result->data, $row->init_time);
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_my_appointment_requests":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "US") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%' or A.`name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $usFunctions->getMyAppointmentRequests("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $usFunctions->getMyAppointmentRequests($searchQuery)->num_rows;

                // Fetch records
                $familiars = $usFunctions->getMyAppointmentRequests($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : ""), $row, $rowperpage);
                $result_data = array();
                while ($r = $familiars->fetch_object()) {
                    $d = array(
                        "created_at" => date("m/d/Y G:i:s", strtotime($r->created_at)),
                        "identification" => $r->identification,
                        "fullname" => $r->fullname,
                        "full_area" => $r->full_area,
                        "type" => ($r->type == "P") ? "Presencial" : "Telemedicina",
                        "date" => date("m/d/Y", strtotime($r->date)),
                        "time" => date("G:i", strtotime($r->time)),
                        "description" => $r->description
                    );

                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Ver detalles\" onclick=\"openModalViewRequest('" . urlencode(strrev(base64_encode(json_encode($d)))) . "');\">
                                <i class=\"align-middle\" data-feather=\"eye\"></i>
                            </a>";
                    if ($r->status == "CR") {
                        $columnActions .= "
                                <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar solicitud\" onclick=\"openModalDelete('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                    <i class=\"align-middle\" data-feather=\"trash-2\" style=\"color: red;\"></i>
                                </a>";
                    }
                    $columnActions .= "</center>";
                    $rData = array(
                        "fullname" => $r->fullname,
                        "area" => $r->area,
                        "type" => $r->type,
                        "date" => date("m/d/Y", strtotime($r->date)),
                        "time" => date("G:i", strtotime($r->time)),
                        "status" => $r->status,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_my_recipe_requests":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "US") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%' or A.`name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $usFunctions->getMyRecipeRequests()->num_rows;

                // Total number of records with filtering
                

                // Fetch records
                $recipesdata= $usFunctions->getMyRecipeRequests();
                $result_data = array();
                while ($r = $recipesdata->fetch_object()) {
                    $d = array(
                        "date" => date("m/d/Y G:i:s", strtotime($r->date)),
                        "product" => $r->product,
                        "quantity" => $r->quantity,
                        "indications" => $r->indications
                    );
                    $result_data[] = $d;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecords,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "delete_appointment_request":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "US") {
                if (isset($_POST["request_id"])) {
                    $requestID = base64_decode(strrev(urldecode($_POST["request_id"])));
                    if ($usFunctions->getAppointmentRequestByID($requestID)->num_rows > 0) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La solicitud ha sido eliminada"
                        );
                        unset($result->error);
                        $usFunctions->deleteAppointmentRequestByID($requestID);
                    } else {
                        $result->error = "Error: Solicitud no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_careers_by_faculty":
            if (isset($_POST["faculty_id"])) {
                $careers = $panelFunctions->getCareersByFaculty($_POST["faculty_id"]);
                $result->status = 200;
                $result->data = array();
                unset($result->error);
                while ($r = $careers->fetch_object()) {
                    array_push($result->data, array(
                        "text" => $r->name,
                        "id" => $r->id
                    ));
                }
            }
            break;
        case "search_employees":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR") {
                if (isset($_POST["text"])) {
                    $employees = $usersFunctions->getEmployees("and (P.`identification` like '%" . $_POST["text"] . "%') ", "", 0, 5);
                    $result->status = 200;
                    $result->data = array();
                    unset($result->error);
                    while ($r = $employees->fetch_object()) {
                        array_push($result->data, array(
                            "value" => $r->identification . " - " . $r->name . " " . $r->last_name,
                            "id" => $r->id
                        ));
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_familiar_requests":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && $_SESSION["dep_user_area"] == 1) {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and ((P.`identification` like '%" . $searchValue . "%' or FRD.`identification` like '%" . $searchValue . "%') or (P.`name` like '%" . $searchValue . "%' or FRD.`name` like '%" . $searchValue . "%') or (P.`last_name` like '%" . $searchValue . "%' or FRD.`lastname` like '%" . $searchValue . "%') or FR.`kin` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $doctorFunctions->getFamiliarRequests("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $doctorFunctions->getFamiliarRequests($searchQuery)->num_rows;

                // Fetch records
                $familiars = $doctorFunctions->getFamiliarRequests($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : ""), $row, $rowperpage);
                $result_data = array();
                while ($r = $familiars->fetch_object()) {
                    $d = array(
                        "created_at" => date("m/d/Y G:i:s", strtotime($r->created_at)),
                        "identification" => $r->identification,
                        "kin" => $r->kin,
                        "fullname" => $r->name . " " . $r->last_name,
                        "civil_state" => $r->civil_state,
                        "birth_date" => (($r->birth_date != null) ? date("m/d/Y", strtotime($r->birth_date)) : ""),
                        "phone" => $r->phone,
                        "email" => $r->email,
                        "address" => $r->address,
                        "backup_doc" => $r->backup_doc
                    );

                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Ver detalles\" onclick=\"openModalViewRequest('" . urlencode(strrev(base64_encode(json_encode($d)))) . "');\">
                                <i class=\"align-middle\" data-feather=\"eye\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Aceptar solicitud\" onclick=\"openModalAccept('" . urlencode(strrev(base64_encode($r->request_id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"check\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Rechazar solicitud\" onclick=\"openModalDecline('" . urlencode(strrev(base64_encode($r->request_id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"x\" style=\"color: red;\"></i>
                            </a>
                        </center>
                    ";
                    $rData = array(
                        "identification" => $r->identification,
                        "name" => $r->name,
                        "last_name" => $r->last_name,
                        "kin" => $r->kin,
                        "employee" => $r->employee,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "accept_familiar_request":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && $_SESSION["dep_user_area"] == 1) {
                if (isset($_POST["request_id"])) {
                    $requestID = base64_decode(strrev(urldecode($_POST["request_id"])));
                    $fr = $usFunctions->getFamiliarRequestByID($requestID);
                    if ($fr->num_rows > 0) {
                        $ir = 0;
                        $isCreated = false;
                        $fr = $fr->fetch_object();
                        if ($fr->person_id == null) {
                            $frd = $doctorFunctions->getFamiliarRequestDetailByID($requestID)->fetch_object();
                            $personID = gen_uuid();
                            $ir = $usersFunctions->createPerson($personID, $frd->name, $frd->lastname, $frd->identification, $frd->email, $frd->phone, $frd->birth_date, $frd->civil_state, $frd->address, $frd->sex);
                            if ($ir > 0) {
                                $ir = $doctorFunctions->createUserKinship($fr->user_id, $personID, $fr->kin);
                                if ($ir > 0) {
                                    $ir = $doctorFunctions->createDoc($personID, "Cédula", $frd->backup_doc);
                                    if ($ir > 0) {
                                        $isCreated = true;
                                    } else {
                                        $doctorFunctions->deleteUserKinship($fr->user_id, $personID);
                                        $usersFunctions->deletePerson($personID);
                                    }
                                } else {
                                    $usersFunctions->deletePerson($personID);
                                }
                            }
                        } else {
                            $ir = $doctorFunctions->createUserKinship($fr->user_id, $fr->person_id, $fr->kin);
                            if ($ir > 0) {
                                $isCreated = true;
                            }
                        }

                        if ($isCreated) {
                            $result->status = 200;
                            $result->data = array(
                                "msg" => "La solicitud ha sido aceptada"
                            );
                            unset($result->error);
                            $doctorFunctions->deleteFamiliarRequestByID($requestID);
                            if (ENABLE_EMAIL_SENDING) {
                                try {
                                    $emp = $usersFunctions->getUserByID($fr->user_id);
                                    MailSender::send_mail(
                                        "Dpto. Médico UTEQ",
                                        array(
                                            $emp->username => $emp->name . " " . $emp->last_name
                                        ),
                                        "Notificación sobre solicitud de familiar",
                                        "Su solicitud de familiar ha sido aceptada, consulte en el sistema para más información.
                                        <br/><br/>
                                        No responder a este correo.<br/>
                                        Para mayor información contactarse a:<br/>
                                        <strong>Departamento médico:</strong> medicos@uteq.edu.ec<br/>
                                        <strong>Enfermería:</strong> Lcda. Gabriela Alvarez Ayala - galvareza@uteq.edu.ec<br/>
                                        <strong>Medicina General:</strong> Dra. Miryam Loor Intriago - mloor@uteq.edu.ec<br/>
                                        <strong>Odontología:</strong> Odontólogo. Cristhian Solano Chichande - csolano@uteq.edu.ec"
                                    );
                                } catch (\Throwable $th) {
                                }
                            }
                        } else {
                            $result->error = "Error: No se ha podido aceptar la solicitud";
                        }
                    } else {
                        $result->error = "Error: Solicitud no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "decline_familiar_request":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && $_SESSION["dep_user_area"] == 1) {
                if (isset($_POST["request_id"])) {
                    $requestID = base64_decode(strrev(urldecode($_POST["request_id"])));
                    $request = $usFunctions->getFamiliarRequestByID($requestID);
                    if ($request->num_rows > 0) {
                        $request = $request->fetch_object();
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La solicitud ha sido rechazada"
                        );
                        unset($result->error);
                        if ($request->person_id == null) {
                            $requestDetail = $doctorFunctions->getFamiliarRequestDetailByID($requestID);
                            if ($requestDetail->num_rows > 0) {
                                $requestDetail = $requestDetail->fetch_object();
                                if (str_contains($requestDetail->backup_doc, "/media/backup_docs_temp/")) {
                                    if (!empty(BASE_URL)) {
                                        $strrep = str_replace("/" . BASE_URL, "", $requestDetail->backup_doc);
                                        unlink($strrep);
                                    } else {
                                        unlink(str_replace("/media", "media", $requestDetail->backup_doc));
                                    }
                                }
                            }
                        }
                        $doctorFunctions->deleteFamiliarRequestByID($requestID);
                        if (ENABLE_EMAIL_SENDING) {
                            try {
                                $emp = $usersFunctions->getUserByID($request->user_id);
                                MailSender::send_mail(
                                    "Dpto. Médico UTEQ",
                                    array(
                                        $emp->username => $emp->name . " " . $emp->last_name
                                    ),
                                    "Notificación sobre solicitud de familiar",
                                    "Su solicitud de familiar ha sido rechazada, consulte en el sistema para más información.
                                    <br/><br/>
                                    No responder a este correo.<br/>
                                    Para mayor información contactarse a:<br/>
                                    <strong>Departamento médico:</strong> medicos@uteq.edu.ec<br/>
                                    <strong>Enfermería:</strong> Lcda. Gabriela Alvarez Ayala - galvareza@uteq.edu.ec<br/>
                                    <strong>Medicina General:</strong> Dra. Miryam Loor Intriago - mloor@uteq.edu.ec<br/>
                                    <strong>Odontología:</strong> Odontólogo. Cristhian Solano Chichande - csolano@uteq.edu.ec"
                                );
                            } catch (\Throwable $th) {
                            }
                        }
                    } else {
                        $result->error = "Error: Solicitud no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_patients":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (P.`identification` like '%" . $searchValue . "%' or P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $doctorFunctions->getPatients("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $doctorFunctions->getPatients($searchQuery)->num_rows;

                // Fetch records
                $patients = $doctorFunctions->getPatients($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : ""), $row, $rowperpage);
                $result_data = array();
                while ($r = $patients->fetch_object()) {
                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Atender\" onclick=\"window.open('/".BASE_URL."patient-attention/".urlencode(strrev(base64_encode($r->id)))."','_self');\">
                                <i class=\"align-middle\" data-feather=\"clipboard\"></i>
                            </a>
                        </center>
                    ";
                    $rData = array(
                        "identification" => $r->identification,
                        "name" => $r->name,
                        "last_name" => $r->last_name,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "load_appointments_by_area":
            $result->status = 200;
            $result->data = array();
            unset($result->error);

            $doctorFunctions->updateConfirmedAppointmentsToAttended();
            $doctorFunctions->updateConfirmedAppointmentsToNoAttended();
            $areaID = $doctorFunctions->getMyArea();
            $appointments = $doctorFunctions->getConfirmedAppointmentsByAreaID($areaID);
            while ($r = $appointments->fetch_object()) {
                array_push($result->data, array(
                    "id" => $r->id,
                    "title" => $r->patient,
                    "body" => $r->description,
                    "calendar" => (($r->type == "P") ? 1 : 2),
                    "start" => $r->date . " " . $r->init_time,
                    "end" => $r->date . " " . $r->end_time
                ));
            }
            break;
        case "cancel_appointment":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && in_array($_SESSION["dep_user_area"], [2, 3])) {
                if (isset($_POST["appointment_id"])) {
                    $appointment = $doctorFunctions->getAppointmentByID($_POST["appointment_id"]);
                    if ($appointment->num_rows > 0) {
                        $appointment = $appointment->fetch_object();
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La cita ha sido cancelada"
                        );
                        unset($result->error);
                        $doctorFunctions->cancelAppointmentByID($_POST["appointment_id"]);
                        if (ENABLE_EMAIL_SENDING) {
                            try {
                                $person = $doctorFunctions->getPatients(" and P.`id`='" . $appointment->person_id . "' ");
                                if ($person->num_rows > 0) {
                                    $person = $person->fetch_object();
                                    if (!empty($person->email)) {
                                        MailSender::send_mail(
                                            "Dpto. Médico UTEQ",
                                            array(
                                                $person->email => $person->name . " " . $person->last_name
                                            ),
                                            "Notificación sobre cita",
                                            "Su cita ha sido cancelada, consulte en el sistema para más información.
                                            <br/><br/>
                                            No responder a este correo.<br/>
                                            Para mayor información contactarse a:<br/>
                                            <strong>Departamento médico:</strong> medicos@uteq.edu.ec<br/>
                                            <strong>Enfermería:</strong> Lcda. Gabriela Alvarez Ayala - galvareza@uteq.edu.ec<br/>
                                            <strong>Medicina General:</strong> Dra. Miryam Loor Intriago - mloor@uteq.edu.ec<br/>
                                            <strong>Odontología:</strong> Odontólogo. Cristhian Solano Chichande - csolano@uteq.edu.ec"
                                        );
                                    }
                                }
                            } catch (\Throwable $th) {
                            }
                        }
                    } else {
                        $result->error = "Error: Cita no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_appointment_requests":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && in_array($_SESSION["dep_user_area"], [2, 3])) {
                $doctorFunctions->deleteExpiredCreatedAppointments();
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $doctorFunctions->getAppointmentRequests("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $doctorFunctions->getAppointmentRequests($searchQuery)->num_rows;

                // Fetch records
                $familiars = $doctorFunctions->getAppointmentRequests($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . ", `date` asc, `time` asc, `created_at` asc " : " order by `date` asc, `time` asc, `created_at` asc "), $row, $rowperpage);
                $result_data = array();
                while ($r = $familiars->fetch_object()) {
                    $d = array(
                        "created_at" => date("m/d/Y G:i:s", strtotime($r->created_at)),
                        "identification" => $r->identification,
                        "fullname" => $r->fullname,
                        "full_area" => $r->full_area,
                        "type" => ($r->type == "P") ? "Presencial" : "Telemedicina",
                        "date" => date("m/d/Y", strtotime($r->date)),
                        "time" => date("G:i", strtotime($r->time)),
                        "description" => $r->description
                    );

                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Ver detalles\" onclick=\"openModalViewRequest('" . urlencode(strrev(base64_encode(json_encode($d)))) . "');\">
                                <i class=\"align-middle\" data-feather=\"eye\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Aceptar solicitud\" onclick=\"openModalAccept('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"check\" style=\"color: green;\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Rechazar solicitud\" onclick=\"openModalDecline('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"x\" style=\"color: red;\"></i>
                            </a>
                        </center>";
                    $rData = array(
                        "patient" => $r->fullname,
                        "date" => date("m/d/Y", strtotime($r->date)),
                        "time" => date("G:i", strtotime($r->time)),
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "accept_appointment_request":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && in_array($_SESSION["dep_user_area"], [2, 3])) {
                if (isset($_POST["request_id"])) {
                    $requestID = base64_decode(strrev(urldecode($_POST["request_id"])));
                    $request = $usFunctions->getAppointmentRequestByID($requestID);
                    if ($request->num_rows > 0) {
                        $request = $request->fetch_object();
                        if (!$doctorFunctions->existsConfirmedAppointmentWithSameDateTime($request->date, $request->init_time)) {
                            $result->status = 200;
                            $result->data = array(
                                "msg" => "La solicitud ha sido aceptada"
                            );
                            unset($result->error);
                            $doctorFunctions->acceptAppointmentRequestByID($requestID);
                            $doctorFunctions->declineAppointmentRequestByDateTime($request->date, $request->init_time);
                            //
                            if (ENABLE_EMAIL_SENDING) {
                                try {
                                    $sendTo = $usersFunctions->getUserByID($request->created_by);
                                    $linkText = "";
                                    if ($request->type == "T") {
                                        $meetLink = $doctorFunctions->getSettingsByAreaID($request->area_id);
                                        if (!empty($meetLink) && $meetLink != false && !empty($meetLink->meet_link)) {
                                            $linkText = " el enlace para acceder a la cita por telemedicina es " . $meetLink->meet_link . ",";
                                        }
                                    }
                                    MailSender::send_mail(
                                        "Dpto. Médico UTEQ",
                                        array(
                                            $sendTo->username => $sendTo->name . " " . $sendTo->last_name
                                        ),
                                        "Notificación sobre solicitud de cita",
                                        "Su solicitud de cita ha sido aceptada," . $linkText . " consulte en el sistema para más información.
                                        <br/><br/>
                                        No responder a este correo.<br/>
                                        Para mayor información contactarse a:<br/>
                                        <strong>Departamento médico:</strong> medicos@uteq.edu.ec<br/>
                                        <strong>Enfermería:</strong> Lcda. Gabriela Alvarez Ayala - galvareza@uteq.edu.ec<br/>
                                        <strong>Medicina General:</strong> Dra. Miryam Loor Intriago - mloor@uteq.edu.ec<br/>
                                        <strong>Odontología:</strong> Odontólogo. Cristhian Solano Chichande - csolano@uteq.edu.ec"
                                    );
                                } catch (\Throwable $th) {
                                }
                            }
                            //
                        } else {
                            $result->error = "Error: Ya existe una cita para esa fecha y hora";
                        }
                    } else {
                        $result->error = "Error: Solicitud no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "decline_appointment_request":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && in_array($_SESSION["dep_user_area"], [2, 3])) {
                if (isset($_POST["request_id"])) {
                    $requestID = base64_decode(strrev(urldecode($_POST["request_id"])));
                    $request = $usFunctions->getAppointmentRequestByID($requestID);
                    if ($request->num_rows > 0) {
                        $request = $request->fetch_object();
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La solicitud ha sido rechazada"
                        );
                        unset($result->error);
                        $doctorFunctions->declineAppointmentRequestByID($requestID);
                        //
                        if (ENABLE_EMAIL_SENDING) {
                            try {
                                $sendTo = $usersFunctions->getUserByID($request->created_by);
                                MailSender::send_mail(
                                    "Dpto. Médico UTEQ",
                                    array(
                                        $sendTo->username => $sendTo->name . " " . $sendTo->last_name
                                    ),
                                    "Notificación sobre solicitud de cita",
                                    "Su solicitud de cita ha sido rechazada, consulte en el sistema para más información.
                                    <br/><br/>
                                    No responder a este correo.<br/>
                                    Para mayor información contactarse a:<br/>
                                    <strong>Departamento médico:</strong> medicos@uteq.edu.ec<br/>
                                    <strong>Enfermería:</strong> Lcda. Gabriela Alvarez Ayala - galvareza@uteq.edu.ec<br/>
                                    <strong>Medicina General:</strong> Dra. Miryam Loor Intriago - mloor@uteq.edu.ec<br/>
                                    <strong>Odontología:</strong> Odontólogo. Cristhian Solano Chichande - csolano@uteq.edu.ec"
                                );
                            } catch (\Throwable $th) {
                            }
                        }
                        //
                    } else {
                        $result->error = "Error: Solicitud no encontrada";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "search_patient":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR") {
                //Read values
                $searchValue = $_POST["q"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (P.`identification` like '%" . $searchValue . "%' or P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%') ";
                }

                // Fetch records
                $patients = $doctorFunctions->getPatients($searchQuery, "", 0, 5);
                $result_data = array();
                while ($r = $patients->fetch_object()) {
                    $rData = array(
                        "id" => $r->id,
                        "text" => $r->name . " " . $r->last_name . " (" . $r->identification . ")",
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = $result_data;
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "create_appointment":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && in_array($_SESSION["dep_user_area"], [2, 3])) {
                if (isset($_POST["patient_id"]) && isset($_POST["date"]) && isset($_POST["type"]) && isset($_POST["description"])) {
                    $isCreated = false;
                    $areaID = $doctorFunctions->getMyArea();
                    $date = date("Y-m-d", strtotime(explode(" ", $_POST["date"])[0]));
                    $init_time = date("G:i:s", strtotime(explode(" ", $_POST["date"])[1]));
                    $res = $doctorFunctions->insertAppointment($areaID, $_POST["patient_id"], $date, $init_time, 'CO', $_POST["type"], $_POST["description"]);
                    if ($res > 0) {
                        $isCreated = true;
                    }
                    if ($isCreated) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "La cita ha sido registrada exitosamente."
                        );
                        unset($result->error);
                        if (ENABLE_EMAIL_SENDING) {
                            try {
                                $person = $doctorFunctions->getPatients(" and P.`id`='" . $_POST["patient_id"] . "' ");
                                if ($person->num_rows > 0) {
                                    $person = $person->fetch_object();
                                    if (!empty($person->email)) {
                                        $linkText = "";
                                        if ($_POST["type"] == "T") {
                                            $meetLink = $doctorFunctions->getSettingsByAreaID($areaID);
                                            if (!empty($meetLink) && $meetLink != false && !empty($meetLink->meet_link)) {
                                                $linkText = " el enlace para acceder a la cita por telemedicina es " . $meetLink->meet_link . ",";
                                            }
                                        }
                                        MailSender::send_mail(
                                            "Dpto. Médico UTEQ",
                                            array(
                                                $person->email => $person->name . " " . $person->last_name
                                            ),
                                            "Notificación sobre cita",
                                            "Ha sido creada una nueva cita para usted," . $linkText . " consulte en el sistema para más información.
                                            <br/><br/>
                                            No responder a este correo.<br/>
                                            Para mayor información contactarse a:<br/>
                                            <strong>Departamento médico:</strong> medicos@uteq.edu.ec<br/>
                                            <strong>Enfermería:</strong> Lcda. Gabriela Alvarez Ayala - galvareza@uteq.edu.ec<br/>
                                            <strong>Medicina General:</strong> Dra. Miryam Loor Intriago - mloor@uteq.edu.ec<br/>
                                            <strong>Odontología:</strong> Odontólogo. Cristhian Solano Chichande - csolano@uteq.edu.ec"
                                        );
                                    }
                                }
                            } catch (\Throwable $th) {
                            }
                        }
                    } else {
                        if($res==-1)
                        {
                            $result->error = "La fecha u hora se encuentran fuera de los límites.";
                        }
                        else{
                            $result->error = "La cita no ha podido ser registrada.";
                        }
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_appointments":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && in_array($_SESSION["dep_user_area"], [1, 2, 3])) {
                $doctorFunctions->updateConfirmedAppointmentsToAttended();
                $doctorFunctions->updateConfirmedAppointmentsToNoAttended();
                $areaID = $doctorFunctions->getMyArea();
                $link = $doctorFunctions->getSettingsByAreaID($areaID);
                $linkText = "";
                if (!empty($link) || $link != false) {
                    $link = $link->meet_link;
                    if (empty($link)) {
                        $linkText = "<span class=\"mx-1 badge rounded-pill bg-danger\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Para establecer un enlace de meet ingrese a la opción de Configuración que está a la izquierda\">No hay enlace de meet</span>";
                        $link = "javascript:;";
                    }
                } else {
                    $linkText = "<span class=\"mx-1 badge rounded-pill bg-danger\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Para establecer un enlace de meet ingrese a la opción de Configuración que está a la izquierda\">No hay enlace de meet</span>";
                    $link = "javascript:;";
                }

                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = "";
                }

                // Total number of records without filtering
                $totalRecords = $doctorFunctions->getConfirmedAppointments("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $doctorFunctions->getConfirmedAppointments($searchQuery)->num_rows;

                // Fetch records
                $appointments = $doctorFunctions->getConfirmedAppointments($searchQuery, " order by `date` asc, `init_time` asc ", $row, $rowperpage);
                $result_data = array();
                while ($r = $appointments->fetch_object()) {
                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Atender\" onclick=\"attendAppointment('" . urlencode(strrev(base64_encode($r->id))) . "','" . urlencode(strrev(base64_encode($r->person_id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"clipboard\"></i>
                            </a>";
                    if ($r->type == "T") {
                        $columnActions .= "
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Ingresar a Meet\" onclick=\"window.open('" . $link . "','_blank');\">
                                <i class=\"align-middle\" data-feather=\"video\"></i>
                            </a>";
                    }
                    $columnActions .= "</center>";

                    $rData = array(
                        "patient" => "<strong>" . $r->patient . "</strong><br/><small>" . $r->identification . "</small>",
                        "date" => date("m/d/Y G:i", strtotime($r->date . " " . $r->init_time)),
                        "type" => "<center>" . (($r->type == "T") ? "<span class=\"badge rounded-pill bg-primary\">Telemedicina</span>" . $linkText : "<span class=\"badge rounded-pill bg-success\">Presencial</span>") . "</center>",
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "save_appointment_to_session":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && in_array($_SESSION["dep_user_area"], [1, 2, 3])) {
                if (isset($_POST["appointment_id"])) {
                    $result->status = 200;
                    unset($result->error);
                    $_SESSION["dep_current_appointment_id"] = base64_decode(strrev(urldecode($_POST["appointment_id"])));
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_products":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && in_array($_SESSION["dep_user_area"], [1, 2, 3])) {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (`name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $doctorFunctions->getProducts("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $doctorFunctions->getProducts($searchQuery)->num_rows;

                // Fetch records
                $products = $doctorFunctions->getProducts($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : ""), $row, $rowperpage);
                $result_data = array();
                while ($r = $products->fetch_object()) {
                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Aumentar Stock\" onclick=\"openModalIncreaseStock('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"shopping-bag\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Editar\" onclick=\"window.location = '/" . BASE_URL . "products/edit/" . urlencode(strrev(base64_encode($r->id))) . "';\">
                                <i class=\"align-middle\" data-feather=\"edit-3\"></i>
                            </a>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar\" onclick=\"openModalDelete('" . urlencode(strrev(base64_encode($r->id))) . "');\">
                                <i class=\"align-middle\" data-feather=\"trash-2\" style=\"color: red;\"></i>
                            </a>
                        </center>";
                    $rData = array(
                        "image" => "<center><img height='96' width='96' alt='Producto' src='".((!empty($r->image)) ? $r->image : "/".BASE_URL."assets/dist/img/no_image.png")."' onerror=\"src='/".BASE_URL."assets/dist/img/no_image.png'\" class='img-responsive'></img></center>",
                        "name" => $r->name,
                        "description" => base64_encode($r->description),
                        "units" => $r->units,
                        "column_actions" => ($_SESSION["dep_user_area"] == 1) ? $columnActions : ""
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "delete_product":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && $_SESSION["dep_user_area"] == 1) {
                if (isset($_POST["product_id"])) {
                    $productID = base64_decode(strrev(urldecode($_POST["product_id"])));
                    $product = $doctorFunctions->getProductByID($productID);
                    if (!empty($product)) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "El producto ha sido eliminado"
                        );
                        unset($result->error);
                        $doctorFunctions->deleteProductByID($productID);
                        $log_details = (object)[
                            "before" => (object)[
                                "name" => $product->name,
                                "image" => $product->image,
                                "units" => $product->units,
                                "description" => $product->description
                            ],
                            "after" => null
                        ];
                        $doctorFunctions->auditProduct("delete", $productID, json_encode($log_details));
                    } else {
                        $result->error = "Error: Producto no encontrado";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "increase_stock":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR" && $_SESSION["dep_user_area"] == 1) {
                if (isset($_POST["product_id"]) && isset($_POST["units"])) {
                    $productID = base64_decode(strrev(urldecode($_POST["product_id"])));
                    $product = $doctorFunctions->getProductByID($productID);
                    if (!empty($product)) {
                        $result->status = 200;
                        $result->data = array(
                            "msg" => "Se ha aumentado el stock del producto seleccionado"
                        );
                        unset($result->error);
                        $doctorFunctions->increaseProductStockByID($productID, (intval($product->units) + intval($_POST["units"])));
                        $log_details = (object)[
                            "before" => (object)[
                                "name" => $product->name,
                                "image" => $product->image,
                                "units" => $product->units,
                                "description" => $product->description
                            ],
                            "after" => (object)[
                                "name" => $product->name,
                                "image" => $product->image,
                                "units" => (intval($product->units) + intval($_POST["units"])),
                                "description" => $product->description
                            ]
                        ];
                        $doctorFunctions->auditProduct("increase_stock", $productID, json_encode($log_details));
                    } else {
                        $result->error = "Error: Producto no encontrado";
                    }
                }
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "get_audit_products":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "AD") {
                //Read values
                $draw = $_POST["draw"];
                $row = $_POST["start"];
                $rowperpage = $_POST["length"];
                $columnIndex = (isset($_POST["order"])) ? $_POST["order"][0]["column"] : null;
                $columnName = ($columnIndex != null) ? $_POST["columns"][$columnIndex]["data"] : "";
                $columnSortOrder = (isset($_POST["order"])) ? $_POST["order"][0]["dir"] : "asc";
                $searchValue = $_POST["search"]["value"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (P.`name` like '%" . $searchValue . "%' or P.`last_name` like '%" . $searchValue . "%' or U.`email` like '%" . $searchValue . "%' or PR.`name` like '%" . $searchValue . "%') ";
                }

                // Total number of records without filtering
                $totalRecords = $doctorFunctions->getAuditProducts("")->num_rows;

                // Total number of records with filtering
                $totalRecordWithFilter = $doctorFunctions->getAuditProducts($searchQuery)->num_rows;

                // Fetch records
                $audit = $doctorFunctions->getAuditProducts($searchQuery, ((!empty($columnName)) ? " order by " . $columnName . " " . $columnSortOrder . " " : " order by `datetime` desc "), $row, $rowperpage);
                $result_data = array();
                while ($r = $audit->fetch_object()) {
                    $columnActions = "
                        <center>
                            <a data-toggle=\"tooltip\" data-placement=\"top\" title=\"Ver detalles\" onclick=\"openModalViewDetails('".base64_encode($r->product)."','" . base64_encode($r->details) . "');\">
                                <i class=\"align-middle\" data-feather=\"eye\"></i>
                            </a>
                        </center>";
                    $rData = array(
                        "user" => $r->user,
                        "email" => $r->email,
                        "action" => $r->action,
                        "product" => $r->product,
                        "datetime" => $r->datetime,
                        "column_actions" => $columnActions
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = array(
                    "draw" => intval($draw),
                    "iTotalRecords" => $totalRecordWithFilter,
                    "iTotalDisplayRecords" => $totalRecords,
                    "aaData" => $result_data
                );
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "search_product":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR") {
                //Read values
                $searchValue = $_POST["q"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (`name` like '%" . $searchValue . "%') ";
                }

                // Fetch records
                $products = $doctorFunctions->getProducts($searchQuery, "", 0, 20);
                $result_data = array();
                while ($r = $products->fetch_object()) {
                    $rData = array(
                        "id" => $r->id,
                        "text" => $r->name,
                        "qty" => $r->units
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = $result_data;
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "search_product2":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR") {
                //Read values
                $searchValue = $_POST["q"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = "`nombre_local` like '%". $searchValue ."%'";
                }

                // Fetch records
                $products = $doctorFunctions->getMedicines($searchQuery, "", 0, 20);
                $result_data = array();
                while ($r = $products->fetch_object()) {
                    $rData = array(
                        "id" => $r->id,
                        "text" => $r->text,
                        "qty" => $r->qty
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = $result_data;
            } else {
                $result->error = "Error: No autorizado";
            }
        break;
        case "select_general":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR") {
                //Read values
                $searchValue = $_POST["q"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = " and (`name` like '%" . $searchValue . "%') ";
                }

                // Fetch records
                $products = $doctorFunctions->getProducts($searchQuery, "", 0, 20);
                $result_data = array();
                while ($r = $products->fetch_object()) {
                    $rData = array(
                        "id" => $r->id,
                        "text" => $r->name,
                        "qty" => $r->units
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = $result_data;
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
        case "search_diagnosis":
            if (isset($_SESSION["dep_user_role"]) && $_SESSION["dep_user_role"] == "DR") {
                //Read values
                $searchValue = $_POST["q"];

                //Search
                $searchQuery = "";
                if (trim($searchValue) != "") {
                    $searchQuery = "`descripcion` like '%" . $searchValue . "%' or `clave` like '%" . $searchValue . "%' ";
                }

                // Fetch records
                $products = $doctorFunctions->getDiagnosis($searchQuery, "", 0, 20);
                $result_data = array();
                while ($r = $products->fetch_object()) {
                    $rData = array(
                        "id" => $r->id,
                        "text" => $r->text,
                        "qty" => $r->qty
                    );
                    $result_data[] = $rData;
                }

                // Response
                $result = $result_data;
            } else {
                $result->error = "Error: No autorizado";
            }
            break;
    }
}

echo json_encode($result);

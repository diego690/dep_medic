<?php
require_once(__DIR__ . "/system/loader.php");
require_once(__DIR__ . "/data/mysql/panel.functions.php");

$panelFunctions = new PanelFunctions();

$result = (object)[
    "status" => 500,
    "error" => "Error: Parámetros faltantes o incorrectos",
    "data" => null
];

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
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
    }
}

echo json_encode($result);

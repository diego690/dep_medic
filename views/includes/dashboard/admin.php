<?php
require_once(__DIR__ . "/../../../system/loader.php");
require_once(__DIR__ . "/../../../data/mysql/users.functions.php");
require_once(__DIR__ . "/../../../data/mysql/report.functions.php");
$userFunctions = new UsersFunctions();
$reportFunctions = new ReportFunctions();

$currentYear = date("Y", strtotime(getCurrentTimestamp()));
$doctorsList = $reportFunctions->getDoctors();
$userCountData = array(
    "nursing" => array(0, 0),
    "medicine" => array(0, 0),
    "odontology" => array(0, 0),
    "patient" => array(0, 0, 0)
);
while ($row = $doctorsList->fetch_object()) {
    $temp = $reportFunctions->getAreaByUserID($row->id);
    if (!empty($temp)) {
        $temp_area = "";
        $temp_campus = -1;
        switch ($temp->name) {
            case "Enfermería":
                $temp_area = "nursing";
                break;
            case "Medicina":
                $temp_area = "medicine";
                break;
            case "Odontología":
                $temp_area = "odontology";
                break;
        }
        if ($temp->campus == "Central") {
            $temp_campus = 0;
        } else if ($temp->campus == "La María") {
            $temp_campus = 1;
        }

        if (!empty($temp_area) && $temp_campus != -1) {
            $userCountData[$temp_area][$temp_campus] += 1;
        }
    }
}
$userCountData["patient"][0] = $reportFunctions->getCountStudents();
$userCountData["patient"][1] = $reportFunctions->getCountEmployees();
$userCountData["patient"][2] = $reportFunctions->getCountKins();
?>

<div class="row">
    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <h3 class="mb-2">Enfermería</h3>
                        <p class="mb-0"><?= $userCountData["nursing"][0] ?> Doctores (Central)</p>
                        <p class="mb-0"><?= $userCountData["nursing"][1] ?> Doctores (La María)</p>
                    </div>
                    <div class="d-inline-block ms-3">
                        <div class="stat">
                            <i class="align-middle text-success fas fa-user-md"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <h3 class="mb-2">Medicina</h3>
                        <p class="mb-0"><?= $userCountData["medicine"][0] ?> Doctores (Central)</p>
                        <p class="mb-0"><?= $userCountData["medicine"][1] ?> Doctores (La María)</p>
                    </div>
                    <div class="d-inline-block ms-3">
                        <div class="stat">
                            <i class="align-middle text-success fas fa-user-md"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <h3 class="mb-2">Odontología</h3>
                        <p class="mb-0"><?= $userCountData["odontology"][0] ?> Doctores (Central)</p>
                        <p class="mb-0"><?= $userCountData["odontology"][1] ?> Doctores (La María)</p>
                    </div>
                    <div class="d-inline-block ms-3">
                        <div class="stat">
                            <i class="align-middle text-success fas fa-user-md"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body py-4">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <h3 class="mb-2">Pacientes</h3>
                        <p class="mb-0"><?= $userCountData["patient"][0] ?> Estudiantes</p>
                        <p class="mb-0"><?= $userCountData["patient"][1] ?> Empleados</p>
                        <p class="mb-0"><?= $userCountData["patient"][2] ?> Familiares</p>
                    </div>
                    <div class="d-inline-block ms-3">
                        <div class="stat">
                            <i class="align-middle text-success fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Nuevos Pacientes <?= $currentYear ?></h5>
            </div>
            <div class="card-body">
                <center><code>No hay datos.</code></center>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Citas <?= $currentYear ?></h5>
            </div>
            <div class="card-body">
                <center><code>No hay datos.</code></center>
            </div>
        </div>
    </div>
</div>
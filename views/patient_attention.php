<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

$doctorFunctions = new DoctorFunctions();
if ($_SESSION["dep_user_role"] != "DR" || !in_array($_SESSION["dep_user_area"], [1, 2, 3])) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$patientID = base64_decode(strrev(urldecode(explode("/", $_GET["patient_id"])[0])));
$patientData = $doctorFunctions->getPatientDataByID($patientID);
if (empty($patientData)) {
    header("Location: /" . BASE_URL . "home");
    exit();
}

$area = $doctorFunctions->getMyArea();
$msg_response = array(
    "errors" => array(),
    "success" => array()
);

//Validate to avoid POST duplicates
$post_validation_session = (isset($_SESSION['post_id'])) ? $_SESSION['post_id'] : "";
$post_validation_form = (isset($_POST['post_id'])) ? $_POST['post_id'] : "";
//POST ACTION
$is_post = (count($_POST) > 0) && ($post_validation_session != $post_validation_form);
if ($is_post) {
    $_SESSION['post_id'] = $_POST['post_id']; //Save this post instance to avoid duplicates

    // Toast
    if (isset($_POST['response_status'])) {
        if ($_POST['response_status'] == 200) {
            array_push($msg_response["success"], $_POST['response_msg']);
        } else {
            array_push($msg_response["errors"], $_POST['response_msg']);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Atención al Paciente</title>

    <?php
    include_once("includes/styles.php");
    ?>
    <style>
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>

</head>
<!--
  HOW TO USE: 
  data-theme: default (default), colored, dark, light
  data-layout: fluid (default), boxed
  data-sidebar-position: left (default), right
  data-sidebar-behavior: sticky (default), fixed, compact
-->

<body data-theme="colored" data-layout="fluid" data-sidebar-position="left" data-sidebar-behavior="<?= SIDEBAR_TYPE ?>">
    <div class="wrapper">

        <?php
        include_once("includes/sidebar.php");
        ?>

        <div class="main">

            <?php
            include_once("includes/navbar.php");
            ?>

            <main class="content">
                <div class="container-fluid p-0">

                    <?php if (!empty($_SESSION["dep_current_appointment_id"])) { ?>
                        <div class="alert alert-info alert-dismissible" role="alert">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <div class="alert-icon">
                                <i class="fas fa-fw fa-info"></i>
                            </div>
                            <div class="alert-message">
                                <strong>
                                    Actualmente está registrando datos a partir de una cita con este paciente.
                                    <br />Luego de guardar un registro nuevo, la cita se tomará como atendida.
                                </strong>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-6 col-md-4">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Información General</h5>
                                </div>
                                <div class="card-body text-center">
                                    <img src="<?= (!empty($patientData->avatar)) ? $patientData->avatar : "/" . BASE_URL . "assets/dist/img/user_avatar.png" ?>" onerror="src='/<?= BASE_URL ?>assets/assets/dist/img/user_avatar.png'" alt="Avatar" class="rounded-circle mb-2" style="object-fit: cover; object-position: center;" width="128" height="128">
                                    <h5 class="card-title mb-0"><?= $patientData->name . " " . $patientData->last_name ?></h5>
                                    <?php
                                    $patientType = "Familiar";
                                    if (!empty($patientData->user_id)) {
                                        $patientType = $doctorFunctions->getPatientTypeByUserID($patientData->user_id);
                                    }
                                    ?>
                                    <div class="text-muted mb-2"><?= $patientType ?></div>
                                </div>
                                <hr class="my-0">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5 text-end">
                                            <span><strong>Estado Civil:</strong></span>
                                        </div>
                                        <div class="col-7 ps-0">
                                            <span><?= $patientData->civil_state ?></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-5 text-end">
                                            <span><strong>Tipo Documento:</strong></span>
                                        </div>
                                        <div class="col-7 ps-0">
                                            <span><?= (strlen($patientData->identification) == 10) ? "Cédula de ciudadanía" : ((strlen($patientData->identification) == 15) ? "RUC" : "Pasaporte") ?></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-5 text-end">
                                            <span><strong>Documento:</strong></span>
                                        </div>
                                        <div class="col-7 ps-0">
                                            <span><?= $patientData->identification ?></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-5 text-end">
                                            <span><strong>Fecha de Nacimiento:</strong></span>
                                        </div>
                                        <div class="col-7 ps-0">
                                            <span><?= date("d/m/Y", strtotime($patientData->birth_date)) ?></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-5 text-end">
                                            <span><strong>Edad:</strong></span>
                                        </div>
                                        <div class="col-7 ps-0">
                                            <span><?= date_diff(date_create($patientData->birth_date), date_create(date("Y-m-d")))->format("%y") ?></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-5 text-end">
                                            <span><strong>Domicilio:</strong></span>
                                        </div>
                                        <div class="col-7 ps-0">
                                            <span><?= $patientData->address ?></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-5 text-end">
                                            <span><strong>Correo Personal:</strong></span>
                                        </div>
                                        <div class="col-7 ps-0">
                                            <small><?= $patientData->email ?></small>
                                        </div>
                                    </div>
                                    <?php if ($patientData->access_email) { ?>
                                        <div class="row">
                                            <div class="col-5 text-end">
                                                <span><strong>Correo Acceso:</strong></span>
                                            </div>
                                            <div class="col-7 ps-0">
                                                <small><?= $patientData->access_email ?></small>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="row">
                                        <div class="col-5 text-end">
                                            <span><strong>Celular:</strong></span>
                                        </div>
                                        <div class="col-7 ps-0">
                                            <span><?= $patientData->phone ?></span>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-0">
                                <div class="card-body py-2 px-3">
                                    <button type="button" class="btn btn-primary me-1 my-1" onclick="window.open('/<?= BASE_URL ?>manage-patients', '_self')">Atrás</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-8">
                            <h1 class="h3 mb-3">Historia Clínica - <?= $patientData->identification ?></h1>

                            <?php if ($_SESSION["dep_user_area"] == 1) { ?>
                                <!-- MÓDULOS DE ENFERMERÍA -->
                                <!-- Datos de Enfermería -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                        <h5 class="card-title mb-0">Datos de Enfermería</h5>
                                        <div class="d-flex align-items-center card-action-wrap">
                                            <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_1" aria-expanded="false">
                                                <i class="mdi mdi-chevron-up"></i>
                                            </a>
                                            <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Nuevo" style="color: white;" href="/<?= BASE_URL ?>nursing-data/<?= $_GET["patient_id"] ?>">
                                                <i class="mdi mdi-24px mdi-plus-circle"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div id="collapse_1" class="collapse">
                                        <div class="card-body p-0">
                                            <div class="table-wrap">
                                                <div class="table-responsive">
                                                    <table id="tb_e_module_1" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $nursingData = $doctorFunctions->getNursingData($patientData->id, "", " order by `created_at` desc ");
                                                            while ($r = $nursingData->fetch_object()) {
                                                            ?>
                                                                <tr>
                                                                    <td><a href="/<?= BASE_URL ?>pdf/nursing-data/<?= urlencode(strrev(base64_encode($r->id))) ?>" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y H:i", strtotime($r->created_at)) ?></a></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($_SESSION["dep_user_area"] == 2) {
                                $existsMedicalHistory = false;
                                $medicalHistoryID = null;
                            ?>
                                <!-- MÓDULOS DE MEDICINA -->
                                <!-- Antecedentes -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                        <h5 class="card-title mb-0">Antecedentes</h5>
                                        <div class="d-flex align-items-center card-action-wrap">
                                            <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_1" aria-expanded="false">
                                                <i class="mdi mdi-chevron-up"></i>
                                            </a>
                                            <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Editar" style="color: white;" href="/<?= BASE_URL ?>medical-history/<?= $_GET["patient_id"] ?>">
                                                <i class="mdi mdi-24px mdi-clipboard-edit-outline"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div id="collapse_1" class="collapse">
                                        <div class="card-body p-0">
                                            <div class="table-wrap">
                                                <div class="table-responsive">
                                                    <table id="tb_m_module_1" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $medicalHistory = $doctorFunctions->getMedicalHistory($patientData->id, "", " order by `updated_at` desc ", 0, 1);
                                                            $existsMedicalHistory = ($medicalHistory->num_rows > 0) ? true : false;
                                                            while ($r = $medicalHistory->fetch_object()) {
                                                                $medicalHistoryID = $r->id;
                                                            ?>
                                                                <tr>
                                                                    <td><a href="/<?= BASE_URL ?>pdf/medical-history/<?= urlencode(strrev(base64_encode($r->id))) ?>" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y H:i", strtotime($r->updated_at)) ?></a></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Anamnesis y Examen Físico -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                        <h5 class="card-title mb-0">Anamnesis y Examen Físico</h5>
                                        <div class="d-flex align-items-center card-action-wrap">
                                            <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_2" aria-expanded="false">
                                                <i class="mdi mdi-chevron-up"></i>
                                            </a>
                                            <?php if ($existsMedicalHistory) { ?>
                                                <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Nuevo" style="color: white;" href="/<?= BASE_URL ?>medical-consultation/<?= $_GET["patient_id"] ?>">
                                                    <i class="mdi mdi-24px mdi-plus-circle"></i>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div id="collapse_2" class="collapse">
                                        <div class="card-body p-0">
                                            <div class="table-wrap">
                                                <div class="table-responsive">
                                                    <table id="tb_m_module_2" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            if ($existsMedicalHistory) {
                                                                $medicalConsultation = $doctorFunctions->getMedicalConsultation($medicalHistoryID, "", " order by `created_at` desc ");
                                                                while ($r = $medicalConsultation->fetch_object()) {
                                                            ?>
                                                                    <tr>
                                                                        <td><a href="/<?= BASE_URL ?>pdf/medical-consultation/<?= urlencode(strrev(base64_encode($r->id))) ?>" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y H:i", strtotime($r->created_at)) ?></a></td>
                                                                    </tr>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Evolución -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                        <h5 class="card-title mb-0">Evolución</h5>
                                        <div class="d-flex align-items-center card-action-wrap">
                                            <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_3" aria-expanded="false">
                                                <i class="mdi mdi-chevron-up"></i>
                                            </a>
                                            <?php if ($existsMedicalHistory) { ?>
                                                <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Nuevo" style="color: white;" href="/<?= BASE_URL ?>medical-evolve/<?= $_GET["patient_id"] ?>">
                                                    <i class="mdi mdi-24px mdi-plus-circle"></i>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div id="collapse_3" class="collapse">
                                        <div class="card-body p-0">
                                            <div class="table-wrap">
                                                <div class="table-responsive">
                                                    <table id="tb_m_module_3" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            if ($existsMedicalHistory) {
                                                                $medicalEvolve = $doctorFunctions->getMedicalEvolve($medicalHistoryID, "", " order by `date` desc, `created_at` desc ");
                                                                while ($r = $medicalEvolve->fetch_object()) {
                                                            ?>
                                                                    <tr>
                                                                        <td><a href="/<?= BASE_URL ?>pdf/medical-evolve/<?= urlencode(strrev(base64_encode($r->id))) ?>" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y", strtotime($r->date)) ?></a></td>
                                                                    </tr>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Examenes -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                        <h5 class="card-title mb-0">Exámenes</h5>
                                        <div class="d-flex align-items-center card-action-wrap">
                                            <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_4" aria-expanded="false">
                                                <i class="mdi mdi-chevron-up"></i>
                                            </a>
                                            <?php if ($existsMedicalHistory) { ?>
                                                <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Nuevo" style="color: white;" href="/<?= BASE_URL ?>medical-exam/<?= $_GET["patient_id"] ?>">
                                                    <i class="mdi mdi-24px mdi-plus-circle"></i>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div id="collapse_4" class="collapse">
                                        <div class="card-body p-0">
                                            <div class="table-wrap">
                                                <div class="table-responsive">
                                                    <table id="tb_m_module_4" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                        <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        if ($existsMedicalHistory) {
                                                            $medicalExam = $doctorFunctions->getMedicalExam2($medicalHistoryID, "", " order by `created_at` desc ");
                                                            while ($r = $medicalExam->fetch_object()) {
                                                                ?>
                                                                <tr>
                                                                    <td><a href="/<?= BASE_URL ?>pdf/medical-exam/<?= urlencode(strrev(base64_encode($r->id))) ?>" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y", strtotime($r->created_at)) ?></a></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Diagnosticos -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                        <h5 class="card-title mb-0">Diagnóstico Médico</h5>
                                        <div class="d-flex align-items-center card-action-wrap">
                                            <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_5" aria-expanded="false">
                                                <i class="mdi mdi-chevron-up"></i>
                                            </a>
                                            <?php if ($existsMedicalHistory) { ?>
                                                <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Nuevo" style="color: white;" href="/<?= BASE_URL ?>medical-diagnosis/<?= $_GET["patient_id"] ?>">
                                                    <i class="mdi mdi-24px mdi-plus-circle"></i>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div id="collapse_5" class="collapse">
                                        <div class="card-body p-0">
                                            <div class="table-wrap">
                                                <div class="table-responsive">
                                                    <table id="tb_m_module_5" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                        <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        if ($existsMedicalHistory) {
                                                            $medicalExam = $doctorFunctions->getMedicalDiagnosis($medicalHistoryID, "", " order by `created_at` desc ");
                                                            while ($r = $medicalExam->fetch_object()) {
                                                                ?>
                                                                <tr>
                                                                    <td><a href="/<?= BASE_URL ?>pdf/medical-diagnosis/<?= urlencode(strrev(base64_encode($r->id))) ?>" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y", strtotime($r->created_at)) ?></a></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if ($_SESSION["dep_user_area"] == 3) {
                                $existsDentalHistory = false;
                                $dentalHistoryID = null;
                            ?>
                                <!-- MÓDULOS DE ODONTOLOGÍA -->
                                <!-- Antecedentes -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                        <h5 class="card-title mb-0">Antecedentes</h5>
                                        <div class="d-flex align-items-center card-action-wrap">
                                            <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_1" aria-expanded="false">
                                                <i class="mdi mdi-chevron-up"></i>
                                            </a>
                                            <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Editar" style="color: white;" href="/<?= BASE_URL ?>medical-odontograma/<?= $_GET["patient_id"]?>">
                                                <i class="mdi mdi-24px mdi-clipboard-edit-outline"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div id="collapse_1" class="collapse">
                                        <div class="card-body p-0">
                                            <div class="table-wrap">
                                                <div class="table-responsive">
                                                    <table id="tb_o_module_1" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $dentalHistory = $doctorFunctions->getDentalHistory($patientData->id, "", " order by `updated_at` desc ", 0, 1);
                                                            $existsDentalHistory = ($dentalHistory->num_rows > 0) ? true : false;
                                                            while ($r = $dentalHistory->fetch_object()) {
                                                                $dentalHistoryID = $r->id;
                                                            ?>
                                                                <tr>
                                                                    <td><a href="javascript:;" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y H:i", strtotime($r->updated_at)) ?></a></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Examen Bucal -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                        <h5 class="card-title mb-0">Examen Bucal</h5>
                                        <div class="d-flex align-items-center card-action-wrap">
                                            <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_2" aria-expanded="false">
                                                <i class="mdi mdi-chevron-up"></i>
                                            </a>
                                            <?php if ($existsDentalHistory) { ?>
                                                <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Nuevo" style="color: white;" href="javascript:;">
                                                    <i class="mdi mdi-24px mdi-plus-circle"></i>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div id="collapse_2" class="collapse">
                                        <div class="card-body p-0">
                                            <div class="table-wrap">
                                                <div class="table-responsive">
                                                    <table id="tb_o_module_2" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            if ($existsDentalHistory) {
                                                                $dentalConsultation = $doctorFunctions->getDentalConsultation($dentalHistoryID, "", " order by `created_at` desc ");
                                                                while ($r = $dentalConsultation->fetch_object()) {
                                                            ?>
                                                                    <tr>
                                                                        <td><a href="javascript:;" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y H:i", strtotime($r->created_at)) ?></a></td>
                                                                    </tr>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Tratamiento -->
                                <div class="card mb-3">
                                    <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                        <h5 class="card-title mb-0">Tratamiento</h5>
                                        <div class="d-flex align-items-center card-action-wrap">
                                            <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_3" aria-expanded="false">
                                                <i class="mdi mdi-chevron-up"></i>
                                            </a>
                                            <?php if ($existsDentalHistory) { ?>
                                                <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Nuevo" style="color: white;" href="javascript:;">
                                                    <i class="mdi mdi-24px mdi-plus-circle"></i>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div id="collapse_3" class="collapse">
                                        <div class="card-body p-0">
                                            <div class="table-wrap">
                                                <div class="table-responsive">
                                                    <table id="tb_o_module_3" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            if ($existsDentalHistory) {
                                                                $dentalEvolve = $doctorFunctions->getDentalEvolve($dentalHistoryID, "", " order by `date` desc, `created_at` desc ");
                                                                while ($r = $dentalEvolve->fetch_object()) {
                                                            ?>
                                                                    <tr>
                                                                        <td><a href="javascript:;" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y", strtotime($r->date)) ?></a></td>
                                                                    </tr>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <!-- MÓDULOS PARA DOCTORES -->
                            <!-- Receta -->
                            <div class="card mb-3">
                                <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                    <h5 class="card-title mb-0">Receta</h5>
                                    <div class="d-flex align-items-center card-action-wrap">
                                        <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_recipe" aria-expanded="false">
                                            <i class="mdi mdi-chevron-up"></i>
                                        </a>
                                        <a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Nuevo" style="color: white;" href="/<?= BASE_URL ?>recipe/<?= $_GET["patient_id"] ?>">
                                            <i class="mdi mdi-24px mdi-plus-circle"></i>
                                        </a>
                                    </div>
                                </div>
                                <div id="collapse_recipe" class="collapse">
                                    <div class="card-body p-0">
                                        <div class="table-wrap">
                                            <div class="table-responsive">
                                                <table id="tb_module_recipe" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $recipeData = $doctorFunctions->getRecipeData($patientData->id, "", " order by `created_at` desc ");
                                                        while ($r = $recipeData->fetch_object()) {
                                                        ?>
                                                            <tr>
                                                                <td><a href="/<?= BASE_URL ?>pdf/recipe/<?= urlencode(strrev(base64_encode($r->id))) ?>" data-rid="<?= $r->id ?>" target='_blank'><?= date("d/m/Y H:i", strtotime($r->created_at)) ?></a></td>
                                                            </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <!-- Documentos del Paciente -->
                            <div class="card mb-3">
                                <div class="card-header card-header-action" style="padding: 12px 1.25rem;">
                                    <h5 class="card-title mb-0">Documentos</h5>
                                    <div class="d-flex align-items-center card-action-wrap">
                                        <a class="inline-block collapsed me-2" data-toggle="collapse" href="#collapse_docs" aria-expanded="false">
                                            <i class="mdi mdi-chevron-up"></i>
                                        </a>
                                        <!--<a class="inline-block badge bg-success" data-toggle="tooltip" data-placement="top" title="Nuevo" style="color: white;" href="javascript:;">
                                            <i class="mdi mdi-24px mdi-plus-circle"></i>
                                        </a>-->
                                    </div>
                                </div>
                                <div id="collapse_docs" class="collapse">
                                    <div class="card-body p-0">
                                        <div class="table-wrap">
                                            <div class="table-responsive">
                                                <table id="tb_module_docs" class="table table-striped table-hover table-bordered table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Tipo</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $patientDocs = $doctorFunctions->getPatientDocs($patientData->id, "");
                                                        while ($r = $patientDocs->fetch_object()) {
                                                        ?>
                                                            <tr>
                                                                <td><a href="<?= $r->url_doc ?>" data-rid="<?= $r->id ?>" target='_blank'><?= $r->name ?></a></td>
                                                            </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--<?= $_SESSION["dep_current_appointment_id"] ?>
                    <br />
                    <?= json_encode($patientData) ?>-->

                </div>
            </main>

            <?php
            include_once("includes/footer.php");
            ?>
        </div>
    </div>

    <?php
    include_once("includes/scripts.php");
    ?>

    <script>
        <?php if ($_SESSION["dep_user_area"] == 1) { ?>
            var dt_e_module_1 = null;
        <?php } ?>
        <?php if ($_SESSION["dep_user_area"] == 2) { ?>
            var dt_m_module_1 = null;
            var dt_m_module_2 = null;
            var dt_m_module_3 = null;
            var dt_m_module_4 = null;
            var dt_m_module_4 = null;
        <?php } ?>
        <?php if ($_SESSION["dep_user_area"] == 3) { ?>
            var dt_o_module_1 = null;
            var dt_o_module_2 = null;
            var dt_o_module_3 = null;
        <?php } ?>
        <?php if ($_SESSION["dep_user_role"] == "DR") { ?>
            var dt_module_recipe = null;
        <?php } ?>
        var dt_module_docs = null;

        $(document).ready(function() {
            $("[data-toggle=tooltip]").mouseenter(function() {
                $(this).tooltip('show');
            });

            <?php if (count($msg_response["errors"])) {
                foreach ($msg_response["errors"] as $error) {
                    echo "toastr.error('{$error}');";
                }
            }
            if (count($msg_response["success"])) {
                foreach ($msg_response["success"] as $success) {
                    echo "toastr.success('{$success}');";
                }
            } ?>

            <?php if ($_SESSION["dep_user_area"] == 1) { ?>
                //ENFERMERÍA
                var tbOptions = {
                    responsive: true,
                    autoWidth: false,
                    lengthChange: false,
                    pageLength: 5,
                    info: false,
                    searching: false,
                    columnDefs: [{
                        targets: [0],
                        orderable: false
                    }],
                    order: [],
                    language: {
                        url: "/<?= BASE_URL ?>assets/plugins/datatable-languages/es_es.lang"
                    },
                    drawCallback: function(settings) {
                        $("#tb_e_module_1 thead").remove();
                    }
                }
                var tb_e_module_1 = $('#tb_e_module_1');
                dt_e_module_1 = tb_e_module_1.DataTable(tbOptions);
            <?php } ?>
            <?php if ($_SESSION["dep_user_area"] == 2) { ?>
                //MEDICINA
                var tbOptions = {
                    responsive: true,
                    autoWidth: false,
                    lengthChange: false,
                    pageLength: 5,
                    info: false,
                    searching: false,
                    columnDefs: [{
                        targets: [0],
                        orderable: false
                    }],
                    order: [],
                    language: {
                        url: "/<?= BASE_URL ?>assets/plugins/datatable-languages/es_es.lang"
                    },
                    drawCallback: function(settings) {
                        $("#tb_m_module_1 thead").remove();
                        $("#tb_m_module_2 thead").remove();
                        $("#tb_m_module_3 thead").remove();
                        $("#tb_m_module_4 thead").remove();
                    }
                }
                var tb_m_module_1 = $('#tb_m_module_1');
                dt_m_module_1 = tb_m_module_1.DataTable(tbOptions);
                var tb_m_module_2 = $('#tb_m_module_2');
                dt_m_module_2 = tb_m_module_2.DataTable(tbOptions);
                var tb_m_module_3 = $('#tb_m_module_3');
                dt_m_module_3 = tb_m_module_3.DataTable(tbOptions);
                var tb_m_module_4 = $('#tb_m_module_4');
                dt_m_module_4 = tb_m_module_4.DataTable(tbOptions);
                var tb_m_module_5 = $('#tb_m_module_5');
                dt_m_module_5 = tb_m_module_5.DataTable(tbOptions);

            <?php } ?>
            <?php if ($_SESSION["dep_user_area"] == 3) { ?>
                //MEDICINA
                var tbOptions = {
                    responsive: true,
                    autoWidth: false,
                    lengthChange: false,
                    pageLength: 5,
                    info: false,
                    searching: false,
                    columnDefs: [{
                        targets: [0],
                        orderable: false
                    }],
                    order: [],
                    language: {
                        url: "/<?= BASE_URL ?>assets/plugins/datatable-languages/es_es.lang"
                    },
                    drawCallback: function(settings) {
                        $("#tb_o_module_1 thead").remove();
                        $("#tb_o_module_2 thead").remove();
                        $("#tb_o_module_3 thead").remove();
                    }
                }
                var tb_o_module_1 = $('#tb_o_module_1');
                dt_o_module_1 = tb_o_module_1.DataTable(tbOptions);
                var tb_o_module_2 = $('#tb_o_module_2');
                dt_o_module_2 = tb_o_module_2.DataTable(tbOptions);
                var tb_o_module_3 = $('#tb_o_module_3');
                dt_o_module_3 = tb_o_module_3.DataTable(tbOptions);
            <?php } ?>

            <?php if ($_SESSION["dep_user_role"] == "DR") { ?>
                //ENFERMERÍA
                var tbOptionsRecipe = {
                    responsive: true,
                    autoWidth: false,
                    lengthChange: false,
                    pageLength: 5,
                    info: false,
                    searching: false,
                    columnDefs: [{
                        targets: [0],
                        orderable: false
                    }],
                    order: [],
                    language: {
                        url: "/<?= BASE_URL ?>assets/plugins/datatable-languages/es_es.lang"
                    },
                    drawCallback: function(settings) {
                        $("#tb_module_recipe thead").remove();
                    }
                }
                var tb_module_recipe = $('#tb_module_recipe');
                dt_module_recipe = tb_module_recipe.DataTable(tbOptionsRecipe);
            <?php } ?>

            //DOCUMENTOS
            var tbOptionsDocs = {
                responsive: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 5,
                info: false,
                searching: false,
                columnDefs: [{
                    targets: [0],
                    orderable: false
                }],
                order: [],
                language: {
                    url: "/<?= BASE_URL ?>assets/plugins/datatable-languages/es_es.lang"
                },
                drawCallback: function(settings) {
                    $("#tb_module_docs thead").remove();
                }
            }
            var tb_module_docs = $('#tb_module_docs');
            dt_module_docs = tb_module_docs.DataTable(tbOptionsDocs);
        });
    </script>

</body>

</html>
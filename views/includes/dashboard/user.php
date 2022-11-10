<?php
require_once(__DIR__ . "/../../../system/loader.php");
require_once(__DIR__ . "/../../../data/mysql/users.functions.php");
$userFunctions = new UsersFunctions();

?>

<div class="row">
    <div class="col-12 col-sm-12 col-xxl d-flex">
        <div class="card illustration flex-fill">
            <div class="card-body p-0 d-flex flex-fill">
                <div class="row g-0 w-100">
                    <div class="col-6">
                        <div class="illustration-text p-3 m-1">
                            <h2 class="illustration-text">Bienvenido de vuelta,</h2>
                            <h4 class="illustration-text mb-0"><?= $_SESSION['dep_user_name'] . " " . $_SESSION['dep_user_lastname'] ?></h4>
                            <p class="mb-0"><?= ($_SESSION['dep_user_is_employee'] == true) ? "(Empleado)" : "(Estudiante)" ?></p>
                        </div>
                    </div>
                    <div class="col-6 align-self-end text-end">
                        <img src="/<?= BASE_URL ?>assets/dist/img/init_logo.png" alt="" class="img-fluid illustration-img">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="contenedor-p">
    <div class="panel-menu">
        <div class="row-fluid">
            <div class="row-fluid">
                <div class="span12">
                    <?php if ($_SESSION["dep_user_is_employee"] == true) { ?>
                        <div class="icon" onclick="window.location.href = '/<?= BASE_URL ?>request-familiar';">
                            <div class="iconimage">
                                <div class="pd">
                                    <img src="/<?= BASE_URL ?>assets/dist/img/opt_add_familiar.png" border="0">
                                </div>
                            </div>
                            <div class="iconname">
                                <div class="pd">
                                    <h4 class="tituloicon">Añadir familiar</h4>
                                    <span class="icondesc">Solicite el registro de un familiar</span>
                                </div>
                            </div>
                        </div>
                        <div class="icon" onclick="window.location.href = '/<?= BASE_URL ?>manage-familiar-requests';">
                            <div class="iconimage">
                                <div class="pd">
                                    <img src="/<?= BASE_URL ?>assets/dist/img/opt_manage_familiar_requests.png" border="0">
                                </div>
                            </div>
                            <div class="iconname">
                                <div class="pd">
                                    <h4 class="tituloicon">Mis familiares</h4>
                                    <span class="icondesc">Solicitudes y familiares registrados</span>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="icon" onclick="window.location.href = '/<?= BASE_URL ?>request-appointment';">
                        <div class="iconimage">
                            <div class="pd">
                                <img src="/<?= BASE_URL ?>assets/dist/img/opt_add_appointment.png" border="0">
                            </div>
                        </div>
                        <div class="iconname">
                            <div class="pd">
                                <h4 class="tituloicon">Agregar una cita</h4>
                                <span class="icondesc">Solicite una cita para usted<?= ($_SESSION["dep_user_is_employee"] == true) ? " o su familiar" : "" ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="icon" onclick="window.location.href = '/<?= BASE_URL ?>manage-appointment-requests';">
                        <div class="iconimage">
                            <div class="pd">
                                <img src="/<?= BASE_URL ?>assets/dist/img/opt_manage_appointment_requests.png" border="0">
                            </div>
                        </div>
                        <div class="iconname">
                            <div class="pd">
                                <h4 class="tituloicon">Mis citas</h4>
                                <span class="icondesc">Solicitudes y citas registradas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once(__DIR__ . "/../../../system/loader.php");
require_once(__DIR__ . "/../../../data/mysql/doctor.functions.php");
$doctorFunctions = new DoctorFunctions();

?>

<div class="row">
    <div class="col-12 col-sm-12 col-xxl d-flex">
        <div class="card illustration flex-fill">
            <div class="card-body p-0 d-flex flex-fill">
                <div class="row g-0 w-100">
                    <div class="col-6">
                        <div class="illustration-text p-3 m-1">
                            <h2 class="illustration-text">Bienvenid@ al área medíca</h2>
                        </div>
                    </div>
                    <div class="col-6 align-self-end text-end">
                        <img src="/<?= BASE_URL ?>assets/dist/img/medicicon.png" alt="" class="img-fluid illustration-img">
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
                    <div class="icon" onclick="window.location.href = '/<?= BASE_URL ?>manage-daily-records';">
                        <div class="iconimage">
                            <div class="pd">
                                <img src="/<?= BASE_URL ?>assets/dist/img/daily-task.png" border="0">
                            </div>
                        </div>
                        <div class="iconname">
                            <div class="pd">
                                <h4 class="tituloicon">Registros de pacientes</h4>
                            </div>
                        </div>
                    </div>
                    <div class="icon" onclick="window.location.href = '/<?= BASE_URL ?>create-patient';">
                        <div class="iconimage">
                            <div class="pd">
                                <img src="/<?= BASE_URL ?>assets/dist/img/medical-patient.png" border="0">
                            </div>
                        </div>
                        <div class="iconname">
                            <div class="pd">
                                <h4 class="tituloicon">Registrar paciente</h4>
                            </div>
                        </div>
                    </div>
                    <div class="icon" onclick="window.location.href = '/<?= BASE_URL ?>manage-patients';">
                        <div class="iconimage">
                            <div class="pd">
                                <img src="/<?= BASE_URL ?>assets/dist/img/medical-record.png" border="0">
                            </div>
                        </div>
                        <div class="iconname">
                            <div class="pd">
                                <h4 class="tituloicon">Ver/ Atender pacientes</h4>
                            </div>
                        </div>
                    </div>
                    <?php if (in_array($_SESSION["dep_user_area"], [2, 3])) { ?>
                    <div class="icon" onclick="window.location.href = '/<?= BASE_URL ?>create-appointment';">
                        <div class="iconimage">
                            <div class="pd">
                                <img src="/<?= BASE_URL ?>assets/dist/img/opt_add_appointmentdr.png" border="0">
                            </div>
                        </div>
                        <div class="iconname">
                            <div class="pd">
                                <h4 class="tituloicon">Registrar citas</h4>
                                <span class="icondesc">Aceptar solicitudes</span>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="icon" onclick="window.location.href = '/<?= BASE_URL ?>manage-appointments';">
                        <div class="iconimage">
                            <div class="pd">
                                <img src="/<?= BASE_URL ?>assets/dist/img/schedule.png" border="0">
                            </div>
                        </div>
                        <div class="iconname">
                            <div class="pd">
                                <h4 class="tituloicon">Ver citas</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
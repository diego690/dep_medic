<?php
if (in_array($_SESSION["dep_user_role"], ["AD", "DR"])) {
?>
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-content js-simplebar">
            <a class="sidebar-brand" href="/<?= BASE_URL ?>home" style="text-align: left;">
                <img width="24px" style="margin-right: .15rem;" src="/<?= BASE_URL ?>assets/dist/img/logo.png">

                <span class="align-middle me-3">Dpto. Médico</span>
            </a>

            <ul class="sidebar-nav">
                <li class="sidebar-header">
                    Principal
                </li>
                <li id="mnu_home" class="sidebar-item">
                    <!-- add class ==> active -->
                    <a class="sidebar-link" href="/<?= BASE_URL ?>home">
                        <i class="align-middle" data-feather="home"></i> <span class="align-middle">Inicio</span>
                    </a>
                </li>
                <?php if ($_SESSION["dep_user_role"] == "AD") { ?>
                    <li class="sidebar-header">
                        Administración
                    </li>
                    <li id="mnu_accounts" class="sidebar-item">
                        <a data-bs-target="#mnugrp_accounts" data-bs-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle" data-feather="users"></i> <span class="align-middle">Cuentas</span>
                        </a>
                        <ul id="mnugrp_accounts" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li id="mnu_accounts_create" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>create-doctor">Registrar Doctor</a></li> <!-- add class ==> active -->
                            <li id="mnu_accounts_manage" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>manage-doctors">Ver Doctores</a></li>
                            <li id="mnu_accounts_employees_manage" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>manage-employees">Ver Empleados</a></li>
                            <li id="mnu_accounts_students_manage" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>manage-students">Ver Estudiantes</a></li>
                        </ul>
                    </li>
                    <li id="mnu_faculties" class="sidebar-item">
                        <a data-bs-target="#mnugrp_faculties" data-bs-toggle="collapse" class="sidebar-link collapsed">
                            <i style="width: 18px;" class="align-middle fas fa-building text-center"></i> <span class="align-middle">Facultades</span>
                        </a>
                        <ul id="mnugrp_faculties" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li id="mnu_faculties_create" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>create-faculty">Registrar Facultad</a></li>
                            <li id="mnu_faculties_manage" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>manage-faculties">Ver Facultades</a></li>
                            <li id="mnu_careers" class="sidebar-item">
                                <a data-bs-target="#mnugrp_careers" data-bs-toggle="collapse" class="sidebar-link collapsed">
                                    <span class="align-middle">Carreras</span>
                                </a>
                                <ul id="mnugrp_careers" class="sidebar-dropdown list-unstyled collapse">
                                    <li id="mnu_careers_create" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>create-career">Registrar Carrera</a></li>
                                    <li id="mnu_careers_manage" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>manage-careers">Ver Carreras</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li id="mnu_occupations" class="sidebar-item">
                        <a data-bs-target="#mnugrp_occupations" data-bs-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle" data-feather="briefcase"></i> <span class="align-middle">Ocupaciones</span>
                        </a>
                        <ul id="mnugrp_occupations" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li id="mnu_occupations_create" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>create-occupation">Registrar Ocupación</a></li>
                            <li id="mnu_occupations_manage" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>manage-occupations">Ver Ocupaciones</a></li>
                        </ul>
                    </li>
                    <li id="mnu_audit" class="sidebar-item">
                        <a data-bs-target="#mnugrp_audit" data-bs-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle" data-feather="search"></i> <span class="align-middle">Auditoría</span>
                        </a>
                        <ul id="mnugrp_audit" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li id="mnu_audit_products" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>audit-products">Productos</a></li>
                        </ul>
                    </li>
                <?php } ?>
                <?php if ($_SESSION["dep_user_role"] == "DR") { ?>
                    <li class="sidebar-header">
                        Administración
                    </li>
                    <li id="mnu_patients" class="sidebar-item">
                        <a data-bs-target="#mnugrp_patients" data-bs-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle" data-feather="users"></i> <span class="align-middle">Pacientes</span>
                        </a>
                        <ul id="mnugrp_patients" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li id="mnu_patients_create" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>create-patient">Registrar Paciente</a></li>
                            <li id="mnu_patients_manage" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>manage-patients">Ver Pacientes</a></li>
                        </ul>
                    </li>
                    <li id="mnu_appointments" class="sidebar-item">
                        <a data-bs-target="#mnugrp_appointments" data-bs-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle" data-feather="clock"></i> <span class="align-middle">Citas</span>
                        </a>
                        <ul id="mnugrp_appointments" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <?php if (in_array($_SESSION["dep_user_area"], [2, 3])) { ?>
                                <li id="mnu_appointments_create" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>create-appointment">Registrar Cita</a></li>
                            <?php } ?>
                            <li id="mnu_appointments_manage" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>manage-appointments">Ver Citas</a></li>
                        </ul>
                    </li>
                    <li id="mnu_products" class="sidebar-item">
                        <a data-bs-target="#mnugrp_products" data-bs-toggle="collapse" class="sidebar-link collapsed">
                            <i class="align-middle" data-feather="archive"></i> <span class="align-middle">Botiquín</span>
                        </a>
                        <ul id="mnugrp_products" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <?php if ($_SESSION["dep_user_area"] == 1) { ?>
                                <li id="mnu_products_create" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>create-product">Registrar Producto</a></li>
                            <?php } ?>
                            <li id="mnu_products_manage" class="sidebar-item"><a class="sidebar-link" href="/<?= BASE_URL ?>manage-products">Ver Productos</a></li>
                        </ul>
                    </li>
                    <?php if (in_array($_SESSION["dep_user_area"], [2, 3])) { ?>
                        <li id="mnu_settings" class="sidebar-item">
                            <a class="sidebar-link" href="/<?= BASE_URL ?>settings">
                                <i class="align-middle" data-feather="settings"></i> <span class="align-middle">Configuración</span>
                            </a>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
            <div class="sidebar-cta">
                <div class="sidebar-cta-content text-center" style="background: #0C5D11;">
                    <strong class="d-inline-block mb-2">VADEMECUM</strong>
                    <div class="mb-2">
                        <img src="/<?= BASE_URL ?>assets/dist/img/vademecum_logo.png">
                    </div>
                    <div class="mb-3 text-sm" style="font-size: 10px;">
                        Su fuente de conocimiento farmacológico
                    </div>

                    <div class="d-grid">
                        <a href="https://www.vademecum.es/" class="btn btn-danger" target="_blank">Acceder</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
<?php
}
?>
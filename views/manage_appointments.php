<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

$doctorFunctions = new DoctorFunctions();
if ($_SESSION["dep_user_role"] != "DR" || !in_array($_SESSION["dep_user_area"], [1, 2, 3])) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$area = $doctorFunctions->getMyArea();
if (!$area) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$_SESSION["dep_current_appointment_id"] = null;

$msg_response = array(
    "errors" => array(),
    "success" => array()
);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Ver Citas</title>

    <?php
    include_once("includes/styles.php");
    ?>

</head>

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

                    <h1 class="h3 mb-3">Citas</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <!--<div class="card-header">
                                    <h5 class="card-title mb-0">Listado de las facultades</h5>
                                </div>-->
                                <div class="card-body">
                                    <table id="tb_appointments" class="table table-striped table-hover table-bordered table-sm">
                                        <!-- table-sm -->
                                        <thead>
                                            <tr>
                                                <th>Paciente</th>
                                                <th>Fecha</th>
                                                <th class="text-center">Tipo</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

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
        var dt_appointments = null;

        function attendAppointment(appointmentID, patientID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "save_appointment_to_session",
                    appointment_id: appointmentID
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        window.open("/<?= BASE_URL ?>patient-attention/"+patientID, "_self");
                    }
                }
            });
        }

        $(document).ready(function() {
            $("#mnu_appointments").addClass("active");
            $("#mnu_appointments a:first").removeClass("collapsed");
            $("#mnugrp_appointments").addClass("show");
            $("#mnu_appointments_manage").addClass("active");
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

            var tbOptions = {
                responsive: true,
                autoWidth: false,
                searching: false,
                lengthChange: false,
                info: false,
                ordering: false,
                processing: true,
                serverSide: true,
                serverMethod: 'post',
                ajax: {
                    url: '/<?= BASE_URL ?>ajax.php',
                    data: {
                        action: 'get_appointments',
                    }
                },
                columns: [{
                        data: "patient",
                        orderable: false
                    },
                    {
                        data: "date",
                        orderable: false
                    },
                    {
                        data: "type",
                        orderable: false
                    },
                    {
                        data: "column_actions",
                        orderable: false
                    }
                ],
                order: [],
                language: {
                    url: "/<?= BASE_URL ?>assets/plugins/datatable-languages/es_es.lang"
                },
                drawCallback: function(settings) {
                    feather.replace();
                    $("[data-toggle=tooltip]").mouseenter(function() {
                        $(this).tooltip('show');
                    });
                }
            }
            var tb_appointments = $('#tb_appointments');
            dt_appointments = tb_appointments.DataTable(tbOptions);

            setInterval(function() {
                if (dt_appointments != null) {
                    dt_appointments.draw();
                }
            }, 15000);
        });
    </script>

</body>

</html>
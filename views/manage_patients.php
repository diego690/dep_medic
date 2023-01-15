<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");

if ($_SESSION["dep_user_role"] != "DR") {
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

    <title><?= ADMIN_PANEL_NAME ?> - Ver Pacientes</title>

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

                <h1 class="h3 mb-3">Ver Pacientes</h1>

                <div class="row">
                    <?php if ($_SESSION["dep_user_area"] == 1) { ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Solicitudes de familiares</h5>
                                </div>
                                <div class="card-body">
                                    <table id="tb_requests" class="table table-striped table-hover table-bordered table-sm">
                                        <!-- table-striped table-sm -->
                                        <thead>
                                        <tr>
                                            <th>Cédula / Pasaporte</th>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Parentesco</th>
                                            <th>Empleado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Listado de los pacientes</h5>
                            </div>
                            <div class="card-body">
                                <table id="tb_patients" class="table table-striped table-hover table-bordered table-sm">
                                    <!-- table-striped table-sm -->
                                    <thead>
                                    <tr>
                                        <th>Cédula / Pasaporte</th>
                                        <th>Nombres</th>
                                        <th>Apellidos</th>
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
include_once("modals/manage_patients.php");
include_once("includes/scripts.php");
?>

<script>
    var dt_requests = null,
        dt_patients = null;

    function openModalAccept(data) {
        $('#modalConfirmAcceptDo').attr('data-value', data);
        $('#modalConfirmAccept').modal("show");
    }

    function openModalDecline(data) {
        $('#modalConfirmDeclineDo').attr('data-value', data);
        $('#modalConfirmDecline').modal("show");
    }

    function openModalViewRequest(data) {
        data = JSON.parse(atob(decodeURIComponent(data).split('').reverse().join('')));
        //set data
        $('#modalConfirmViewRequest .mcvr_created_at').text(data.created_at);
        $('#modalConfirmViewRequest .mcvr_identification').text(data.identification);
        $('#modalConfirmViewRequest .mcvr_kin').text(data.kin);
        $('#modalConfirmViewRequest .mcvr_fullname').text(data.fullname);
        $('#modalConfirmViewRequest .mcvr_civil_state').text(data.civil_state);
        $('#modalConfirmViewRequest .mcvr_birth_date').text(data.birth_date);
        $('#modalConfirmViewRequest .mcvr_phone').text((data.phone != null && data.phone.length > 0) ? data.phone : "---");
        $('#modalConfirmViewRequest .mcvr_email').text((data.email != null && data.email.length > 0) ? data.email : "---");
        $('#modalConfirmViewRequest .mcvr_address').text(data.address);
        $('#modalConfirmViewRequest .mcvr_backup_doc').empty();
        if (data.backup_doc != null) {
            $('#modalConfirmViewRequest .mcvr_backup_doc').append(`
                    <iframe src="` + data.backup_doc + `" style="width:100%; height:500px;" frameborder="0"></iframe>
                `);
        } else {
            $('#modalConfirmViewRequest .mcvr_backup_doc').append(`
                    <center><code>La solicitud se basa en un paciente ya registrado</code></center>
                `);
        }
        $('#modalConfirmViewRequest').modal("show");
    }

    function acceptRequest(requestID) {
        $.ajax({
            url: '/<?= BASE_URL ?>ajax.php',
            method: 'POST',
            data: {
                action: "accept_familiar_request",
                request_id: requestID
            },
            success: function(response) {
                response = JSON.parse(response);
                if (response.status == 500) {
                    toastr.error(response.error);
                } else {
                    toastr.success(response.data.msg);
                    if (dt_requests != null) {
                        dt_requests.draw();
                    }
                    if (dt_patients != null) {
                        dt_patients.draw();
                    }
                }
            }
        });
    }

    function declineRequest(requestID) {
        $.ajax({
            url: '/<?= BASE_URL ?>ajax.php',
            method: 'POST',
            data: {
                action: "decline_familiar_request",
                request_id: requestID
            },
            success: function(response) {
                response = JSON.parse(response);
                if (response.status == 500) {
                    toastr.error(response.error);
                } else {
                    toastr.success(response.data.msg);
                    if (dt_requests != null) {
                        dt_requests.draw();
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        $("#mnu_patients").addClass("active");
        $("#mnu_patients a:first").removeClass("collapsed");
        $("#mnugrp_patients").addClass("show");
        $("#mnu_patients_manage").addClass("active");
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

        var tbOptions1 = {
            responsive: true,
            autoWidth: false,
            dom: "frtp",
            lengthChange: false,
            info: false,
            processing: true,
            serverSide: true,
            serverMethod: 'post',
            ajax: {
                url: '/<?= BASE_URL ?>ajax.php',
                data: {
                    action: 'get_familiar_requests',
                }
            },
            columns: [{
                data: "identification"
            },
                {
                    data: "name"
                },
                {
                    data: "last_name"
                },
                {
                    data: "kin"
                },
                {
                    data: "employee"
                },
                {
                    data: "column_actions",
                    orderable: true
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
        var tb_requests = $('#tb_requests');
        dt_requests = tb_requests.DataTable(tbOptions1);
        var tbOptions2 = {
            responsive: true,
            autoWidth: false,
            dom: "frtp",
            lengthChange: false,
            info: false,
            processing: true,
            serverSide: true,
            serverMethod: 'post',
            ajax: {
                url: '/<?= BASE_URL ?>ajax.php',
                data: {
                    action: 'get_patients',
                }
            },
            columns: [{
                data: "identification"
            },
                {
                    data: "name"
                },
                {
                    data: "last_name"
                },
                {
                    data: "column_actions",
                    orderable: true
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
        var tb_patients = $('#tb_patients');
        dt_patients = tb_patients.DataTable(tbOptions2);
    });
</script>

</body>

</html>
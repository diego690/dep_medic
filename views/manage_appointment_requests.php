<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");

if ($_SESSION["dep_user_role"] != "US") {
    header("Location: /" . BASE_URL . "home");
    exit();
}

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

    <title><?= ADMIN_PANEL_NAME ?> - Mis Citas</title>

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

                    <nav aria-label="breadcrumb" style="float: right;">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/<?= BASE_URL ?>home">Inicio</a></li>
                            <li class="breadcrumb-item active">Mis Citas</li>
                        </ol>
                    </nav>

                    <h1 class="h3 mb-3">Mis Citas</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado de mis citas</h5>
                                </div>
                                <div class="card-body">
                                    <table id="tb_appointments" class="table table-striped table-hover table-bordered table-sm">
                                        <!-- table-striped table-sm -->
                                        <thead>
                                            <tr>
                                                <th>Paciente</th>
                                                <th>Área</th>
                                                <th>Tipo</th>
                                                <th>Fecha</th>
                                                <th>Hora</th>
                                                <th>Estado</th>
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
    include_once("modals/manage_appointment_requests.php");
    include_once("includes/scripts.php");
    ?>

    <script>
        var dt_appointments = null;

        function openModalDelete(data) {
            $('#modalConfirmDeleteDo').attr('data-value', data);
            $('#modalConfirmDelete').modal("show");
        }

        function openModalViewRequest(data) {
            data = JSON.parse(atob(decodeURIComponent(data).split('').reverse().join('')));
            //set data
            $('#modalConfirmViewRequest .mcvr_created_at').text(data.created_at);
            $('#modalConfirmViewRequest .mcvr_identification').text(data.identification);
            $('#modalConfirmViewRequest .mcvr_fullname').text(data.fullname);
            $('#modalConfirmViewRequest .mcvr_area').text(data.full_area);
            $('#modalConfirmViewRequest .mcvr_type').text(data.type);
            $('#modalConfirmViewRequest .mcvr_date').text(data.date);
            $('#modalConfirmViewRequest .mcvr_time').text(data.time);
            $('#modalConfirmViewRequest .mcvr_description').text(data.description);
            $('#modalConfirmViewRequest').modal("show");
        }

        function deleteRequest(requestID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "delete_appointment_request",
                    request_id: requestID
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        toastr.success(response.data.msg);
                        if (dt_appointments != null) {
                            dt_appointments.draw();
                        }
                    }
                }
            });
        }

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

            var tbOptions = {
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
                        action: 'get_my_appointment_requests',
                    }
                },
                columns: [{
                        data: "fullname"
                    },
                    {
                        data: "area"
                    },
                    {
                        data: "type",
                        render: function(data, type, row, meta) {
                            if (data == "P") {
                                return "Presencial";
                            } else {
                                return "Telemedicina";
                            }
                        }
                    },
                    {
                        data: "date"
                    },
                    {
                        data: "time"
                    },
                    {
                        data: "status",
                        render: function(data, type, row, meta) {
                            if (data == "CR") {
                                return `<center><span class="badge bg-warning">En espera...</span></center>`;
                            } else {
                                return `<center><span class="badge bg-success">Confirmado</span></center>`;
                            }
                        }
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
        });
    </script>

</body>

</html>
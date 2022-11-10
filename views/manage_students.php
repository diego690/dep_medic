<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");

if ($_SESSION["dep_user_role"] != "AD") {
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

    <title><?= ADMIN_PANEL_NAME ?> - Ver Estudiantes</title>

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

                    <h1 class="h3 mb-3">Ver Estudiantes</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado de los estudiantes</h5>
                                </div>
                                <div class="card-body">
                                    <table id="tb_students" class="table table-striped table-hover table-bordered table-sm">
                                        <!-- table-striped table-sm -->
                                        <thead>
                                            <tr>
                                                <th>Email</th>
                                                <th>Nombres</th>
                                                <th>Apellidos</th>
                                                <th>Carrera</th>
                                                <th>Semestre</th>
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
    include_once("modals/users.php");
    include_once("includes/scripts.php");
    ?>

    <script>
        var dt_students = null;

        function openModalActivate(data) {
            $('#modalConfirmActivateDo').attr('data-value', data);
            $('#modalConfirmActivate').modal("show");
        }

        function openModalDeactivate(data) {
            $('#modalConfirmDeactivateDo').attr('data-value', data);
            $('#modalConfirmDeactivate').modal("show");
        }

        function activateUser(userID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "activate_user",
                    user_id: userID
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        toastr.success(response.data.msg);
                        if (dt_students != null) {
                            dt_students.draw();
                        }
                    }
                }
            });
        }

        function deactivateUser(userID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "deactivate_user",
                    user_id: userID
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        toastr.success(response.data.msg);
                        if (dt_students != null) {
                            dt_students.draw();
                        }
                    }
                }
            });
        }

        $(document).ready(function() {
            $("#mnu_accounts").addClass("active");
            $("#mnu_accounts a:first").removeClass("collapsed");
            $("#mnugrp_accounts").addClass("show");
            $("#mnu_accounts_students_manage").addClass("active");
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
                processing: true,
                serverSide: true,
                serverMethod: 'post',
                ajax: {
                    url: '/<?= BASE_URL ?>ajax.php',
                    data: {
                        action: 'get_students',
                    }
                },
                columns: [{
                        data: "email"
                    },
                    {
                        data: "name"
                    },
                    {
                        data: "last_name"
                    },
                    {
                        data: "career"
                    },
                    {
                        data: "semester"
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
                },
                createdRow: function(row, data, dataIndex) {
                    if ($(row).find("a[title='Habilitar cuenta']").length > 0) {
                        $(row).addClass('table-danger');
                    }
                }
            }
            var tb_students = $('#tb_students');
            dt_students = tb_students.DataTable(tbOptions);
        });
    </script>

</body>

</html>
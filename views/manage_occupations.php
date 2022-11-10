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

    <title><?= ADMIN_PANEL_NAME ?> - Ver Ocupaciones</title>

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

                    <h1 class="h3 mb-3">Ver Ocupaciones</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado de las ocupaciones</h5>
                                </div>
                                <div class="card-body">
                                    <table id="tb_occupations" class="table table-striped table-hover table-bordered table-sm">
                                        <!-- table-sm -->
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
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
    include_once("modals/occupations.php");
    include_once("includes/scripts.php");
    ?>

    <script>
        var dt_occupations = null;

        function openModalDelete(data) {
            $('#modalConfirmDeleteDo').attr('data-value', data);
            $('#modalConfirmDelete').modal("show");
        }

        function deleteOccupation(occupationID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "delete_occupation",
                    occupation_id: occupationID
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        toastr.success(response.data.msg);
                        if (dt_occupations != null) {
                            dt_occupations.draw();
                        }
                    }
                }
            });
        }

        $(document).ready(function() {
            $("#mnu_occupations").addClass("active");
            $("#mnu_occupations a:first").removeClass("collapsed");
            $("#mnugrp_occupations").addClass("show");
            $("#mnu_occupations_manage").addClass("active");
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
                        action: 'get_occupations',
                    }
                },
                columns: [{
                        data: "name"
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
            var tb_occupations = $('#tb_occupations');
            dt_occupations = tb_occupations.DataTable(tbOptions);
        });
    </script>

</body>

</html>
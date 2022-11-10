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

    <title><?= ADMIN_PANEL_NAME ?> - Auditoría de Productos</title>

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

                    <h1 class="h3 mb-3">Auditoría de Productos</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado</h5>
                                </div>
                                <div class="card-body">
                                    <table id="tb_audit" class="table table-striped table-hover table-bordered table-sm">
                                        <!-- table-sm -->
                                        <thead>
                                            <tr>
                                                <th>Registro</th>
                                                <th>Fecha</th>
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
    include_once("modals/audit_products.php");
    include_once("includes/scripts.php");
    ?>

    <script>
        var dt_audit = null;

        function openModalViewDetails(product, data) {
            try {
                data = JSON.parse(atob(data));
                $('#modalConfirmViewDetails #audit_container').empty();
                $('#modalConfirmViewDetailsLabel').text(atob(product));
                if (data.before == null) { //creación
                    $('#modalConfirmViewDetails #audit_container').append(`
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <center><h5>DATOS</h5></center>
                                <div class="p-2" style="border: 2px solid green;">
                                    <div class="mb-3 form-group">
                                        <center><label class="form-label">Imagen</label></center>
                                        <center><img width="128" height="128" alt="Producto" class="img-responsive" src="` + ((data.after.image != "") ? data.after.image : "/<?= BASE_URL ?>assets/dist/img/no_image.png") + `" onerror="src='/<?= BASE_URL ?>assets/dist/img/no_image.png'"></img></center>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Nombre</label>
                                        <span class="form-control">` + data.after.name + `</span>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Unidades</label>
                                        <span class="form-control">` + data.after.units + `</span>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Descripción</label>
                                        <span class="form-control">` + data.after.description + `</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                    `);
                } else if (data.after == null) { //eliminación
                    $('#modalConfirmViewDetails #audit_container').append(`
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <center><h5>DATOS</h5></center>
                                <div class="p-2" style="border: 2px solid red;">
                                    <div class="mb-3 form-group">
                                        <center><label class="form-label">Imagen</label></center>
                                        <center><img width="128" height="128" alt="Producto" class="img-responsive" src="` + ((data.before.image != "") ? data.before.image : "/<?= BASE_URL ?>assets/dist/img/no_image.png") + `" onerror="src='/<?= BASE_URL ?>assets/dist/img/no_image.png'"></img></center>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Nombre</label>
                                        <span class="form-control">` + data.before.name + `</span>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Unidades</label>
                                        <span class="form-control">` + data.before.units + `</span>
                                    </div>
                                    <div class="mb-3 form-group">
                                        <label class="form-label">Descripción</label>
                                        <span class="form-control">` + data.before.description + `</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                    `);
                } else { //otros procesos
                    $('#modalConfirmViewDetails #audit_container').append(`
                        <div class="row">
                            <div class="col-md-6">
                                <center><h5>ANTES</h5></center>
                                <div id="before_container" class="p-2" style="border: 2px solid red;"></div>
                            </div>
                            <div class="col-md-6">
                                <center><h5>DESPUÉS</h5></center>
                                <div id="after_container" class="p-2" style="border: 2px solid green;"></div>
                            </div>
                        </div>
                    `);
                    if (data.before.image != data.after.image) {
                        $('#modalConfirmViewDetails #before_container').append(`
                            <div class="mb-3 form-group">
                                <center><label class="form-label">Imagen</label></center>
                                <center><img width="128" height="128" alt="Producto" class="img-responsive" src="` + ((data.before.image != "") ? data.before.image : "/<?= BASE_URL ?>assets/dist/img/no_image.png") + `" onerror="src='/<?= BASE_URL ?>assets/dist/img/no_image.png'"></img></center>
                            </div>
                        `);
                        $('#modalConfirmViewDetails #after_container').append(`
                            <div class="mb-3 form-group">
                                <center><label class="form-label">Imagen</label></center>
                                <center><img width="128" height="128" alt="Producto" class="img-responsive" src="` + ((data.after.image != "") ? data.after.image : "/<?= BASE_URL ?>assets/dist/img/no_image.png") + `" onerror="src='/<?= BASE_URL ?>assets/dist/img/no_image.png'"></img></center>
                            </div>
                        `);
                    }
                    if (data.before.name != data.after.name) {
                        $('#modalConfirmViewDetails #before_container').append(`
                            <div class="mb-3 form-group">
                                <label class="form-label">Nombre</label>
                                <span class="form-control">` + data.before.name + `</span>
                            </div>
                        `);
                        $('#modalConfirmViewDetails #after_container').append(`
                            <div class="mb-3 form-group">
                                <label class="form-label">Nombre</label>
                                <span class="form-control">` + data.after.name + `</span>
                            </div>
                        `);
                    }
                    if (data.before.units != data.after.units) {
                        $('#modalConfirmViewDetails #before_container').append(`
                            <div class="mb-3 form-group">
                                <label class="form-label">Unidades</label>
                                <span class="form-control">` + data.before.units + `</span>
                            </div>
                        `);
                        $('#modalConfirmViewDetails #after_container').append(`
                            <div class="mb-3 form-group">
                                <label class="form-label">Unidades</label>
                                <span class="form-control">` + data.after.units + `</span>
                            </div>
                        `);
                    }
                    if (data.before.description != data.after.description) {
                        $('#modalConfirmViewDetails #before_container').append(`
                            <div class="mb-3 form-group">
                                <label class="form-label">Descripción</label>
                                <span class="form-control">` + data.before.description + `</span>
                            </div>
                        `);
                        $('#modalConfirmViewDetails #after_container').append(`
                            <div class="mb-3 form-group">
                                <label class="form-label">Descripción</label>
                                <span class="form-control">` + data.after.description + `</span>
                            </div>
                        `);
                    }
                }
                $('#modalConfirmViewDetails').modal("show");
            } catch (error) {
                toastr.error("Ha sucedido un error intentando abrir el detalle del registro: " + error);
            }
            $('#modalConfirmViewDetails #before_container').append(``);
        }

        $(document).ready(function() {
            $("#mnu_audit").addClass("active");
            $("#mnu_audit a:first").removeClass("collapsed");
            $("#mnugrp_audit").addClass("show");
            $("#mnu_audit_products").addClass("active");
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
                        action: 'get_audit_products',
                    }
                },
                columns: [{
                        data: "user",
                        orderable: false,
                        render: function(data, type, row, meta) {
                            let ac = "ha modificado";
                            switch (row["action"]) {
                                case "create":
                                    ac = "<span class='badge rounded-pill bg-success'>ha creado</span>";
                                    break;
                                case "edit":
                                    ac = "<span class='badge rounded-pill bg-primary'>ha editado</span>";
                                    break;
                                case "delete":
                                    ac = "<span class='badge rounded-pill bg-danger'>ha eliminado</span>";
                                    break;
                                case "increase_stock":
                                    ac = "<span class='badge rounded-pill bg-secondary'>ha aumentado el stock de</span>";
                                    break;
                            }
                            return "<strong data-toggle='tooltip' data-placement='top' title='"+row["email"]+"'>"+data+"</strong> "+ac+" <strong>"+row["product"]+"</strong>";
                        }
                    },
                    {
                        data: "datetime"
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
            var tb_audit = $('#tb_audit');
            dt_audit = tb_audit.DataTable(tbOptions);
        });
    </script>

</body>

</html>
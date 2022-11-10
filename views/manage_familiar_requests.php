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

    <title><?= ADMIN_PANEL_NAME ?> - Mis Familiares</title>

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
                            <li class="breadcrumb-item active">Mis Familiares</li>
                        </ol>
                    </nav>

                    <h1 class="h3 mb-3">Mis Familiares</h1>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado de mis familiares</h5>
                                </div>
                                <div class="card-body">
                                    <table id="tb_familiars" class="table table-striped table-hover table-bordered table-sm">
                                        <!-- table-striped table-sm -->
                                        <thead>
                                            <tr>
                                                <th>Cédula / Pasaporte</th>
                                                <th>Nombres</th>
                                                <th>Apellidos</th>
                                                <th>Parentesco</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado de mis solicitudes</h5>
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
    include_once("modals/manage_familiar_requests.php");
    include_once("includes/scripts.php");
    ?>

    <script>
        var dt_familiars = null,
            dt_requests = null;

        function openModalDelete(data) {
            $('#modalConfirmDeleteDo').attr('data-value', data);
            $('#modalConfirmDelete').modal("show");
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
            }
            $('#modalConfirmViewRequest').modal("show");
        }

        function openModalViewMyFamiliar(data) {
            data = JSON.parse(atob(decodeURIComponent(data).split('').reverse().join('')));
            //set data
            $('#modalConfirmViewMyFamiliar .mcvmf_identification').text(data.identification);
            $('#modalConfirmViewMyFamiliar .mcvmf_kin').text(data.kin);
            $('#modalConfirmViewMyFamiliar .mcvmf_fullname').text(data.fullname);
            $('#modalConfirmViewMyFamiliar .mcvmf_civil_state').text(data.civil_state);
            $('#modalConfirmViewMyFamiliar .mcvmf_birth_date').text(data.birth_date);
            $('#modalConfirmViewMyFamiliar .mcvmf_phone').text((data.phone != null && data.phone.length > 0) ? data.phone : "---");
            $('#modalConfirmViewMyFamiliar .mcvmf_email').text((data.email != null && data.email.length > 0) ? data.email : "---");
            $('#modalConfirmViewMyFamiliar .mcvmf_address').text(data.address);
            $('#modalConfirmViewMyFamiliar').modal("show");
        }

        function deleteRequest(requestID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "delete_familiar_request",
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
                        action: 'get_my_familiars',
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
            var tb_familiars = $('#tb_familiars');
            dt_familiars = tb_familiars.DataTable(tbOptions1);
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
                        action: 'get_my_familiar_requests',
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
            var tb_requests = $('#tb_requests');
            dt_requests = tb_requests.DataTable(tbOptions2);
        });
    </script>

</body>

</html>
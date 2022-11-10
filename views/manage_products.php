<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");

if ($_SESSION["dep_user_role"] != "DR") {
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

    <title><?= ADMIN_PANEL_NAME ?> - Ver Productos</title>

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

                    <h1 class="h3 mb-3">Ver Productos</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado de los productos</h5>
                                </div>
                                <div class="card-body">
                                    <table id="tb_products" class="table table-striped table-hover table-bordered table-sm">
                                        <!-- table-sm -->
                                        <thead>
                                            <tr>
                                                <th class="text-center">Imagen</th>
                                                <th>Nombre</th>
                                                <th class="text-center">Unidades</th>
                                                <?php if ($_SESSION["dep_user_area"] == 1) { ?>
                                                    <th class="text-center">Acciones</th>
                                                <?php } ?>
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
    include_once("modals/manage_products.php");
    include_once("includes/scripts.php");
    ?>

    <script>
        var dt_products = null;

        function showProductDescription(productName, productDesc) {
            Swal.fire({
                title: atob(productName),
                html: atob(productDesc),
                icon: 'info',
                confirmButtonText: 'Ok'
            });
        }

        function openModalDelete(data) {
            $('#modalConfirmDeleteDo').attr('data-value', data);
            $('#modalConfirmDelete').modal("show");
        }

        function deleteProduct(productID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "delete_product",
                    product_id: productID
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        toastr.success(response.data.msg);
                        if (dt_products != null) {
                            dt_products.draw();
                        }
                    }
                }
            });
        }

        function openModalIncreaseStock(data) {
            $("#txt_units").val("1");
            $("#txt_stock").val("1");
            $('#modalConfirmIncreaseStockDo').attr('data-value', data);
            $('#modalConfirmIncreaseStock').modal("show");
        }

        function increaseStock(productID) {
            if ($("#stock_form").valid()) {
                $.ajax({
                    url: '/<?= BASE_URL ?>ajax.php',
                    method: 'POST',
                    data: {
                        action: "increase_stock",
                        product_id: productID,
                        units: (parseInt($("#txt_stock").val()) * parseInt($("#txt_units").val()))
                    },
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.status == 500) {
                            toastr.error(response.error);
                        } else {
                            toastr.success(response.data.msg);
                            $('#modalConfirmIncreaseStock').modal("hide");
                            if (dt_products != null) {
                                dt_products.draw();
                            }
                        }
                    }
                });
            }
        }

        $(document).ready(function() {
            $("#mnu_products").addClass("active");
            $("#mnu_products a:first").removeClass("collapsed");
            $("#mnugrp_products").addClass("show");
            $("#mnu_products_manage").addClass("active");
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
                        action: 'get_products',
                    }
                },
                columns: [{
                        data: "image",
                        orderable: false
                    },
                    {
                        data: "name",
                        render: function(data, type, row, meta) {
                            return "<strong>"+data+"</strong>&nbsp;&nbsp;<i class='fa fa-info-circle' data-toggle='tooltip' data-placement='top' title='Ver descripción' style='cursor: pointer;' onclick=\"showProductDescription('"+btoa(row["name"])+"','"+row["description"]+"')\"></i>";
                        }
                    },
                    {
                        data: "units",
                        render: function(data, type, row, meta) {
                            return "<center>"+data+"</center>";
                        }
                    },
                    <?php if ($_SESSION["dep_user_area"] == 1) { ?> {
                            data: "column_actions",
                            orderable: false
                        }
                    <?php } ?>
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
            var tb_products = $('#tb_products');
            dt_products = tb_products.DataTable(tbOptions);

            $('#stock_form').validate({
                rules: {
                    txt_units: {
                        required: true
                    },
                    txt_stock: {
                        required: true
                    }
                },
                messages: {
                    txt_units: {
                        required: "Este campo es requerido"
                    },
                    txt_stock: {
                        required: "Este campo es requerido"
                    }
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>

</body>

</html>
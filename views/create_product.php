<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

$doctorFunctions = new DoctorFunctions();
if ($_SESSION["dep_user_role"] != "DR" || $_SESSION["dep_user_area"] != 1) {
    header("Location: /" . BASE_URL . "home");
    exit();
}

$msg_response = array(
    "errors" => array(),
    "success" => array()
);

//Validate to avoid POST duplicates
$post_validation_session = (isset($_SESSION['post_id'])) ? $_SESSION['post_id'] : "";
$post_validation_form = (isset($_POST['post_id'])) ? $_POST['post_id'] : "";
$is_post = (count($_POST) > 0) && ($post_validation_session != $post_validation_form);

if ($is_post) {
    $_SESSION['post_id'] = $_POST['post_id'];

    if (!$doctorFunctions->existsProduct(trim($_POST["txt_name"]))) {
        $correctFile = false;
        if ((!isset($_FILES["file_image"]) || $_FILES["file_image"]["name"] == "") || (isset($_FILES["file_image"]) && $_FILES["file_image"]["size"] <= 500000 && $_FILES["file_image"]["type"] == "image/jpeg")) {
            $correctFile = true;
        }
        if ($correctFile) {
            $product_image = "";
            if (isset($_FILES["file_image"])) {
                $directory = "../media/products/";
                $now = getCurrentTimestamp();
                $dateArray = array(
                    "year" => explode("-", explode(" ", $now)[0])[0],
                    "month" => explode("-", explode(" ", $now)[0])[1],
                    "day" => explode("-", explode(" ", $now)[0])[2],
                    "hour" => explode(":", explode(" ", $now)[1])[0],
                    "minutes" => explode(":", explode(" ", $now)[1])[1],
                    "seconds" => explode(":", explode(" ", $now)[1])[2]
                );
                $directory .= $dateArray["year"] . "/" . $dateArray["month"] . "/" . $dateArray["day"] . "/";
                $newFileName = "image_" . intval($dateArray["year"]) . intval($dateArray["month"]) . intval($dateArray["day"]) . intval($dateArray["hour"]) . intval($dateArray["minutes"]) . intval($dateArray["seconds"]) . ".jpg";
                $upload = $directory . $newFileName;
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }
                $indexFilename = 2;
                while (file_exists($upload)) {
                    $filesplit = explode(".", $newFileName);
                    $name = $filesplit[0] . "_" . $indexFilename;
                    $exts = $filesplit[1];
                    $upload = $directory . $name . "." . $exts;
                    $indexFilename++;
                }
                if (move_uploaded_file($_FILES["file_image"]["tmp_name"], $upload)) {
                    $product_image = "/" . BASE_URL . explode("../", $upload)[1];
                } else {
                    $product_image = "";
                    array_push($msg_response["errors"], "La imagen no se ha podido subir al servidor.");
                }
            }

            $isCreated = false;
            $log_details = (object)[
                "before" => null,
                "after" => (object)[
                    "name" => trim($_POST["txt_name"]),
                    "image" => $product_image,
                    "units" => (intval($_POST["txt_stock"]) * intval($_POST["txt_units"])),
                    "description" => trim($_POST["txt_description"])
                ]
            ];
            $productID = gen_uuid();
            $result = $doctorFunctions->createProduct($productID, trim($_POST["txt_name"]), $product_image, (intval($_POST["txt_stock"]) * intval($_POST["txt_units"])), trim($_POST["txt_description"]));
            if ($result > 0) {
                $doctorFunctions->auditProduct("create", $productID, json_encode($log_details));
                $isCreated = true;
            }

            if ($isCreated) {
                array_push($msg_response["success"], "El producto ha sido registrado exitosamente.");
            } else {
                array_push($msg_response["errors"], "El producto no ha podido ser registrado.");
            }
        } else {
            array_push($msg_response["errors"], "La imagen seleccionada no cumple los requisitos, de tamaño o formato o hubo un error al guardar fichero.");
        }
    } else {
        array_push($msg_response["errors"], "Este producto ya se encuentra registrado.");
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Registrar Producto</title>

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

                    <h1 class="h3 mb-3">Registrar Producto</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Datos del Producto</h5>
                                </div>
                                <div class="card-body">
                                    <form id="create_form" enctype="multipart/form-data" action="create-product" method="post" novalidate="novalidate">
                                        <!-- To set an unique ID to this post form, to avoid duplicates -->
                                        <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                        <!--  -->

                                        <div class="mb-3 form-group">
                                            <label for="txt_name" class="form-label">Nombre <span style="color: red;">*</span></label>
                                            <input type="text" class="form-control" id="txt_name" name="txt_name" maxlength="150" onkeypress="return soloLetras(event)" required>
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_description" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="txt_description" name="txt_description"></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_units">Unidades por caja</label>
                                                    <input type="number" class="form-control" id="txt_units" name="txt_units" min="1" onkeypress="return soloNumeros(event)" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_stock">Stock inicial</label>
                                                    <input type="number" class="form-control" id="txt_stock" name="txt_stock" min="0" onkeypress="return soloNumeros(event)" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="file_image" class="form-label">Imagen</label>
                                            <input type="file" class="form-control btn btn-default" id="file_image" name="file_image" accept="image/jpeg">
                                            <input type="hidden" id="txt_image" name="txt_image" value="">
                                            <small class="form-text d-block text-muted">Tamaño máximo permitido 500KB, en formato jpg.</small>
                                        </div>

                                        <button type="submit" class="btn btn-success">Registrar</button>
                                    </form>
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
        $(document).ready(function() {
            $("#mnu_products").addClass("active");
            $("#mnu_products a:first").removeClass("collapsed");
            $("#mnugrp_products").addClass("show");
            $("#mnu_products_create").addClass("active");
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

            $("#txt_units, #txt_stock").on("input", function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            $("#file_image").filestyle({
                input: false,
                iconName: "fas fa-upload"
            });

            $('#create_form').validate({
                rules: {
                    txt_name: {
                        required: true
                    },
                    txt_units: {
                        required: true
                    },
                    txt_stock: {
                        required: true
                    }
                },
                messages: {
                    txt_name: {
                        required: "Este campo es requerido"
                    },
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
<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

$doctorFunctions = new DoctorFunctions();
if ($_SESSION["dep_user_role"] != "DR" ) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$productID = base64_decode(strrev(urldecode(explode("/", $_GET["id"])[0])));
$productData = $doctorFunctions->getProductByID($productID);
if (empty($productData)) {
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

    if (!$doctorFunctions->existsProduct($_POST["txt_name"], $productID)) {
        $correctPhoto = false;
        if ((!isset($_FILES["file_image"]) || $_FILES["file_image"]["name"] == "") || (isset($_FILES["file_image"]) && $_FILES["file_image"]["size"] <= 500000 && $_FILES["file_image"]["type"] == "image/jpeg")) {
            $correctPhoto = true;
        }
        if ($correctPhoto) {
            $product_image = "";
            if ($_FILES["file_image"]["name"] == "") {
                $product_image = $productData->image;
            } else {
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
                    $product_image = $productData->image;
                    array_push($msg_response["errors"], "La imagen no se ha podido subir al servidor.");
                }
            }

            $isUpdated = false;
            $log_details = (object)[
                "before" => (object)[
                    "name" => $productData->name,
                    "image" => $productData->image,
                    "units" => $productData->units,
                    "description" => $productData->description
                ],
                "after" => (object)[
                    "name" => trim($_POST["txt_name"]),
                    "image" => $product_image,
                    "units" => $productData->units,
                    "description" => trim($_POST["txt_description"])
                ]
            ];
            $result = $doctorFunctions->updateProduct($productID, trim($_POST["txt_name"]), $product_image, trim($_POST["txt_description"]));
            if ($result > 0) {
                $doctorFunctions->auditProduct("edit", $productID, json_encode($log_details));
                $isUpdated = true;
                $productData = $doctorFunctions->getProductByID($productID);
            }

            if ($isUpdated) {
                array_push($msg_response["success"], "El producto ha sido actualizado exitosamente.");
            } else {
                array_push($msg_response["errors"], "El producto no ha podido ser actualizado.");
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

    <title><?= ADMIN_PANEL_NAME ?> - Editar Producto</title>

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

                    <h1 class="h3 mb-3">Editar Producto</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Datos del Producto</h5>
                                </div>
                                <div class="card-body">
                                    <form id="edit_form" enctype="multipart/form-data" action="" method="post" novalidate="novalidate">
                                        <!-- To set an unique ID to this post form, to avoid duplicates -->
                                        <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                        <!--  -->

                                        <div class="row mb-4">
                                            <div class="col-md-8">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_name" class="form-label">Nombre <span style="color: red;">*</span></label>
                                                    <input type="text" class="form-control" id="txt_name" name="txt_name" maxlength="150" required value="<?= $productData->name ?>">
                                                </div>
                                                <div class="mb-3 form-group">
                                                    <label for="txt_description" class="form-label">Descripción</label>
                                                    <textarea class="form-control" id="txt_description" name="txt_description"><?= $productData->description ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <img id="img_image" alt="Producto" src="<?= (!empty($productData->image)) ? $productData->image : "/" . BASE_URL . "assets/dist/img/no_image.png" ?>" onerror="src='/<?= BASE_URL ?>assets/dist/img/no_image.png'" class="img-responsive mt-2" width="128" height="128" />
                                                    <div class="mt-2">
                                                        <input class="btn btn-default" type="file" name="file_image" id="file_image">
                                                        <input type="hidden" id="txt_image" name="txt_image" value="<?= $productData->image ?>">
                                                    </div>
                                                    <small>Tamaño máximo permitido 500KB, en formato jpg</small>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-outline-secondary" onclick="window.location='/<?= BASE_URL ?>home';">Cancelar</button>
                                        <button type="button" class="btn btn-primary" onclick="window.location='/<?= BASE_URL ?>manage-products';">Regresar a la lista</button>
                                        <button type="submit" class="btn btn-success">Guardar</button>
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
            $("#file_image").filestyle({
                input: false,
                iconName: "fas fa-upload"
            });

            $('#edit_form').validate({
                rules: {
                    txt_name: {
                        required: true
                    }
                },
                messages: {
                    txt_name: {
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
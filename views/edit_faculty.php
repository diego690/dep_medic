<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/admin.functions.php");

$adminFunctions = new AdminFunctions();
if ($_SESSION["dep_user_role"] != "AD") {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$facultyID = base64_decode(strrev(urldecode(explode("/", $_GET["id"])[0])));
$facultyData = $adminFunctions->getFacultyByID($facultyID)->fetch_object();

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

    if (!$adminFunctions->existsFaculty($_POST["txt_name"], $facultyID)) {
        $isUpdated = false;
        $result = $adminFunctions->updateFaculty($facultyID, trim($_POST["txt_name"]), trim($_POST["txt_description"]));
        if ($result > 0) {
            $isUpdated = true;
            $facultyData = $adminFunctions->getFacultyByID($facultyID)->fetch_object();
        }

        if ($isUpdated) {
            array_push($msg_response["success"], "La facultad ha sido actualizada exitosamente.");
        } else {
            array_push($msg_response["errors"], "La facultad no ha podido ser actualizada.");
        }
    } else {
        array_push($msg_response["errors"], "Esta facultad ya se encuentra registrada.");
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Editar Facultad</title>

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

                    <h1 class="h3 mb-3">Editar Facultad</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Datos de la Facultad</h5>
                                </div>
                                <div class="card-body">
                                    <form id="edit_form" action="" method="post" novalidate="novalidate">
                                        <!-- To set an unique ID to this post form, to avoid duplicates -->
                                        <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                        <!--  -->

                                        <div class="mb-3 form-group">
                                            <label for="txt_name" class="form-label">Nombre <span style="color: red;">*</span></label>
                                            <input type="text" class="form-control" id="txt_name" name="txt_name" maxlength="50" value="<?= $facultyData->name ?>">
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_description" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="txt_description" name="txt_description"><?= $facultyData->description ?></textarea>
                                        </div>

                                        <button type="button" class="btn btn-outline-secondary" onclick="window.location='/<?= BASE_URL ?>home';">Cancelar</button>
                                        <button type="button" class="btn btn-primary" onclick="window.location='/<?= BASE_URL ?>manage-faculties';">Regresar a la lista</button>
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
            $("#mnu_faculties").addClass("active");
            $("#mnu_faculties a:first").removeClass("collapsed");
            $("#mnugrp_faculties").addClass("show");
            $("#mnu_faculties_manage").addClass("active");
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
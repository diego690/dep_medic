<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/users.functions.php");

$userFunctions = new UsersFunctions();
$userData = $userFunctions->getUserByID($_SESSION["dep_user_id"]);

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

    if (isset($_POST["txt_email"])) {
        $correctPhoto = false;
        if ((!isset($_FILES["file_avatar"]) || $_FILES["file_avatar"]["name"] == "") || (isset($_FILES["file_avatar"]) && $_FILES["file_avatar"]["size"] <= 500000 && $_FILES["file_avatar"]["type"] == "image/jpeg")) {
            $correctPhoto = true;
        }
        if ($correctPhoto) {
            $avatar = "";
            if ($_FILES["file_avatar"]["name"] == "") {
                $avatar = $userData->avatar;
            } else {
                $directory = "../media/fotos/";
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
                $newFileName = "foto_" . intval($dateArray["year"]) . intval($dateArray["month"]) . intval($dateArray["day"]) . intval($dateArray["hour"]) . intval($dateArray["minutes"]) . intval($dateArray["seconds"]) . ".jpg";
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
                if (move_uploaded_file($_FILES["file_avatar"]["tmp_name"], $upload)) {
                    $avatar = "/" . BASE_URL . explode("../", $upload)[1];
                    if (str_contains($userData->avatar, "/media/fotos/")) {
                        if (!empty(BASE_URL)) {
                            $strrep = str_replace(BASE_URL, "", $userData->avatar);
                            unlink(".." . $strrep);
                        } else {
                            unlink(".." . $userData->avatar);
                        }
                    }
                } else {
                    $avatar = $userData->avatar;
                    array_push($msg_response["errors"], "La imagen no se ha podido subir al servidor.");
                }
            }

            $userFunctions->updateProfileByUserID($_SESSION["dep_user_id"], trim($_POST["txt_address"]), $_POST["txt_phone"], $_POST["select_civil_state"], $avatar, date("Y-m-d", strtotime($_POST["txt_birth_date"])), trim($_POST["txt_alt_email"]));
            $userData = $userFunctions->getUserByID($_SESSION["dep_user_id"]);
            $_SESSION["dep_user_avatar"] = $userData->avatar;
            array_push($msg_response["success"], "Los cambios han sido guardados.");
        } else {
            array_push($msg_response["errors"], "La imagen seleccionada no cumple los requisitos, de tamaño o formato o hubo un error al guardar fichero.");
        }
    } else if (isset($_POST["txt_pwd"])) {
        if (!$userFunctions->getUserByCredentials($_SESSION['dep_user_username'], $_POST['txt_pwd'])) {
            array_push($msg_response["errors"], "La contraseña actual no es válida.");
        } else {
            $userFunctions->changePassword($_SESSION["dep_user_id"], $_POST['txt_new_pwd']);
            array_push($msg_response["success"], "La contraseña ha sido cambiada.");
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Editar Perfil</title>

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

                    <?php if ($_SESSION["dep_user_role"] == "US") { ?>
                        <nav aria-label="breadcrumb" style="float: right;">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/<?= BASE_URL ?>home">Inicio</a></li>
                                <li class="breadcrumb-item active">Editar Perfil</li>
                            </ol>
                        </nav>
                    <?php } ?>

                    <h1 class="h3 mb-3">Perfil</h1>

                    <div class="row">
                        <div class="col-md-3 col-xl-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Opciones</h5>
                                </div>

                                <div class="list-group list-group-flush" role="tablist">
                                    <?php if ($_SESSION["dep_user_role"] != "AD") { ?>
                                        <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#panel-account" role="tab">
                                            Cuenta
                                        </a>
                                    <?php } ?>
                                    <a class="list-group-item list-group-item-action <?= ($_SESSION["dep_user_role"] == "AD") ? "active" : "" ?>" data-bs-toggle="list" href="#panel-password" role="tab">
                                        Contraseña
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9 col-xl-10">
                            <div class="tab-content">
                                <?php if ($_SESSION["dep_user_role"] != "AD") { ?>
                                    <div class="tab-pane fade show active" id="panel-account" role="tabpanel">

                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">Cuenta</h5>
                                            </div>
                                            <div class="card-body">
                                                <form id="account_form" enctype="multipart/form-data" action="edit-profile" method="post" novalidate="novalidate">
                                                    <!-- To set an unique ID to this post form, to avoid duplicates -->
                                                    <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                                    <!--  -->

                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <!-- disabled -->
                                                            <div class="mb-3 form-group">
                                                                <label for="txt_email" class="form-label">Correo</label>
                                                                <input type="text" readonly="true" class="form-control" id="txt_email" name="txt_email" placeholder="" value="<?= $userData->username ?>">
                                                            </div>
                                                            <div class="mb-3 form-group">
                                                                <label for="txt_fullname" class="form-label">Nombres y Apellidos</label>
                                                                <input type="text" readonly="true" class="form-control" id="txt_fullname" name="txt_fullname" placeholder="" value="<?= $userData->name . " " . $userData->last_name ?>">
                                                            </div>
                                                            <div class="mb-3 form-group">
                                                                <label for="txt_identification" class="form-label">Cédula</label>
                                                                <input type="text" readonly="true" class="form-control" id="txt_identification" name="txt_identification" placeholder="" value="<?= $userData->identification ?>">
                                                            </div>
                                                            <!-- ./disabled -->
                                                            <div class="mb-3 form-group">
                                                                <label for="txt_address" class="form-label">Domicilio <span style="color: red;">*</span></label>
                                                                <input type="text" class="form-control" id="txt_address" name="txt_address" placeholder="" value="<?= $userData->address ?>">
                                                            </div>
                                                            <div class="mb-3 form-group">
                                                                <label for="txt_phone" class="form-label">Celular</label>
                                                                <input type="text" class="form-control" id="txt_phone" name="txt_phone" value="<?= $userData->phone ?>" data-mask="0000000000">
                                                            </div>
                                                            <div class="mb-3 form-group">
                                                                <label for="select_civil_state" class="form-label">Estado Civil <span style="color: red;">*</span></label>
                                                                <select class="form-select" id="select_civil_state" name="select_civil_state" style="width: 100%;" required>
                                                                    <option>Soltero(a)</option>
                                                                    <option>Casado(a)</option>
                                                                    <option>Divorciado(a)</option>
                                                                    <option>Viudo(a)</option>
                                                                    <option>Unión de hecho</option>
                                                                    <option>Unión libre</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3 form-group">
                                                                <label for="txt_birth_date" class="form-label">Fecha de Nacimiento <span style="color: red;">*</span></label>
                                                                <input type="text" class="form-control" id="txt_birth_date" name="txt_birth_date" value="<?= date("m/d/Y", strtotime($userData->birth_date)) ?>">
                                                            </div>
                                                            <div class="mb-3 form-group">
                                                                <label for="txt_alt_email" class="form-label">Correo Personal</label>
                                                                <input type="email" class="form-control" id="txt_alt_email" name="txt_alt_email" value="<?= $userData->email ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="text-center">
                                                                <img id="img_avatar" alt="Avatar" src="<?= (!empty($userData->avatar)) ? $userData->avatar : "/" . BASE_URL . "assets/dist/img/user_avatar.png" ?>" onerror="src='/<?= BASE_URL ?>assets/dist/img/user_avatar.png'" class="rounded-circle img-responsive mt-2" width="128" height="128" />
                                                                <div class="mt-2">
                                                                    <input class="btn btn-default" type="file" name="file_avatar" id="file_avatar">
                                                                    <input type="hidden" id="txt_avatar" name="txt_avatar" value="<?= $userData->avatar ?>">
                                                                </div>
                                                                <small>Tamaño máximo permitido 500KB, en formato jpg</small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <button type="submit" class="btn btn-success">Guardar cambios</button>
                                                </form>

                                            </div>
                                        </div>

                                    </div>
                                <?php } ?>
                                <div class="tab-pane fade <?= ($_SESSION["dep_user_role"] == "AD") ? "show active" : "" ?>" id="panel-password" role="tabpanel">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Contraseña</h5>

                                            <form id="password_form" action="edit-profile" method="post" novalidate="novalidate">
                                                <!-- To set an unique ID to this post form, to avoid duplicates -->
                                                <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                                <!--  -->

                                                <div class="mb-3 form-group">
                                                    <label for="txt_pwd" class="form-label">Contraseña actual <span style="color: red;">*</span></label>
                                                    <input type="password" class="form-control" id="txt_pwd" name="txt_pwd">
                                                </div>
                                                <div class="mb-3 form-group">
                                                    <label for="txt_new_pwd" class="form-label">Nueva contraseña <span style="color: red;">*</span></label>
                                                    <input type="password" class="form-control" id="txt_new_pwd" name="txt_new_pwd">
                                                </div>
                                                <div class="mb-3 form-group">
                                                    <label for="txt_verify_pwd" class="form-label">Verificar contraseña <span style="color: red;">*</span></label>
                                                    <input type="password" class="form-control" id="txt_verify_pwd" name="txt_verify_pwd">
                                                </div>
                                                <button type="submit" class="btn btn-success">Guardar cambios</button>
                                            </form>

                                        </div>
                                    </div>
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

            <?php if ($_SESSION["dep_user_role"] != "AD") { ?>
                $("#select_civil_state").select2({
                    width: "resolve",
                    minimumResultsForSearch: -1
                });
                <?php if (!empty($userData->civil_state)) { ?>
                    $("#select_civil_state").val("<?= $userData->civil_state ?>").trigger("change");
                <?php } else { ?>
                    $("#select_civil_state option:eq(0)").prop("selected", true);
                <?php } ?>
                $("#file_avatar").filestyle({
                    input: false,
                    iconName: "fas fa-upload"
                });
                $("#txt_birth_date").daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    locale: {
                        cancelLabel: 'Limpiar',
                        applyLabel: 'Aceptar',
                        daysOfWeek: [
                            "Do",
                            "Lu",
                            "Ma",
                            "Mi",
                            "Ju",
                            "Vi",
                            "Sa"
                        ],
                        monthNames: [
                            "Enero",
                            "Febrero",
                            "Marzo",
                            "Abril",
                            "Mayo",
                            "Junio",
                            "Julio",
                            "Agosto",
                            "Septiembre",
                            "Octubre",
                            "Noviembre",
                            "Diciembre"
                        ]
                    },
                    opens: 'left'
                });
                $('#txt_birth_date').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                });

                $('#account_form').validate({
                    rules: {
                        txt_address: {
                            required: true
                        },
                        select_civil_state: {
                            required: true
                        },
                        txt_phone: {
                            minlength: 10
                        },
                        txt_birth_date: {
                            required: true
                        },
                        txt_alt_email: {
                            email: true
                        }
                    },
                    messages: {
                        txt_address: {
                            required: "Este campo es requerido"
                        },
                        select_civil_state: {
                            required: "Este campo es requerido"
                        },
                        txt_phone: {
                            minlength: "El número de celular no es válido"
                        },
                        txt_birth_date: {
                            required: "Este campo es requerido"
                        },
                        txt_alt_email: {
                            email: "Ingrese un correo válido"
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
            <?php } ?>

            $('#password_form').validate({
                rules: {
                    txt_pwd: {
                        required: true
                    },
                    txt_new_pwd: {
                        required: true
                    },
                    txt_verify_pwd: {
                        required: true,
                        equalTo: "#txt_new_pwd"
                    }
                },
                messages: {
                    txt_pwd: {
                        required: "Ingrese su contraseña actual"
                    },
                    txt_new_pwd: {
                        required: "Ingrese la nueva contraseña"
                    },
                    txt_verify_pwd: {
                        required: "Por favor, repita la nueva contraseña",
                        equalTo: "Las contraseñas no coinciden"
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
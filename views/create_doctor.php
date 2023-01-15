<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/users.functions.php");
require_once(__DIR__ . "/../data/mysql/admin.functions.php");
require_once(__DIR__ . "/../data/mysql/panel.functions.php");

$usersFunctions = new UsersFunctions();
$adminFunctions = new AdminFunctions();
$panelFunctions = new PanelFunctions();
if ($_SESSION["dep_user_role"] != "AD") {
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

    if (!$usersFunctions->existsUserPerson($_POST["txt_email"], $_POST["txt_identification"])) {
        $isCreated = false;
        $personID = gen_uuid();
        $alt_email = $_POST["txt_alt_email"];
        if (empty(trim($_POST["txt_alt_email"]))) {
            $alt_email = $_POST["txt_email"];
        }
        $birth_date = date("Y-m-d", strtotime($_POST["txt_birth_date"]));
        $result = $usersFunctions->createPerson($personID, trim($_POST["txt_name"]), trim($_POST["txt_lastname"]), $_POST["txt_identification"], $alt_email, $_POST["txt_phone"], $birth_date, $_POST["select_civil_state"], trim($_POST["txt_address"]), $_POST["select_sex"]);
        if ($result > 0) {
            $userID = gen_uuid();
            $result = $usersFunctions->createUser($userID, $personID, $_POST["txt_email"], "DR", $_POST["txt_identification"]);
            if ($result > 0) {
                $result = $adminFunctions->createUserArea($userID, base64_decode(strrev(urldecode($_POST["select_area"]))));
                if ($result > 0) {
                    $isCreated = true;
                } else {
                    $usersFunctions->deleteUser($userID);
                    $usersFunctions->deletePerson($personID);
                }
            } else {
                $usersFunctions->deletePerson($personID);
            }
        }

        if ($isCreated) {
            array_push($msg_response["success"], "El doctor ha sido registrado exitosamente.");
        } else {
            array_push($msg_response["errors"], "El doctor no ha podido ser registrado.");
        }
    } else {
        array_push($msg_response["errors"], "Este doctor ya se encuentra registrado.");
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Registrar Doctor</title>

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

                    <h1 class="h3 mb-3">Registrar Doctor</h1>
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="alert-icon">
                            <i class="fas fa-fw fa-info"></i>
                        </div>
                        <div class="alert-message">
                            <strong>Registre un doctor/enfermero para el departamento médico</strong>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Datos del Doctor</h5>
                                </div>
                                <div class="card-body">
                                    <form id="create_form" action="create-doctor" method="post" novalidate="novalidate">
                                        <!-- To set an unique ID to this post form, to avoid duplicates -->
                                        <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                        <!--  -->

                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_email" class="form-label">Correo Institucional <span style="color: red;">*</span></label>
                                                    <input type="email" class="form-control" id="txt_email" name="txt_email">
                                                    <small class="form-text d-block text-muted">Este correo se utilizará como usuario para acceder a la cuenta.</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_identification" class="form-label">Cédula / Pasaporte <span style="color: red;">*</span></label>
                                                    <input type="text" class="form-control" id="txt_identification" name="txt_identification" onkeypress="return soloNumeros(event)" maxlength="15">
                                                    <small class="form-text d-block text-muted">Este campo se utilizará como contraseña, el usuario la podrá cambiar luego.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_name" class="form-label">Nombres <span style="color: red;">*</span></label>
                                                    <input type="text" class="form-control" id="txt_name" name="txt_name" maxlength="50" onkeypress="return soloLetras(event)">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_lastname" class="form-label">Apellidos <span style="color: red;">*</span></label>
                                                    <input type="text" class="form-control" id="txt_lastname" name="txt_lastname" maxlength="50" onkeypress="return soloLetras(event)">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
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
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_birth_date" class="form-label">Fecha de Nacimiento <span style="color: red;">*</span></label>
                                                    <input type="text" class="form-control" id="txt_birth_date" name="txt_birth_date">
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="mb-3 form-group">
                                                    <label for="select_sex" class="form-label">Sexo <span style="color: red;">*</span></label>
                                                    <select class="form-control" id="select_sex" name="select_sex" required style="width: 100%;">
                                                        <option value="M">Masculino</option>
                                                        <option value="F">Femenino</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_phone" class="form-label">Celular</label>
                                                    <input type="text" class="form-control" id="txt_phone" name="txt_phone" data-mask="0000000000" onkeypress="return soloNumeros(event)">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_alt_email" class="form-label">Correo Personal&nbsp;&nbsp;<i class="fas fa-info-circle" data-toggle="tooltip" data-placement="right" title="Si este campo queda en blanco, se reemplazará por el correo institucional"></i></label>
                                                    <input type="email" class="form-control" id="txt_alt_email" name="txt_alt_email">
                                                    <small class="form-text d-block text-muted">Este correo se utilizará para las citas de telemedicina.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_address" class="form-label">Domicilio <span style="color: red;">*</span></label>
                                                    <input type="text" class="form-control" id="txt_address" name="txt_address" maxlength="90">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="select_area" class="form-label">Área de trabajo <span style="color: red;">*</span></label>
                                                    <select class="form-select" id="select_area" name="select_area" style="width: 100%;" required>
                                                        <?php
                                                        $areas = $panelFunctions->getAreas();
                                                        while ($row = $areas->fetch_object()) {
                                                        ?>
                                                            <option value="<?= urlencode(strrev(base64_encode($row->id))) ?>"><?= $row->name . " - Campus " . $row->campus ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
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
            $("#mnu_accounts").addClass("active");
            $("#mnu_accounts a:first").removeClass("collapsed");
            $("#mnugrp_accounts").addClass("show");
            $("#mnu_accounts_create").addClass("active");
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

            $("#select_civil_state").select2({
                width: "resolve",
                minimumResultsForSearch: -1
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
            $("#select_area, #select_sex").select2({
                width: "resolve",
                minimumResultsForSearch: -1
            });

            $.validator.addMethod("is_institucional_email", function(value, element, param) {
                return (value.split("@")[1] == "uteq.edu.ec") ? true : false;
            }, "Ingrese un correo institucional");
            $('#create_form').validate({
                rules: {
                    txt_email: {
                        required: true,
                        email: true,
                        is_institucional_email: true
                    },
                    txt_identification: {
                        required: true,
                        maxlength: 15,
                        minlength: 10
                    },
                    txt_name: {
                        required: true
                    },
                    txt_lastname: {
                        required: true
                    },
                    select_civil_state: {
                        required: true
                    },
                    txt_birth_date: {
                        required: true
                    },
                    txt_address: {
                        required: true
                    },
                    txt_phone: {
                        minlength: 10
                    },
                    txt_alt_email: {
                        email: true
                    }
                },
                messages: {
                    txt_email: {
                        required: "Este campo es requerido",
                        email: "Ingrese un correo válido"
                    },
                    txt_identification: {
                        required: "Este campo es requerido",
                        maxlength: "Número de cédula no válido",
                        minlength: "Número de cédula no válido"
                    },
                    txt_name: {
                        required: "Este campo es requerido"
                    },
                    txt_lastname: {
                        required: "Este campo es requerido"
                    },
                    select_civil_state: {
                        required: "Este campo es requerido"
                    },
                    txt_birth_date: {
                        required: "Este campo es requerido"
                    },
                    txt_address: {
                        required: "Este campo es requerido"
                    },
                    txt_phone: {
                        minlength: "El número de celular no es válido"
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
        });
    </script>

</body>

</html>
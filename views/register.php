<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/helpers/mail_sender.php");
require_once(__DIR__ . "/../data/mysql/users.functions.php");
require_once(__DIR__ . "/../data/mysql/auth.functions.php");
require_once(__DIR__ . "/../data/mysql/panel.functions.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

if (is_logged()) {
    header("Location: home");
    return;
}

$usersFunctions = new UsersFunctions();
$authFunctions = new AuthFunctions();
$panelFunctions = new PanelFunctions();
$doctorFunctions = new DoctorFunctions();

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
        $correctFile = false;
        if ((!isset($_FILES["file_backup"]) || $_FILES["file_backup"]["name"] == "") || (isset($_FILES["file_backup"]) && $_FILES["file_backup"]["size"] <= 40000000 && $_FILES["file_backup"]["type"] == "application/pdf")) {
            $correctFile = true;
        }
        if ($correctFile) {
            if (isset($_FILES["file_backup"])) {
                $backup = "";
                $directory = "../media/backup_docs_temp/";
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
                $newFileName = "archivo_" . intval($dateArray["year"]) . intval($dateArray["month"]) . intval($dateArray["day"]) . intval($dateArray["hour"]) . intval($dateArray["minutes"]) . intval($dateArray["seconds"]) . ".pdf";
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
                if (move_uploaded_file($_FILES["file_backup"]["tmp_name"], $upload)) {
                    $backup = "/" . BASE_URL . explode("../", $upload)[1];
                } else {
                    $backup = "";
                    array_push($msg_response["errors"], "El archivo no se ha podido subir al servidor.");
                }
            }

            if (!empty($backup)) {
                $isCreated = false;
                $personID = gen_uuid();
                $alt_email = $_POST["txt_email"];
                $birth_date = date("Y-m-d", strtotime($_POST["txt_birth_date"]));
                $result = $usersFunctions->createPerson($personID, trim($_POST["txt_name"]), trim($_POST["txt_lastname"]), $_POST["txt_identification"], $alt_email, $_POST["txt_phone"], $birth_date, $_POST["select_civil_state"], trim($_POST["txt_address"]), $_POST["select_sex"]);
                if ($result > 0) {
                    $userID = gen_uuid();
                    $result = $usersFunctions->createUser($userID, $personID, $_POST["txt_email"], "US", $_POST["txt_identification"]);
                    if ($result > 0) {
                        $result = $doctorFunctions->createUserCareer($userID, $_POST["select_career"], 1);
                        $doctorFunctions->createDoc($personID, "Exámenes Médicos", $backup);
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
                    array_push($msg_response["success"], "El usuario ha sido registrado exitosamente.");
                } else {
                    array_push($msg_response["errors"], "El usuario no ha podido ser registrado.");
                }
            }
        } else {
            array_push($msg_response["errors"], "El archivo seleccionado no cumple los requisitos, de tamaño o formato o hubo un error al guardar fichero.");
        }
    } else {
        array_push($msg_response["errors"], "El alumno ya se encuentra registrado.");
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Registrar Usuario</title>

    <link rel="shortcut icon" href="/<?= BASE_URL ?>assets/dist/img/logo.png" sizes="32x32">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

    <link class="js-stylesheet" href="/<?= BASE_URL ?>assets/auth-plugins/css/light.css" rel="stylesheet">
    <link href="/<?= BASE_URL ?>assets/plugins/toastr/toastr.min.css" rel="stylesheet">
    <style>
        .bootstrap-filestyle.input-group {
            display: block;
        }

        .bootstrap-filestyle.input-group span.badge {
            background-color: #4bbf73 !important;
        }
    </style>
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-behavior="sticky">
    <div class="main d-flex justify-content-center w-100">
        <main class="content d-flex p-0">
            <div class="container d-flex flex-column">
                <div class="row h-100">
                    <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
                        <div class="d-table-cell align-middle">

                            <div class="text-center mt-4">
                                <h1 class="h2">Registrar Usuario</h1>
                                <p class="lead">
                                    Si eres estudiante de primer semestre ingresa tus datos para crear tu cuenta.
                                </p>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="m-sm-4">
                                        <form id="create_form" enctype="multipart/form-data" action="" method="post" novalidate="novalidate">
                                            <!-- To set an unique ID to this post form, to avoid duplicates -->
                                            <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                            <!--  -->

                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="txt_identification" class="form-label">Cédula / Pasaporte <span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="txt_identification" name="txt_identification">
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="txt_email" class="form-label">Correo Institucional <span style="color: red;">*</span></label>
                                                        <input type="email" class="form-control" id="txt_email" name="txt_email">
                                                        <small class="form-text d-block text-muted">Este correo se utilizará como usuario para acceder a la cuenta. La cédula como contraseña.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="txt_name" class="form-label">Nombres <span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="txt_name" name="txt_name" maxlength="50">
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="txt_lastname" class="form-label">Apellidos <span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="txt_lastname" name="txt_lastname" maxlength="50">
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
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="txt_birth_date" class="form-label">Fecha de Nacimiento <span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="txt_birth_date" name="txt_birth_date">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="txt_phone" class="form-label">Celular</label>
                                                        <input type="text" class="form-control" id="txt_phone" name="txt_phone" data-mask="0000000000">
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
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
                                                <div class="col-12">
                                                    <div class="mb-3 form-group">
                                                        <label for="txt_address" class="form-label">Domicilio <span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control" id="txt_address" name="txt_address" maxlength="90">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="select_faculty" class="form-label">Facultad <span style="color: red;">*</span></label>
                                                        <select class="form-select" id="select_faculty" name="select_faculty" style="width: 100%;" required>
                                                            <?php
                                                            $faculties = $panelFunctions->getFaculties();
                                                            while ($r = $faculties->fetch_object()) {
                                                            ?>
                                                                <option value="<?= $r->id ?>"><?= $r->name ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="select_career" class="form-label">Carrera <span style="color: red;">*</span></label>
                                                        <select class="form-select" id="select_career" name="select_career" style="width: 100%;" required>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12 col-md-12">
                                                    <div class="mb-3 form-group">
                                                        <label for="file_backup" class="form-label">Exámenes médicos <span style="color: red;">*</span></label>
                                                        <input type="file" class="form-control btn btn-default" id="file_backup" name="file_backup" accept="application/pdf">
                                                        <input type="hidden" id="txt_backup" name="txt_backup" value="">
                                                        <small class="form-text d-block text-muted">Añada un solo documento pdf con los resultados de los exámenes médicos realizados. Tamaño máximo permitido 40MB, en formato pdf.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-lg btn-success">Registrar</button>
                                                <a href="login" class="btn btn-lg btn-info">Cancelar</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/<?= BASE_URL ?>assets/auth-plugins/js/app.js"></script>
    <script src="/<?= BASE_URL ?>assets/plugins/toastr/toastr.min.js"></script>
    <script src="/<?= BASE_URL ?>assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="/<?= BASE_URL ?>assets/plugins/bootstrap-filestyle/bootstrap-filestyle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/locale/es.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chance/1.0.13/chance.min.js"></script>

    <script>
        $(document).ready(function() {
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

            $("#select_civil_state, #select_sex").select2({
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
            $("#select_faculty, #select_career").select2({
                width: "resolve"
            });
            $("#select_faculty").on("change", function() {
                $("#select_career").empty();
                $.ajax({
                    url: '/<?= BASE_URL ?>ajax2.php',
                    method: 'POST',
                    data: {
                        action: "get_careers_by_faculty",
                        faculty_id: $(this).val()
                    },
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.status != 500) {
                            $.each(response.data, function(each_i, each_v) {
                                $("#select_career").append(new Option(each_v.text, each_v.id));
                            });
                            if ($("#select_career option").length > 0) {
                                $("#select_career").prop("selectedIndex", 0).trigger("change");
                            }
                        }
                    }
                });
            });
            $("#select_faculty").val($("#select_faculty option:first").val()).trigger("change");
            $("#file_backup").filestyle({
                input: false,
                iconName: "fas fa-upload"
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
                    select_career: {
                        required: true
                    },
                    file_backup: {
                        required: true
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
                    select_career: {
                        required: "Este campo es requerido"
                    },
                    file_backup: {
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
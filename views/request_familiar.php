<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/us.functions.php");

$usFunctions = new usFunctions();
if ($_SESSION["dep_user_role"] != "US" || ($_SESSION["dep_user_role"] == "US" && $_SESSION["dep_user_is_employee"] == false)) {
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

    if ($usFunctions->isFamiliarRequestLimited()) {
        array_push($msg_response["errors"], "Ya ha superado el límite de solicitudes disponibles.");
    } else {
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
                    array_push($msg_response["errors"], "El archivo de respaldo no se ha podido subir al servidor.");
                }
            }

            $isCreated = false;
            $user = $usFunctions->existsPerson($_POST["txt_identification"]);
            if ($user->num_rows > 0) {
                $user = $user->fetch_object();
                $familiarID = $user->id;
                $requestID = gen_uuid();
                $result = $usFunctions->insertFamiliarRequest($requestID, $_SESSION["dep_user_id"], "existing", $_POST["select_kinship"], $familiarID);
                if ($result > 0) {
                    $isCreated = true;
                }
            } else {
                $requestID = gen_uuid();
                $result = $usFunctions->insertFamiliarRequest($requestID, $_SESSION["dep_user_id"], "new", $_POST["select_kinship"]);
                if ($result > 0) {
                    $birth_date = date("Y-m-d", strtotime($_POST["txt_birth_date"]));
                    $result = $usFunctions->insertFamiliarRequestDetail($requestID, $_POST["txt_name"], $_POST["txt_lastname"], $_POST["txt_identification"], $_POST["txt_alt_email"], $_POST["txt_phone"], $birth_date, $_POST["select_civil_state"], $_POST["txt_address"], $backup, $_POST["select_sex"]);
                    if ($result > 0) {
                        $isCreated = true;
                    } else {
                        $usFunctions->deleteFamiliarRequest($requestID);
                    }
                }
            }

            if ($isCreated) {
                array_push($msg_response["success"], "La solicitud ha sido registrada exitosamente.");
            } else {
                array_push($msg_response["errors"], "La solicitud no ha podido ser registrada.");
            }
        } else {
            array_push($msg_response["errors"], "El archivo de respaldo seleccionado no cumple los requisitos, de tamaño o formato o hubo un error al guardar fichero.");
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

    <title><?= ADMIN_PANEL_NAME ?> - Solicitar Registro Familiar</title>

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
                            <li class="breadcrumb-item active">Añadir Familiar</li>
                        </ol>
                    </nav>

                    <h1 class="h3 mb-3">Solicitar Registro Familiar</h1>
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="alert-icon">
                            <i class="fas fa-fw fa-info"></i>
                        </div>
                        <div class="alert-message">
                            <strong>
                                Para solicitar el registro de un familiar, deberá ingresar su cédula y presionar el botón de búsqueda para verificar si el familiar se encuentra registrado:<br />
                                <br />> Si se encuentra registrado, se cargarán sus datos y tan solo deberá seleccionar el parentesco y presionar en Aceptar.
                                <br />> Si no se encuentra registrado, tendrá que llenar toda la información requerida para agregar al familiar como nuevo paciente y vincularlo de acuerdo al parentesco seleccionado.
                            </strong>
                        </div>
                    </div>
                    <?php if ($usFunctions->isFamiliarRequestLimited()) { ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <div class="alert-icon">
                                <i class="fas fa-fw fa-exclamation-triangle"></i>
                            </div>
                            <div class="alert-message">
                                <strong>
                                    Ya tiene una solicitud pendiente, ha superado el límite de solicitudes disponibles.
                                </strong>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Datos del Familiar</h5>
                                </div>
                                <div class="card-body">
                                    <form id="create_form" enctype="multipart/form-data" action="request-familiar" method="post" novalidate="novalidate">
                                        <!-- To set an unique ID to this post form, to avoid duplicates -->
                                        <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                        <!--  -->

                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_identification" class="form-label">Cédula / Pasaporte <span style="color: red;">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="txt_identification" name="txt_identification">
                                                        <button id="btn_search" class="btn btn-success" type="button"><i class="fas fa-search"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="select_kinship" class="form-label">Parentesco <span style="color: red;">*</span></label>
                                                    <select class="form-select" id="select_kinship" name="select_kinship" style="width: 100%;" required>
                                                        <option>Madre</option>
                                                        <option>Padre</option>
                                                        <option>Hijo/a</option>
                                                        <option>Esposo/a</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_name" class="form-label">Nombres <span style="color: red;">*</span></label>
                                                    <input type="text" disabled="disabled" class="form-control" id="txt_name" name="txt_name" maxlength="50">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_lastname" class="form-label">Apellidos <span style="color: red;">*</span></label>
                                                    <input type="text" disabled="disabled" class="form-control" id="txt_lastname" name="txt_lastname" maxlength="50">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="select_civil_state" class="form-label">Estado Civil <span style="color: red;">*</span></label>
                                                    <select class="form-select" disabled="disabled" id="select_civil_state" name="select_civil_state" style="width: 100%;" required>
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
                                                    <input type="text" disabled="disabled" class="form-control" id="txt_birth_date" name="txt_birth_date">
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="mb-3 form-group">
                                                    <label for="select_sex" class="form-label">Sexo <span style="color: red;">*</span></label>
                                                    <select disabled="disabled" class="form-control" id="select_sex" name="select_sex" required style="width: 100%;">
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
                                                    <input type="text" disabled="disabled" class="form-control" id="txt_phone" name="txt_phone" data-mask="0000000000">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_alt_email" class="form-label">Correo Personal</label>
                                                    <input type="email" disabled="disabled" class="form-control" id="txt_alt_email" name="txt_alt_email">
                                                    <small class="form-text d-block text-muted">Este correo se utilizará para las citas de telemedicina.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_address" class="form-label">Domicilio <span style="color: red;">*</span></label>
                                                    <input type="text" disabled="disabled" class="form-control" id="txt_address" name="txt_address" maxlength="90">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="file_backup" class="form-label">Documento de respaldo <span style="color: red;">*</span></label>
                                                    <input type="file" disabled="disabled" class="form-control btn btn-default" id="file_backup" name="file_backup" accept="application/pdf">
                                                    <input type="hidden" id="txt_backup" name="txt_backup" value="">
                                                    <small class="form-text d-block text-muted">Añada un documento pdf de la copia de cédula del familiar y del empleado de la UTEQ. Tamaño máximo permitido 40MB, en formato pdf.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <button id="btn_submit" type="submit" disabled="disabled" class="btn btn-success">Aceptar</button>
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
        function clearFields() {
            $("#txt_name").val("");
            $("#txt_lastname").val("");
            $("#select_civil_state option:eq(0)").prop("selected", true).trigger("change");
            $("#txt_birth_date").val("");
            $("#select_sex").val("M").trigger("change");
            $("#txt_phone").val("");
            $("#txt_alt_email").val("");
            $("#txt_address").val("");
            $("#file_backup").filestyle("clear");
        }

        function blockFields(val) {
            $("#txt_name").prop("disabled", val);
            $("#txt_lastname").prop("disabled", val);
            $("#select_civil_state").prop("disabled", val);
            $("#txt_birth_date").prop("disabled", val);
            $("#select_sex").prop("disabled", val);
            $("#txt_phone").prop("disabled", val);
            $("#txt_alt_email").prop("disabled", val);
            $("#txt_address").prop("disabled", val);
            $("#file_backup").prop("disabled", val);
            if (val) {
                $(".group-span-filestyle label[for=file_backup]").css("cursor", "not-allowed");
            } else {
                $(".group-span-filestyle label[for=file_backup]").css("cursor", "pointer");
            }
            $("#btn_submit").prop("disabled", val);
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

            $("#select_kinship, #select_sex").select2({
                width: "resolve",
                minimumResultsForSearch: -1
            });
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
            $("#file_backup").filestyle({
                input: false,
                iconName: "fas fa-upload"
            });
            $(".group-span-filestyle label[for=file_backup]").css("cursor", "not-allowed");

            $('#txt_birth_date').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
            $("#txt_identification").on("keyup", function() {
                clearFields();
                blockFields(true);
            });
            $("#btn_search").on("click", function() {
                $.ajax({
                    url: '/<?= BASE_URL ?>ajax.php',
                    method: 'POST',
                    data: {
                        action: "exist_person",
                        identification: $("#txt_identification").val(),
                    },
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.status == 500) {
                            toastr.error(response.error);
                        } else {
                            if (response.data == null) {
                                clearFields();
                                blockFields(false);
                                toastr.info("Familiar no registrado, ingrese todos los datos requeridos.");
                            } else if (response.data == "-1") {
                                clearFields();
                                blockFields(true);
                                toastr.warning("Cédula no aceptada");
                            } else {
                                blockFields(true);
                                $("#btn_submit").prop("disabled", false);
                                toastr.info("Familiar encontrado, seleccione el parentesco.");
                                $("#txt_name").val(response.data.name);
                                $("#txt_lastname").val(response.data.last_name);
                                $("#select_civil_state").val(response.data.civil_state).trigger("change");
                                $("#txt_birth_date").val(response.data.birth_date);
                                $("#select_sex").val(response.data.sex).trigger("change");
                                $("#txt_phone").val(response.data.phone);
                                $("#txt_alt_email").val(response.data.email);
                                $("#txt_address").val(response.data.address);
                            }
                        }
                    }
                });
            });


            $('#create_form').validate({
                rules: {
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
                    select_kinship: {
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
                    },
                    file_backup: {
                        required: true
                    }
                },
                messages: {
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
                    select_kinship: {
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
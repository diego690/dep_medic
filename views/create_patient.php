<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/users.functions.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");
require_once(__DIR__ . "/../data/mysql/panel.functions.php");

$usersFunctions = new UsersFunctions();
$doctorFunctions = new DoctorFunctions();
$panelFunctions = new PanelFunctions();
if ($_SESSION["dep_user_role"] != "DR") {
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

    if ($_POST["txt_familiar"] == "true") {
        if (!$usersFunctions->existsPerson($_POST["txt_identification"])) {
            $isCreated = false;
            $personID = gen_uuid();
            $birth_date = date("Y-m-d", strtotime($_POST["txt_birth_date"]));
            $result = $usersFunctions->createPerson($personID, trim($_POST["txt_name"]), trim($_POST["txt_lastname"]), $_POST["txt_identification"], trim($_POST["txt_alt_email"]), $_POST["txt_phone"], $birth_date, $_POST["select_civil_state"], trim($_POST["txt_address"]), $_POST["select_sex"]);
            if ($result > 0) {
                $result = $doctorFunctions->createUserKinship($_POST["select_familiar"], $personID, $_POST["select_kin"]);
                if ($result > 0) {
                    $isCreated = true;
                } else {
                    $usersFunctions->deletePerson($personID);
                }
            }

            if ($isCreated) {
                array_push($msg_response["success"], "El paciente ha sido registrado exitosamente.");
            } else {
                array_push($msg_response["errors"], "El paciente no ha podido ser registrado.");
            }
        } else {
            array_push($msg_response["errors"], "Este paciente ya se encuentra registrado.");
        }
    } else {
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
                $result = $usersFunctions->createUser($userID, $personID, $_POST["txt_email"], "US", $_POST["txt_identification"]);
                if ($result > 0) {
                    if ($_POST["select_user_type"] == "EM") {
                        $result = $doctorFunctions->createUserOccupation($userID, $_POST["select_occupation"]);
                    } else {
                        $result = $doctorFunctions->createUserCareer($userID, $_POST["select_career"], $_POST["txt_semester"]);
                    }
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
                array_push($msg_response["success"], "El paciente ha sido registrado exitosamente.");
            } else {
                array_push($msg_response["errors"], "El paciente no ha podido ser registrado.");
            }
        } else {
            array_push($msg_response["errors"], "Este paciente ya se encuentra registrado.");
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

    <title><?= ADMIN_PANEL_NAME ?> - Registrar Paciente</title>

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

                    <h1 class="h3 mb-3">Registrar Paciente</h1>
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="alert-icon">
                            <i class="fas fa-fw fa-info"></i>
                        </div>
                        <div class="alert-message">
                            <strong>Al registrar un paciente, deberá indicar si es un familiar o no:<br />
                                <br />> Si es un familiar, seleccione de quién es.
                                <br />> Si no es un familiar, llene los datos de la cuenta de usuario y especificar si es un estudiante o empleado.</strong>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Datos del Paciente</h5>
                                </div>
                                <div class="card-body">
                                    <form id="create_form" action="create-patient" method="post" novalidate="novalidate">
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
                                            <!-- Switch -->
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="tog_familiar" class="form-label">¿Es familiar?</label>
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" id="tog_familiar" name="tog_familiar" style="cursor: pointer;">
                                                        <label class="form-check-label">No / Si</label>
                                                    </div>
                                                    <input type="hidden" id="txt_familiar" name="txt_familiar" value="">
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
                                                    <input type="text" class="form-control" id="txt_phone" name="txt_phone" data-mask="0000000000">
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
                                            <div class="col-12">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_address" class="form-label">Domicilio <span style="color: red;">*</span></label>
                                                    <input type="text" class="form-control" id="txt_address" name="txt_address" maxlength="90">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group" style="display: none;">
                                                    <label for="select_familiar" class="form-label">Familiar de... <span style="color: red;">*</span></label>
                                                    <div class="input-group">
                                                        <select class="form-select" id="select_familiar" name="select_familiar" style="width: calc(100% - 40px) !important;" required>
                                                            
                                                        </select>
                                                        <button id="btn_search_employee" type="button" class="input-group-append input-group-text btn btn-secondary" disabled="true"><i class="fa fa-search"></i></button>
                                                    </div>
                                                    <small class="form-text d-block text-muted">Búsqueda por cédula.</small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group" style="display: none;">
                                                    <label for="select_kin" class="form-label">Parentesco <span style="color: red;">*</span></label>
                                                    <select class="form-select" id="select_kin" name="select_kin" style="width: 100%;" required>
                                                        <option>Madre</option>
                                                        <option>Padre</option>
                                                        <option>Hijo/a</option>
                                                        <option>Esposo/a</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="user_data_container" style="display: block;">
                                            <hr />
                                            <div class="row">
                                                <div class="col-12" style="padding-bottom: 20px; padding-top: 16px;">
                                                    <h5 class="card-title mb-0">Datos de Usuario</h5>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="txt_email" class="form-label">Correo Institucional <span style="color: red;">*</span></label>
                                                        <input type="email" class="form-control" id="txt_email" name="txt_email">
                                                        <small class="form-text d-block text-muted">Este correo se utilizará como usuario para acceder a la cuenta. La cédula como contraseña.</small>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="select_user_type" class="form-label">Tipo de usuario <span style="color: red;">*</span></label>
                                                        <select class="form-select" id="select_user_type" name="select_user_type" style="width: 100%;" required>
                                                            <option value="EM">Empleado</option>
                                                            <option value="ES">Estudiante</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="_student_container" style="display: none;" class="row">
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
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="txt_semester" class="form-label">Semestre <span style="color: red;">*</span></label>
                                                        <input type="number" class="form-control" id="txt_semester" name="txt_semester" min="1" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="_employee_container" class="row">
                                                <div class="col-12 col-md-6">
                                                    <div class="mb-3 form-group">
                                                        <label for="select_occupation" class="form-label">Ocupación <span style="color: red;">*</span></label>
                                                        <select class="form-select" id="select_occupation" name="select_occupation" style="width: 100%;" required>
                                                            <?php
                                                            $occupations = $panelFunctions->getOccupations();
                                                            while ($r = $occupations->fetch_object()) {
                                                            ?>
                                                                <option value="<?= $r->id ?>"><?= $r->name ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
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
        var search_employee = "";

        $(document).ready(function() {
            $("#mnu_patients").addClass("active");
            $("#mnu_patients a:first").removeClass("collapsed");
            $("#mnugrp_patients").addClass("show");
            $("#mnu_patients_create").addClass("active");
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

            $("#select_civil_state, #select_user_type, #select_kin, #select_sex").select2({
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
            $("#select_faculty, #select_career, #select_occupation").select2({
                width: "resolve"
            });
            $("#select_familiar").select2({
                width: "calc(100% - 40px)"
            }).on("select2:opening", function(e) {
                $(this).data("select2").$dropdown.find(":input.select2-search__field").val(search_employee);
            });
            $("#btn_search_employee").on("click", function() {
                $("#select_familiar").empty().trigger("change");
                $(this).attr("disabled", true);
                $.ajax({
                    url: '/<?= BASE_URL ?>ajax.php',
                    method: 'POST',
                    data: {
                        action: "search_employees",
                        text: search_employee
                    },
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.status != 500) {
                            if (response.data.length > 0) {
                                $.each(response.data, function(each_i, each_v) {
                                    $("#select_familiar").append(new Option(each_v.value, each_v.id, false, false)).trigger("change");
                                });
                            }
                            $("#btn_search_employee").prop("disabled", false);
                        }
                    }
                });
            });
            $(document).on("keyup", "input.select2-search__field", function(e) {
                if ($(this).attr("aria-controls") == "select2-select_familiar-results") {
                    search_employee = $(this).val();
                    if ($(this).val().length >= 1) {
                        $("#btn_search_employee").prop("disabled", false);
                    } else {
                        $("#btn_search_employee").attr("disabled", true);
                        $("#select_familiar").empty().trigger("change");
                    }
                }
            });
            $("#tog_familiar").on("change", function() {
                //$("#create_form").validate().resetForm();
                if ($(this).prop("checked") == true) {
                    $("#user_data_container").css("display", "none");
                    $("#txt_alt_email").parent().find("i").css("display", "none");
                    $("#select_familiar").parent().parent().css("display", "block");
                    $("#select_kin").parent().css("display", "block");
                } else {
                    $("#user_data_container").css("display", "block");
                    $("#txt_alt_email").parent().find("i").css("display", "inline-block");
                    $("#select_familiar").parent().parent().css("display", "none");
                    $("#select_kin").parent().css("display", "none");
                    $("#select_user_type option:eq(0)").prop("selected", true).trigger("change");
                }
            });
            $("#select_user_type").on("change", function() {
                if ($(this).val() == "EM") {
                    $("#_student_container").css("display", "none");
                    $("#_employee_container").css("display", "flex");
                } else {
                    $("#_employee_container").css("display", "none");
                    $("#_student_container").css("display", "flex");
                    if ($("#select_faculty option").length > 0) {
                        $("#select_faculty option:eq(0)").prop("selected", true).trigger("change");
                    }
                }
            });
            $("#select_faculty").on("change", function() {
                $("#select_career").empty();
                $.ajax({
                    url: '/<?= BASE_URL ?>ajax.php',
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
            $("#txt_semester").on("input", function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            $("#create_form").on("submit", function(e) {
                $("#txt_familiar").val($("#tog_familiar").prop("checked"));
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
                    },
                    select_familiar: {
                        required: true
                    },
                    select_career: {
                        required: true
                    },
                    txt_semester: {
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
                    txt_alt_email: {
                        email: "Ingrese un correo válido"
                    },
                    select_familiar: {
                        required: "Este campo es requerido"
                    },
                    select_career: {
                        required: "Este campo es requerido"
                    },
                    txt_semester: {
                        required: "Este campo es requerido",
                        min: "Por favor ingrese un valor mayor o igual a 1"
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
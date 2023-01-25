<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

$doctorFunctions = new DoctorFunctions();
if ($_SESSION["dep_user_role"] != "DR" || $_SESSION["dep_user_area"] != 1) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$turnID = (!isset($_SESSION["dep_current_appointment_id"]) || empty($_SESSION["dep_current_appointment_id"])) ? null : $_SESSION["dep_current_appointment_id"];
$patientID = base64_decode(strrev(urldecode(explode("/", $_GET["patient_id"])[0])));
$patientData = $doctorFunctions->getPatientDataByID($patientID);
if (empty($patientData)) {
    header("Location: /" . BASE_URL . "manage-patients");
    exit();
}

$area = $doctorFunctions->getMyArea();
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

    $isCreated = false;
    if (!empty($turnID)) {
        $verifyTurn = $doctorFunctions->verifyTurnByID($turnID);
        if (empty($verifyTurn)) {
            $_SESSION["dep_current_appointment_id"] = null;
            $turnID = null;
        } else if ($verifyTurn->person_id != $patientID) {
            $turnID = null;
        }
    }
    $result = $doctorFunctions->insertNursingData($turnID, $patientID, $_POST["txt_weight"], $_POST["txt_pressure"], $_POST["txt_temperature"], $_POST["txt_heart_frequency"], $_POST["txt_oxygen"], $_POST["txt_height"], $_POST["txt_breathing_frequency"], $_POST["txt_imc"]);
    if ($result > 0) {
        $result = $doctorFunctions->insertMedicalHistory_by_nursing($patientID, $_POST["txt_pressure"], $_POST["txt_heart_frequency"], $_POST["txt_weight"], $_POST["txt_height"], $_POST["txt_imc"]);
        $isCreated = true;
    }

    if ($isCreated) {
?>
        <form id="responseForm" action="/<?= BASE_URL ?>patient-attention/<?= urlencode(strrev(base64_encode($patientID))) ?>" method="post">
            <!-- To set an unique ID to this post form, to avoid duplicates -->
            <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
            <input type="hidden" name="response_status" value="200">
            <input type="hidden" name="response_msg" value="Se han registrado exitosamente los datos de enfermería del paciente.">
        </form>
        <script type="text/javascript">
            document.getElementById('responseForm').submit();
        </script>
<?php
    } else {
        array_push($msg_response["errors"], "Ha ocurrido un error. Revise los datos ingresados.");
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Datos de Enfermería</title>

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
                    <h1 class="h3 mb-3">Datos de Enfermería</h1>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Registro de Datos</h5>
                                </div>
                                <div class="card-body">
                                    <form id="create_form" action="" method="post" novalidate="novalidate">
                                        <!-- To set an unique ID to this post form, to avoid duplicates -->
                                        <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                        <!--  -->
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_height" class="form-label">Estatura (m.) <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_height" name="txt_height" maxlength="20">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_weight" class="form-label">Peso (kg.) <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_weight" name="txt_weight" maxlength="20">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_temperature" class="form-label">Temperatura (C°) <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_temperature" name="txt_temperature" maxlength="20">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_pressure" class="form-label">Presión <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_pressure" name="txt_pressure" maxlength="20">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_breathing_frequency" class="form-label">Frecuencia Respiratoria <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_breathing_frequency" name="txt_breathing_frequency" maxlength="20">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_heart_frequency" class="form-label">Frecuencia Cardíaca <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_heart_frequency" name="txt_heart_frequency" maxlength="20">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_oxygen" class="form-label">Oxígeno <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_oxygen" name="txt_oxygen" maxlength="20">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_imc" class="form-label">IMC <span style="color: red;">*</span></label>
                                                    <input type="number" readonly class="form-control" id="txt_imc" name="txt_imc" maxlength="20" value="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <center><img class="img-responsive" style="width: 80%;" alt='Gráfico IMC' src="/<?= BASE_URL ?>assets/dist/img/IMC.png"></img></center>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-success">Registrar</button>
                                        <button type="button" class="btn btn-secondary" onclick="window.open('/<?= BASE_URL ?>patient-attention/<?= $_GET['patient_id'] ?>', '_self')">Volver</button>
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

            $("#txt_height, #txt_weight, #txt_temperature, #txt_pressure, #txt_breathing_frequency, #txt_heart_frequency, #txt_oxygen").on("input", function(evt) {
                if (evt.originalEvent.data !== ".") {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                }
                if ($(this).attr("id") === "txt_height" || $(this).attr("id") === "txt_weight") {
                    try {
                        let altura = parseFloat(($("#txt_height").val() == "") ? "0" : $("#txt_height").val());
                        let peso = parseFloat(($("#txt_weight").val() == "") ? "0" : $("#txt_weight").val());
                        let imc = 0;
                        if (altura != 0) {
                            imc = peso / (altura * altura);
                        }
                        imc = imc.toFixed(2);
                        $("#txt_imc").val(imc);
                    } catch (error) {
                        $("#txt_imc").val("0");
                        console.log(error);
                    }
                }
            });

            $('#create_form').validate({
                rules: {
                    /*txt_height: {
                        required: true
                    }*/
                },
                messages: {
                    /*txt_height: {
                        required: "Este campo es requerido"
                    }*/
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
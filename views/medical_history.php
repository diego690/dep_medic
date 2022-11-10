<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

$doctorFunctions = new DoctorFunctions();
if ($_SESSION["dep_user_role"] != "DR" || $_SESSION["dep_user_area"] != 2) {
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
$moduleData = $doctorFunctions->getMedicalHistoryByPatientID($patientID);

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

    $isUpdated = false;
    if (!empty($turnID)) {
        $verifyTurn = $doctorFunctions->verifyTurnByID($turnID);
        if (empty($verifyTurn)) {
            $_SESSION["dep_current_appointment_id"] = null;
            $turnID = null;
        } else if ($verifyTurn->person_id != $patientID) {
            $turnID = null;
        }
    }
    if ($doctorFunctions->existsMedicalHistoryByPatientID($patientID)) {
        $doctorFunctions->updateMedicalHistory($patientID, trim($_POST["txt_app"]), trim($_POST["txt_apf"]), trim($_POST["txt_ago"]), trim($_POST["txt_allergies"]), trim($_POST["txt_habits"]), $_POST["txt_pressure"], $_POST["txt_heart_frequency"], $_POST["txt_weight"], $_POST["txt_height"], $_POST["txt_imc"]);
        $result = 1;
    } else {
        $result = $doctorFunctions->insertMedicalHistory($patientID, trim($_POST["txt_app"]), trim($_POST["txt_apf"]), trim($_POST["txt_ago"]), trim($_POST["txt_allergies"]), trim($_POST["txt_habits"]), $_POST["txt_pressure"], $_POST["txt_heart_frequency"], $_POST["txt_weight"], $_POST["txt_height"], $_POST["txt_imc"]);
    }
    if ($result > 0) {
        $moduleData = $doctorFunctions->getMedicalHistoryByPatientID($patientID);
        $isUpdated = true;
    }

    if ($isUpdated) {
?>
        <form id="responseForm" action="/<?= BASE_URL ?>patient-attention/<?= urlencode(strrev(base64_encode($patientID))) ?>" method="post">
            <!-- To set an unique ID to this post form, to avoid duplicates -->
            <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
            <input type="hidden" name="response_status" value="200">
            <input type="hidden" name="response_msg" value="Se han registrado exitosamente los antecedentes del paciente.">
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

    <title><?= ADMIN_PANEL_NAME ?> - Antecedentes</title>

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

                    <h1 class="h3 mb-3">Antecedentes</h1>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Registrar Datos</h5>
                                </div>
                                <div class="card-body">
                                    <form id="create_form" action="" method="post" novalidate="novalidate">
                                        <!-- To set an unique ID to this post form, to avoid duplicates -->
                                        <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                        <!--  -->

                                        <div class="mb-3 form-group">
                                            <label for="txt_app" class="form-label">APP</label>
                                            <input type="text" class="form-control" id="txt_app" name="txt_app" maxlength="200" value="<?= (!empty($moduleData)) ? $moduleData->app : "" ?>">
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_apf" class="form-label">APF</label>
                                            <input type="text" class="form-control" id="txt_apf" name="txt_apf" maxlength="200" value="<?= (!empty($moduleData)) ? $moduleData->apf : "" ?>">
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_ago" class="form-label">AGO</label>
                                            <input type="text" class="form-control" id="txt_ago" name="txt_ago" maxlength="200" value="<?= (!empty($moduleData)) ? $moduleData->ago : "" ?>">
                                        </div>
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_allergies" class="form-label">Alergias</label>
                                                    <input type="text" class="form-control" id="txt_allergies" name="txt_allergies" maxlength="100" value="<?= (!empty($moduleData)) ? $moduleData->allergies : "" ?>">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_habits" class="form-label">Hábitos</label>
                                                    <input type="text" class="form-control" id="txt_habits" name="txt_habits" maxlength="100" value="<?= (!empty($moduleData)) ? $moduleData->habits : "" ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_pressure" class="form-label">PA <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_pressure" name="txt_pressure" maxlength="20" value="<?= (!empty($moduleData)) ? $moduleData->pressure : "" ?>">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_heart_frequency" class="form-label">FC <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_heart_frequency" name="txt_heart_frequency" maxlength="20" value="<?= (!empty($moduleData)) ? $moduleData->heart_frequency : "" ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_weight" class="form-label">Peso (kg.) <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_weight" name="txt_weight" maxlength="20" value="<?= (!empty($moduleData)) ? $moduleData->weight : "" ?>">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_height" class="form-label">Talla (m.) <span style="color: red;">*</span></label>
                                                    <input type="number" class="form-control" id="txt_height" name="txt_height" maxlength="20" value="<?= (!empty($moduleData)) ? $moduleData->height : "" ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_imc" class="form-label">IMC <span style="color: red;">*</span></label>
                                            <input type="number" readonly class="form-control" id="txt_imc" name="txt_imc" maxlength="20" value="<?= (!empty($moduleData)) ? $moduleData->imc : "0" ?>">
                                        </div>

                                        <button type="submit" class="btn btn-success">Guardar</button>
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

            $("#txt_height, #txt_weight, #txt_pressure, #txt_heart_frequency").on("input", function(evt) {
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
                        console.log("Error hp: "+error);
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
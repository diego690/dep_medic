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
$historyID = $doctorFunctions->getMedicalHistoryByPatientID($patientID);
if (empty($historyID)) {
    header("Location: /" . BASE_URL . "manage-patients");
    exit();
} else {
    $historyID = $historyID->id;
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
    $result = $doctorFunctions->insertMedicalConsultation($historyID, $turnID, $_POST["txt_reason"], trim($_POST["txt_head_neck"]), trim($_POST["txt_thorax"]), trim($_POST["txt_abdomen"]), trim($_POST["txt_extremities"]), trim($_POST["txt_diagnostic"]), $_POST["txt_treatment"]);
    if ($result > 0) {
        $isCreated = true;
    }

    if ($isCreated) {
?>
        <form id="responseForm" action="/<?= BASE_URL ?>patient-attention/<?= urlencode(strrev(base64_encode($patientID))) ?>" method="post">
            <!-- To set an unique ID to this post form, to avoid duplicates -->
            <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
            <input type="hidden" name="response_status" value="200">
            <input type="hidden" name="response_msg" value="Se ha registrado exitosamente el examen físico del paciente.">
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

    <title><?= ADMIN_PANEL_NAME ?> - Anamnesis y Examen Físico</title>

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

                    <h1 class="h3 mb-3">Anamnesis y Examen Físico</h1>

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
                                            <label for="txt_reason" class="form-label">Motivo de Consulta <span style="color: red;">*</span></label>
                                            <textarea class="form-control" id="txt_reason" name="txt_reason" maxlength="300"></textarea>
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_head_neck" class="form-label">Cabeza y Cuello</label>
                                            <input class="form-control" id="txt_head_neck" name="txt_head_neck" maxlength="100">
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_thorax" class="form-label">Tórax Anterior y Posterior</label>
                                            <input class="form-control" id="txt_thorax" name="txt_thorax" maxlength="100">
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_abdomen" class="form-label">Abdomen</label>
                                            <input class="form-control" id="txt_abdomen" name="txt_abdomen" maxlength="100">
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_extremities" class="form-label">Extremidades</label>
                                            <input class="form-control" id="txt_extremities" name="txt_extremities" maxlength="100">
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_diagnostic" class="form-label">Impresión Diagnóstica</label>
                                            <input class="form-control" id="txt_diagnostic" name="txt_diagnostic" maxlength="100">
                                        </div>
                                        <div class="mb-3 form-group">
                                            <label for="txt_treatment" class="form-label">Tratamiento</label>
                                            <textarea class="form-control" id="txt_treatment" name="txt_treatment" maxlength="300"></textarea>
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

            $('#create_form').validate({
                rules: {
                    txt_reason: {
                        required: true
                    }
                },
                messages: {
                    txt_reason: {
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
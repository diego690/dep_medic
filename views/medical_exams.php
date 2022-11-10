<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

$doctorFunctions = new DoctorFunctions();
if ($_SESSION["dep_user_role"] != "DR" || $_SESSION["dep_user_area"] != 2) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
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
    $result = null;
    $isCreated = false;
    $date = date("Y-m-d", strtotime($_POST["txt_date"]));

        $insertar = $_POST['select_exam'];
        $i = sizeof($_POST['select_exam']);
        //echo sizeof($_POST['select_exam']);
        //$user_id = $_POST['select_pacientesDoc'];
        for($if = 0; $if < $i; $if++) {

            echo $insertar[$if];
            $result = $doctorFunctions->insertMedicalExam($historyID, $date, $insertar[$if]);
            $isCreated=true;

        }

    /*$result = $doctorFunctions->insertMedicalEvolve($historyID, $date, $_POST["txt_evolve_notes"], $_POST["txt_prescription"]);*/
    if ($result > 0) {
        $isCreated = true;
    }

    if ($isCreated) {
        ?>
        <form id="responseForm" action="/<?= BASE_URL ?>patient-attention/<?= urlencode(strrev(base64_encode($patientID))) ?>" method="post">
            <!-- To set an unique ID to this post form, to avoid duplicates -->
            <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
            <input type="hidden" name="response_status" value="200">
            <input type="hidden" name="response_msg" value="Se ha registrado exitosamente los Examenes para el paciente.">
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

    <title><?= ADMIN_PANEL_NAME ?> - Exámenes</title>

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

                <h1 class="h3 mb-3">Evolución</h1>

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
                                        <label for="txt_date" class="form-label">Fecha <span style="color: red;">*</span></label>
                                        <input type="text" class="form-control" id="txt_date" name="txt_date" required>
                                    </div>
                                    <div class="row">
                                        <?php
                                        include_once("includes/examns/ex_hematologia.php");

                                        include_once("includes/examns/ex_infectologia.php");

                                        include_once("includes/examns/ex_inmunologia.php");

                                        include_once("includes/examns/ex_biologia_molecular.php");
                                        ?>
                                    </div>
                                    <div class="row">
                                        <?php
                                        include_once("includes/examns/ex_niv_farmacos_drogas.php");

                                        include_once("includes/examns/ex_hormonas.php");

                                        include_once("includes/examns/ex_orina.php");

                                        include_once("includes/examns/ex_heces.php");
                                        ?>
                                    </div>
                                    <div class="row">
                                        <?php
                                        include_once("includes/examns/ex_bioquimicas.php");

                                        include_once("includes/examns/ex_marcadores_tumorales.php");

                                        include_once("includes/examns/ex_esputo.php");

                                        include_once("includes/examns/ex_enzimas.php");
                                        ?>
                                    </div>
                                    <div class="row">
                                        <?php
                                        include_once("includes/examns/ex_bactereologia.php");

                                        include_once("includes/examns/ex_electrolitos.php");

                                        include_once("includes/examns/ex_gases_sanguineos.php");

                                        include_once("includes/examns/ex_otros.php")
                                        ?>
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
        $("#select_hematologia,#select_infectologia,#select_inmunologia,#select_biologiamolecular,#select_niv_farmacos_drogas").select2({
            width: "resolve"
        });
        $("#select_hormonas,#select_orina,#select_heces,#select_bioquimicas,#select_marcadores_tumor,#select_esputo,#select_enzimas").select2({
            width: "resolve"
        });
        $("#select_bacteorologia,#select_electrolitos,#select_gases,#select_otros,#select_pacientesDoc,#select_exam").select2({
            width: "resolve"
        });
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

        let a_startDate = moment();
        $("#txt_date").daterangepicker({
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
            startDate: a_startDate,
            autoUpdate: false,
            opens: 'left'
        });
        $('#txt_date').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        $('#create_form').validate({
            rules: {
                txt_date: {
                    required: true
                },
                txt_evolve_notes: {
                    required: true
                },
                txt_prescription: {
                    required: true
                }
            },
            messages: {
                txt_date: {
                    required: "Este campo es requerido"
                },
                txt_evolve_notes: {
                    required: "Este campo es requerido"
                },
                txt_prescription: {
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
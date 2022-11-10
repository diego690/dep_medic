<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../system/helpers/mail_sender.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

$doctorFunctions = new DoctorFunctions();
if ($_SESSION["dep_user_role"] != "DR" || !in_array($_SESSION["dep_user_area"], [2, 3])) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$area = $doctorFunctions->getMyArea();
if (!$area) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$settingsData = $doctorFunctions->getSettingsByAreaID($area);

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

    $updateResult = false;
    if (isset($_POST["txt_meet_link"])) {
        $doctorFunctions->updateMeetLinkByAreaID($area, trim($_POST["txt_meet_link"]));
        $settingsData = $doctorFunctions->getSettingsByAreaID($area);
        $updateResult = true;
        if (ENABLE_EMAIL_SENDING) {
            try {
                $persons = $doctorFunctions->getPatientsInvolvedInAppointmentsByAreaID($area);
                $sendTo_arr = array();
                while ($r = $persons->fetch_object()) {
                    if (!empty($r->patient_email)) {
                        $sendTo_arr[$r->patient_email] = $r->patient_name . " " . $r->patient_lastname;
                    }
                }
                if (!empty($sendTo_arr) && !empty(trim($_POST["txt_meet_link"]))) {
                    MailSender::send_mail(
                        "Dpto. Médico UTEQ",
                        $sendTo_arr,
                        "Notificación sobre cita por telemedicina",
                        "Ha sido modificado el enlace para acceder a la cita por telemedicina en la área escogida, el nuevo enlace es: <a href='" . $_POST["txt_meet_link"] . "'>" . $_POST["txt_meet_link"] . "</a>.
                        <br/><br/>
                        No responder a este correo.<br/>
                        Para mayor información contactarse a:<br/>
                        <strong>Departamento médico:</strong> medicos@uteq.edu.ec<br/>
                        <strong>Enfermería:</strong> Lcda. Gabriela Alvarez Ayala - galvareza@uteq.edu.ec<br/>
                        <strong>Medicina General:</strong> Dra. Miryam Loor Intriago - mloor@uteq.edu.ec<br/>
                        <strong>Odontología:</strong> Odontólogo. Cristhian Solano Chichande - csolano@uteq.edu.ec"
                    );
                }
            } catch (\Throwable $th) {
            }
        }
    }

    if ($updateResult) {
        array_push($msg_response["success"], "Los cambios han sido guardados.");
    } else {
        array_push($msg_response["errors"], "Los cambios no han podido ser guardados.");
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Configuración</title>

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

                    <h1 class="h3 mb-3">Configuración</h1>

                    <div class="row">
                        <div class="col-md-3 col-xl-2">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Opciones</h5>
                                </div>

                                <div class="list-group list-group-flush" role="tablist">
                                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#panel-meet-link" role="tab">
                                        Enlace de Meet
                                    </a>
                                    <!--<a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#panel-option-2" role="tab">
                                        Opción 2
                                    </a>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9 col-xl-10">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="panel-meet-link" role="tabpanel">

                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Enlace de Meet</h5>
                                        </div>
                                        <div class="card-body">
                                            <form id="meet_link_form" enctype="multipart/form-data" action="settings" method="post" novalidate="novalidate">
                                                <!-- To set an unique ID to this post form, to avoid duplicates -->
                                                <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                                <!--  -->

                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="mb-3 form-group">
                                                            <label for="txt_meet_link" class="form-label">Enlace</label>
                                                            <input type="text" class="form-control" id="txt_meet_link" name="txt_meet_link" placeholder="" value="<?= $settingsData->meet_link ?>">
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-success">Guardar cambios</button>
                                            </form>

                                        </div>
                                    </div>

                                </div>
                                <!--<div class="tab-pane fade" id="panel-option-2" role="tabpanel">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Opción 2</h5>

                                        </div>
                                    </div>
                                </div>-->
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
            $("#mnu_settings").addClass("active");
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

            $('#meet_link_form').validate({
                rules: {
                    /*txt_meet_link: {
                        required: true
                    }*/
                },
                messages: {
                    /*txt_meet_link: {
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
<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/helpers/mail_sender.php");
require_once(__DIR__ . "/../data/mysql/users.functions.php");
require_once(__DIR__ . "/../data/mysql/auth.functions.php");

if (is_logged()) {
    header("Location: home");
    return;
}

$userFunctions = new UsersFunctions();
$authFunctions = new AuthFunctions();

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

    if ($userFunctions->existsUser($_POST["txt_email"])) {
        $user = $userFunctions->getUserByUsername($_POST["txt_email"]);
        $newCode = urlencode(strrev(base64_encode(gen_uuid())));
        if ($authFunctions->existsRestorePasswordCodeByUserID($user->user_id)) {
            $authFunctions->updateRestorePasswordCodeByUserID($user->user_id, $newCode);
        } else {
            $authFunctions->insertRestorePasswordCode($user->user_id, $newCode);
        }
        if (SMTP_USERNAME != "" && SMTP_PASSWORD != "") {
            $sendTo = $_POST["txt_email"];
            $result = MailSender::send_mail(
                "Dpto. Médico UTEQ",
                array(
                    $sendTo => "Usuario: " . $user->name . " " . $user->last_name
                ),
                "Restablecer contraseña",
                "Para restablecer su contraseña, haga click en el siguiente <a target=\"_blank\" href=\"" . trim($_SERVER['HTTP_HOST']) . "/" . BASE_URL . "restore-password?code=" . $newCode . "&email=" . $_POST["txt_email"] . "\">vínculo</a>. Expirará en 10 minutos
                
                Si no ha solicitado el restablecimiento de su contraseña, simplemente ignore este correo electrónico. No se efectuará ningún cambio en su cuenta.
                <br/><br/>
                No responder a este correo.<br/>
                Para mayor información contactarse a:<br/>
                <strong>Departamento médico:</strong> medicos@uteq.edu.ec<br/>
                <strong>Enfermería:</strong> Lcda. Gabriela Alvarez Ayala - galvareza@uteq.edu.ec<br/>
                <strong>Medicina General:</strong> Dra. Miryam Loor Intriago - mloor@uteq.edu.ec<br/>
                <strong>Odontología:</strong> Odontólogo. Cristhian Solano Chichande - csolano@uteq.edu.ec"
            );
            if ($result) {
                array_push($msg_response["success"], "Se ha enviado un mensaje a su correo: " . $_POST["txt_email"] . " con el enlace de confirmación. Expirará en 10 minutos");
            } else {
                array_push($msg_response["errors"], "No se ha podido enviar el mensaje a su correo. Por favor, intente más tarde");
            }
        } else {
            array_push($msg_response["errors"], "En este momento no se encuentra habilitada esta opción");
        }
    } else {
        array_push($msg_response["errors"], "El correo ingresado no se encuentra registrado en nuestro sistema");
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Restablecer Contraseña</title>

    <link rel="shortcut icon" href="/<?= BASE_URL ?>assets/dist/img/logo.png" sizes="32x32">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

    <link class="js-stylesheet" href="/<?= BASE_URL ?>assets/auth-plugins/css/light.css" rel="stylesheet">
    <link href="/<?= BASE_URL ?>assets/plugins/toastr/toastr.min.css" rel="stylesheet">
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-behavior="sticky">
    <div class="main d-flex justify-content-center w-100">
        <main class="content d-flex p-0">
            <div class="container d-flex flex-column">
                <div class="row h-100">
                    <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
                        <div class="d-table-cell align-middle">

                            <div class="text-center mt-4">
                                <h1 class="h2">Restablecer Contraseña</h1>
                                <p class="lead">
                                    Introduce tu email para realizar el cambio de contraseña.
                                </p>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="m-sm-4">
                                        <form id="forgot_pwd_form" action="forgot-password" method="post">
                                            <!-- To set an unique ID to this post form, to avoid duplicates -->
                                            <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                            <!--  -->

                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input class="form-control form-control-lg" type="email" id="txt_email" name="txt_email" placeholder="" value="<?= (isset($_GET["email"])) ? $_GET["email"] : "" ?>" />
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="button" onclick="sendEmail();" class="btn btn-lg btn-success">Restablecer contraseña</button>
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

    <script>
        function sendEmail() {
            if ($("#txt_email").val().length == 0) {
                $("#txt_email").focus();
                return false;
            }
            $("#forgot_pwd_form").submit();
        }

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
        });
    </script>

</body>

</html>
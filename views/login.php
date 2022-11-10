<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../data/mysql/users.functions.php");

if (is_logged()) {
    header("Location: home");
    return;
}

$errors = [];

//Validate to avoid POST duplicates
$post_validation_session = (isset($_SESSION['post_id'])) ? $_SESSION['post_id'] : "";
$post_validation_form = (isset($_POST['post_id'])) ? $_POST['post_id'] : "";
$is_post = (count($_POST) > 0) && ($post_validation_session != $post_validation_form);

if ($is_post) {
    $_SESSION['post_id'] = $_POST['post_id'];
    $errors = [];

    if (!isset($_POST['txt_email']) || !$_POST['txt_email']) {
        $errors['username'] = 'El email es un campo obligatorio.';
    }

    if (!isset($_POST['txt_pwd']) || !$_POST['txt_pwd']) {
        $errors['password'] = 'La contraseña es un campo obligatorio.';
    }

    if (!count($errors)) {
        $userFunctions = new UsersFunctions();
        $user = $userFunctions->getUserByCredentials($_POST['txt_email'], $_POST['txt_pwd']);

        if (!$user) {
            $errors['username'] = 'Login fallido, credenciales incorrectas';
        } else {
            $_SESSION['dep_logged_in'] = true;
            $_SESSION['dep_ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['dep_user_id'] = $user->user_id;
            $_SESSION['dep_user_name'] = $user->name;
            $_SESSION['dep_user_lastname'] = $user->last_name;
            $_SESSION['dep_user_username'] = $user->username;
            $_SESSION['dep_user_identification'] = $user->identification;
            $_SESSION['dep_user_avatar'] = $user->avatar;
            $_SESSION['dep_user_role'] = $user->role;
            $_SESSION['dep_user_area'] = null;
            $_SESSION['dep_user_is_employee'] = null;
            if ($user->role == "US") {
                $_SESSION['dep_user_is_employee'] = $userFunctions->isUserAnEmployee($user->user_id);
            }
            if ($user->role == "DR") {
                $_SESSION['dep_user_area'] = getMyArea();
            }
            $_SESSION['dep_current_appointment_id'] = null;
            //

            header('Location: home');
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

    <title><?= ADMIN_PANEL_NAME ?> - Login</title>

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
                                <h1 class="h2">Bienvenid@ al Departamento Médico UTEQ</h1>
                                <p class="lead">
                                    Inicia sesión
                                </p>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="m-sm-4">
                                        <div class="text-center">
                                            <img src="/<?= BASE_URL ?>assets/dist/img/logo.png" alt="UTEQ" class="img-fluid" width="132" height="132" />
                                        </div>
                                        <form id="login_form" action="login" method="post">
                                            <!-- To set an unique ID to this post form, to avoid duplicates -->
                                            <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                            <!--  -->

                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input class="form-control form-control-lg" type="email" id="txt_email" name="txt_email" maxlength="100" placeholder="" />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Contraseña</label>
                                                <input class="form-control form-control-lg" type="password" id="txt_pwd" name="txt_pwd" maxlength="100" placeholder="" />
                                                <small>
                                                    ¿Has olvidado tu contraseña? <a href="forgot-password">Clic aquí!</a>
                                                </small><br />
                                                <small class="mt-2">
                                                    ¿Eres estudiante de primer semestre? <a href="register">Regístrate!</a>
                                                </small>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button onclick="login()" type="button" class="btn btn-lg btn-success">Entrar</button>
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
        function login() {
            if ($("#txt_email").val().length == 0) {
                $("#txt_email").focus();
                return false;
            }
            if ($("#txt_pwd").val().length == 0) {
                $("#txt_pwd").focus();
                return false;
            }
            $("#login_form").submit();
        }

        $(document).on("keydown", function(e) {
            var keyCode = e.which || e.keyCode;
            if (keyCode == 13)
            {
                login();
            }
        });

        $(document).ready(function() {
            <?php if (count($errors)) {
                foreach ($errors as $error) {
                    echo "toastr.error('{$error}');";
                }
            } ?>
        });
    </script>

</body>

</html>
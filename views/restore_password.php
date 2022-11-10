<?php
require_once(__DIR__ . "/../system/loader.php");
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

if (!isset($_GET["code"]) || !isset($_GET["email"])) {
    array_push($msg_response["errors"], "Error al momento de recibir los datos, haga clic en el botón para reenviar el código nuevamente.");
} else {
    $codeInfo = $authFunctions->getRestorePasswordCodeByCode($_GET["code"], $_GET["email"]);
    if ($codeInfo->num_rows == 0) {
        array_push($msg_response["errors"], "El código que está utilizando no es válido, haga clic en el botón para reenviar el código nuevamente.");
    } else {
        $codeInfo = $codeInfo->fetch_object();
        $expires_at = $codeInfo->expires_at;
        $now = getCurrentTimestamp();
        if (strtotime($now) > strtotime($expires_at)) {
            array_push($msg_response["errors"], "Su código ha expirado, haga clic en el botón para reenviar el código nuevamente.");
        } else {
            $user = $userFunctions->getUserByUsername($_GET["email"]);
            $userFunctions->changePassword($user->user_id, $user->identification);
            $authFunctions->deleteRestorePasswordCodeByUserID($user->user_id);
            array_push($msg_response["success"], "Restauración completa, ingrese a su cuenta con su número de cédula como contraseña.");
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
                                <h1 class="h2">Contraseña<?= (count($msg_response["errors"]) > 0) ? " No " : " " ?>Actualizada</h1>
                                <p class="lead">
                                    <?= (count($msg_response["errors"]) > 0) ? $msg_response["errors"][0] : $msg_response["success"][0] ?>
                                </p>
                                <a href="<?= (count($msg_response["errors"]) > 0) ? "forgot-password".((isset($_GET["email"])) ? "?email=".$_GET["email"] : "") : "login" ?>" class="btn btn-lg btn-<?= (count($msg_response["errors"]) > 0) ? "info" : "success" ?> mt-3"><?= (count($msg_response["errors"]) > 0) ? "Reintentar" : "Continuar" ?></a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/<?= BASE_URL ?>assets/auth-plugins/js/app.js"></script>

</body>

</html>
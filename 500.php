<?php
require_once(__DIR__ . "/system/loader.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>500 Error</title>

    <link rel="shortcut icon" href="/<?= BASE_URL ?>assets/dist/img/logo.png" sizes="32x32">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

    <link class="js-stylesheet" href="/<?=BASE_URL?>assets/auth-plugins/css/light.css" rel="stylesheet">
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-behavior="sticky">
    <div class="main d-flex justify-content-center w-100">
        <main class="content d-flex p-0">
            <div class="container d-flex flex-column">
                <div class="row h-100">
                    <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
                        <div class="d-table-cell align-middle">

                            <div class="text-center">
                                <h1 class="display-1 fw-bold">500</h1>
                                <p class="h1">Error interno del servidor.</p>
                                <p class="h2 fw-normal mt-3 mb-4">El server encontró algo inesperado que no permitió completar la petición. Inténtelo más tarde.</p>
                                <a href="/<?=BASE_URL?>home" class="btn btn-success btn-lg">Regresar a Inicio</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/<?=BASE_URL?>assets/auth-plugins/js/app.js"></script>

</body>

</html>
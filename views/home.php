<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
//require_once(__DIR__ . "/../data/mysql/users.functions.php");

//$userFunctions = new UsersFunctions();

$msg_response = array(
    "errors" => array(),
    "success" => array()
);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Inicio</title>

    <?php
    include_once("includes/styles.php");
    ?>

</head>
<!--
  HOW TO USE: 
  data-theme: default (default), colored, dark, light
  data-layout: fluid (default), boxed
  data-sidebar-position: left (default), right
  data-sidebar-behavior: sticky (default), fixed, compact
-->

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

                    <h1 class="h3 mb-3">Inicio</h1>

                    <?php
                    if ($_SESSION["dep_user_role"] == "AD") {
                        include_once("includes/dashboard/admin.php");
                    } else if ($_SESSION["dep_user_role"] == "US") {
                        include_once("includes/dashboard/user.php");
                    } else if ($_SESSION["dep_user_role"] == "DR"){
                        
                        include_once("includes/dashboard/doctor.php");
                    }
                        
                    ?>

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
            $("#mnu_home").addClass("active");
            $("[data-toggle=tooltip]").mouseenter(function() {
                $(this).tooltip('show');
            });
        });
    </script>

</body>

</html>
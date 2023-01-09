<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");

$doctorFunctions = new DoctorFunctions();
if ($_SESSION["dep_user_role"] != "DR" || !in_array($_SESSION["dep_user_area"], [2, 3])) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
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
    $uuid = gen_uuid();
    $result = $doctorFunctions->insertDiagnosis($patientID);
    if($result>0){

            $details = json_decode($_POST["diagnosis_details"]);
            foreach ($details as $detail){

                $result= $doctorFunctions->insertDiagnosisDetails($detail->product);
               // print ($detail->quantity);
            }
           if($result > 0){
                $isCreated= true;
            }
    }

    if ($isCreated) {
        ?>
        <form id="responseForm" action="/<?= BASE_URL ?>patient-attention/<?= urlencode(strrev(base64_encode($patientID))) ?>" method="post">
            <!-- To set an unique ID to this post form, to avoid duplicates -->
            <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
            <input type="hidden" name="response_status" value="200">
            <input type="hidden" name="response_msg" value="Se ha registrado exitosamente la receta para el paciente.">
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

    <title><?= ADMIN_PANEL_NAME ?> - Diagnosticos</title>

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

                <h1 class="h3 mb-3">Diagnosticos</h1>

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
                                    <input type='hidden' id="diagnosis_details" name='diagnosis_details' value=''>


                                    <div class="row">
                                        <div class="col-9">
                                            <div class="mb-3 form-group">
                                                <label for="select_diagnosis" class="form-label">Diagnosticos CIE10 <span style="color: red;">*</span></label>
                                                <select class="form-control" id="select_diagnosis" name="select_diagnosis" style="width: 100%;">

                                                </select>
                                                <small id="cie10" class="text-success" style="font-size: 18px;"></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button id="btn_add" type="button" class="btn btn-info mx-1" style="float: right;">Agregar</button>
                                            <button id="btn_clear" type="button" class="btn btn-danger mx-1" style="float: right;">Limpiar</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                           <label class="cie10"></label>
                                        </div>
                                    </div>
                                    <hr />
                                    <div class="row">
                                        <div class="col-12">
                                            <table id="tb_details" class="table">
                                                <thead>
                                                <tr>
                                                    <th class="d-none d-md-table-cell" style="width: 40%">ID</th>
                                                    <th class="d-none d-md-table-cell">CIE10</th>
                                                    <th class="d-none d-md-table-cell">CIE20</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success mt-4">Registrar</button>
                                    <button type="button" class="btn btn-secondary mt-4" onclick="window.open('/<?= BASE_URL ?>patient-attention/<?= $_GET['patient_id'] ?>', '_self')">Volver</button>
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
    var codigo = "";
    function clearComponents() {

        $("#select_diagnosis").val("").trigger("change");
        $("#select_diagnosis").parent().find("small").text("");
    }

    function removeRow(obj) {
        $(obj).closest("tr").remove();
    }

    function addToTable(data) {
        let rowColor = "success";
        $("#tb_details tbody").append(`
                    <tr class="table-` + rowColor + `">
                        <td class="text-center">` + data.product + `</td>
                        <td class="text-center">` + data.quantity + `</td>
                        <td class="text-center">` + data.productID + `</td>

                        <td class="table-action">
                            <center><a href="javascript:;" class="text-danger" onclick="removeRow(this);"><i class="fa fa-trash"></i></a></center>
                        </td>
                    </tr>
                `);
    }

    $(document).ready(function () {
        $("[data-toggle=tooltip]").mouseenter(function () {
            $(this).tooltip('show');
        });

        <?php
        if (count($msg_response["errors"])) {
            foreach ($msg_response["errors"] as $error) {
                echo "toastr.error('{$error}');";
            }
        }
        if (count($msg_response["success"])) {
            foreach ($msg_response["success"] as $success) {
                echo "toastr.success('{$success}');";
            }
        }
        ?>

        //init
        $("#select_diagnosis").select2({
            ajax: {
                url: "/<?= BASE_URL ?>ajax.php",
                method: "POST",
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        action: "search_diagnosis",
                        q: params.term
                    }
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                qty: item.qty,
                                id: item.id,
                                text: item.text
                            }
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            width: "resolve"
        });



        //events

        $("#select_diagnosis").on("select2:select", function (e) {
            let data = e.params.data;

            $("#select_diagnosis option[value=" + data.id + "]").data('text', data.text);
            $("#select_diagnosis").trigger('change');
            $("#select_diagnosis").parent().find("small").text("Código CIE10: " + data.qty);
           let details= {
                product: (data.id),
                productID: (data.qty),
                quantity: (data.text)
            };
            addToTable(details);
        });
        $("#btn_clear").on("click", function () {
            clearComponents();
        });
        ///abre
        $("#btn_add").on("click", function () {
            let details = {
                productID:  $("#select_diagnosis option:selected").text(),
                quantity:  $("#select_diagnosis").val()
            };
            let existsInTable = false;
            $("#tb_details tbody tr").each(function () {
                let productID = $(this).find("textarea").val();
                if (details.productID == productID) {
                    existsInTable = true;
                    return;
                }
            });
            if (existsInTable) {
                toastr.error("El producto ya está ingresado");
                return false;
            }

            addToTable(details);
            clearComponents();
        });
        ///cierre
        $('#create_form').on("submit", function (e) {
            if ($("#tb_details tbody tr").length > 0) {
                let diagnosis_details = [];
                $("#tb_details tbody tr").each(function () {
                    let details = {
                        product:$(this).children("td:eq(0)").text(),
                        productID: $(this).children("td:eq(1)").text(),
                        quantity: $(this).children("td:eq(2)").text(),

                    }
                    diagnosis_details.push(details);
                });
                $("#diagnosis_details").val(JSON.stringify(diagnosis_details));
            } else {
                toastr.error("No ha ingresado nada en el detalle.");
            }
        });
    });
</script>

</body>

</html>
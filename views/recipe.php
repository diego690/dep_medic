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
    $result = $doctorFunctions->insertRecipe($uuid, $patientID);
    if ($result > 0) {
        try {
            $details = json_decode($_POST["recipe_details"]);
            foreach ($details as $detail) {
                $result = $doctorFunctions->insertRecipeDetails($uuid, $detail->product, $detail->quantity, $detail->indications, $detail->kit_quantity);
                $doctorFunctions->decreaseProductStockByID($detail->product, $detail->kit_quantity);
            }

            if ($result > 0) {
                $isCreated = true;
            } else {
                $doctorFunctions->deleteRecipe($uuid);
            }
        } catch (\Throwable $th) {
            $doctorFunctions->deleteRecipe($uuid);
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

    <title><?= ADMIN_PANEL_NAME ?> - Receta</title>

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

                <h1 class="h3 mb-3">Receta</h1>

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
                                    <input type='hidden' id="recipe_details" name='recipe_details' value=''>

                                    <div class="row">
                                        <div class="col-3">
                                            <div class="mb-3 form-group">
                                                <label for="select_product_type" class="form-label">Fuente</label>
                                                <select class="form-control" id="select_product_type" name="select_product_type" placeholder="Seleccione de dónde proviene el producto" style="width: 100%;">
                                                    <option value="1">Desde el botiquín</option>
                                                    <option value="2">Producto nuevo</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-9">
                                            <div class="mb-3 form-group" id="container_product_1">
                                                <label for="select_product" class="form-label">Producto <span style="color: red;">*</span></label>
                                                <select class="form-control" id="select_product" name="select_product" style="width: 100%;">

                                                </select>
                                                <small class="text-success"></small>
                                            </div>
                                            <div class="mb-3 form-group" id="container_product_2" style="display: none;">
                                                <label for="select_product2" class="form-label">Producto <span style="color: red;">*</span></label>
                                                <select class="form-control" id="select_product2" name="select_product2" style="width: 100%;">

                                                </select>
                                                <small class="text-success"></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3 form-group">
                                                <label for="txt_quantity" class="form-label">Cantidad <span style="color: red;">*</span></label>
                                                <input type="text" class="form-control" id="txt_quantity" name="txt_quantity" value="1">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3 form-group" id="container_quantity_1">
                                                <label for="txt_kit_quantity" class="form-label">Cantidad a extraer del botiquín <span style="color: red;">*</span></label>
                                                <input type="text" class="form-control" id="txt_kit_quantity" name="txt_kit_quantity" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3 form-group">
                                                <label for="txt_indications" class="form-label">Indicaciones <span style="color: red;">*</span></label>
                                                <textarea class="form-control" id="txt_indications" name="txt_indications" maxlength="200"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <button id="btn_add" type="button" class="btn btn-info mx-1" style="float: right;">Agregar</button>
                                            <button id="btn_clear" type="button" class="btn btn-danger mx-1" style="float: right;">Limpiar</button>
                                        </div>
                                    </div>
                                    <hr />
                                    <div class="row">
                                        <div class="col-12">
                                            <table id="tb_details" class="table">
                                                <thead>
                                                <tr>
                                                    <th style="width: 30%;">Producto</th>
                                                    <th class="d-none d-md-table-cell" style="width: 40%">Indicaciones</th>
                                                    <th style="width: 10%">Cantidad</th>
                                                    <th style="width: 10%">Cantidad (Botiquín)</th>
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
    function clearComponents() {
        $("#select_product_type").val("1").trigger("change");
        $("#select_product").val("").trigger("change");
        $("#select_product2").val("").trigger("change");
        $("#txt_product").val("");
        $("#txt_quantity").val("1");
        $("#txt_kit_quantity").val("0");
        $("#txt_indications").val("");
        $("#select_product").parent().find("small").text("");
    }

    function removeRow(obj) {
        $(obj).closest("tr").remove();
    }

    function addToTable(data) {
        let rowColor = "success";
        if (data.type == "2") {
            rowColor = "primary";
        }
        $("#tb_details tbody").append(`
                    <tr class="table-` + rowColor + `">
                        <td><textarea style="display: none;">` + data.productID + `</textarea>` + data.product + `</td>
                        <td class="d-none d-md-table-cell">` + data.indications + `</td>
                        <td class="text-center">` + data.quantity + `</td>
                        <td class="text-center">` + data.kit_quantity + `</td>
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
        $("#select_product").select2({
            ajax: {
                url: "/<?= BASE_URL ?>ajax.php",
                method: "POST",
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        action: "search_product",
                        q: params.term
                    }
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.text,
                                id: item.id,
                                qty: item.qty
                            }
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            width: "resolve"
        });
        $("#select_product2").select2({
            ajax: {
                url: "/<?= BASE_URL ?>ajax.php",
                method: "POST",
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        action: "search_product2",
                        q: params.term
                    }
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.text,
                                id: item.id,
                                qty: item.qty
                            }
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            width: "resolve"
        });
        $("#select_product_type").select2({
            width: "resolve",
            minimumResultsForSearch: -1
        });
        $("#txt_quantity, #txt_kit_quantity").inputFilter(function (value) {
            return /^\d*$/.test(value);
        });

        //events
        $("#select_product_type").on("change", function () {
            if ($(this).val() == "1") {
                $("#container_product_2").hide();
                $("#container_product_1").show();
                $("#container_quantity_1").show();
            } else {
                $("#container_product_1").hide();
                $("#container_quantity_1").hide();
                $("#container_product_2").show();
            }
        });
        $("#select_product").on("select2:select", function (e) {
            let data = e.params.data;
            $("#select_product option[value=" + data.id + "]").data('qty', data.qty);
            $("#select_product").trigger('change');
            $("#select_product").parent().find("small").text("Disponible: " + data.qty);
        });
        $("#select_product2").on("select2:select", function (e) {
            let data = e.params.data;
            $("#select_product option[value=" + data.id + "]").data('qty', data.qty);
            $("#select_product").trigger('change');
            $("#select_product").parent().find("small").text("Disponible: " + data.qty);
        });
        $("#btn_clear").on("click", function () {
            clearComponents();
        });
        $("#btn_add").on("click", function () {
            let details = {
                type: $("#select_product_type").val(),
                product: ($("#select_product_type").val() == "1") ? $("#select_product option:selected").text() : $.trim($("#select_product2 option:selected").text()),
                productID: ($("#select_product_type").val() == "1") ? $("#select_product").val() : $.trim($("#select_product2 option:selected").text()),
                quantity: ($("#txt_quantity").val() == "") ? 0 : parseInt($("#txt_quantity").val()),
                kit_quantity: ($("#txt_kit_quantity").val() == "") ? 0 : parseInt($("#txt_kit_quantity").val()),
                indications: $("#txt_indications").val()
            };
            if (details.product == "" || details.indications == "") {
                toastr.error("Ingrese todos los datos");
                return false;
            }
            if (details.quantity < 1) {
                toastr.error("La cantidad debe ser mayor a cero");
                return false;
            }
            if ($("#select_product_type").val() == "1") {
                let kit_quantity = $("#select_product option:selected").data("qty");
                if (details.kit_quantity > details.quantity || details.kit_quantity > kit_quantity) {
                    toastr.error("La cantidad a entregar del botiquín supera los límites");
                    return false;
                }
            }
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

        $('#create_form').on("submit", function (e) {
            if ($("#tb_details tbody tr").length > 0) {
                let recipe_details = [];
                $("#tb_details tbody tr").each(function () {
                    let details = {
                        product: $(this).find("textarea").val(),
                        indications: $(this).children("td:eq(1)").text(),
                        quantity: $(this).children("td:eq(2)").text(),
                        kit_quantity: $(this).children("td:eq(3)").text()
                    }
                    recipe_details.push(details);
                });
                $("#recipe_details").val(JSON.stringify(recipe_details));
            } else {
                toastr.error("No ha ingresado nada en el detalle.");
            }
        });
    });
</script>

</body>

</html>
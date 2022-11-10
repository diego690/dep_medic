<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/us.functions.php");

$usFunctions = new usFunctions();
if ($_SESSION["dep_user_role"] != "US") {
    header("Location: /" . BASE_URL . "home");
    exit();
}

$msg_response = array(
    "errors" => array(),
    "success" => array()
);

$loggedPersonID = $usFunctions->getMyPersonID();

//Validate to avoid POST duplicates
$post_validation_session = (isset($_SESSION['post_id'])) ? $_SESSION['post_id'] : "";
$post_validation_form = (isset($_POST['post_id'])) ? $_POST['post_id'] : "";
$is_post = (count($_POST) > 0) && ($post_validation_session != $post_validation_form);

if ($is_post) {
    $_SESSION['post_id'] = $_POST['post_id'];

    if ($usFunctions->isAppointmentRequestLimited()) {
        array_push($msg_response["errors"], "Ya ha superado el límite de solicitudes disponibles.");
    } else {
        $isCreated = false;
        $personID = (isset($_POST["select_person"]) ? base64_decode(strrev(urldecode($_POST["select_person"]))) : $usFunctions->getMyPersonID());
        $areaID = base64_decode(strrev(urldecode($_POST["txt_area"])));
        $result = $usFunctions->insertRequestAppointment($areaID, $personID, $_POST["txt_date"], $_POST["txt_init_time"], 'CR', $_POST["txt_type"], $_POST["txt_description"]);
        if ($result > 0) {
            $isCreated = true;
        }
        if ($isCreated) {
            array_push($msg_response["success"], "La solicitud ha sido registrada exitosamente.");
        } else {
            array_push($msg_response["errors"], "La solicitud no ha podido ser registrada.");
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

    <title><?= ADMIN_PANEL_NAME ?> - Solicitar Cita</title>

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

                    <nav aria-label="breadcrumb" style="float: right;">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/<?= BASE_URL ?>home">Inicio</a></li>
                            <li class="breadcrumb-item active">Agregar una Cita</li>
                        </ol>
                    </nav>

                    <h1 class="h3 mb-3">Solicitar Cita</h1>
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="alert-icon">
                            <i class="fas fa-fw fa-info"></i>
                        </div>
                        <div class="alert-message">
                            <strong>
                                Solo podrá agendar una cita por persona<br />
                                <br />> En el caso de seleccionar presencial, acercarse 15 minutos antes al dpto médico.
                                <br />> En el caso de seleccionar telemedicina, se le enviará un enlace al correo registrado.
                            </strong>
                        </div>
                    </div>
                    <?php if ($usFunctions->isAppointmentRequestLimited()) { ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <div class="alert-icon">
                                <i class="fas fa-fw fa-exclamation-triangle"></i>
                            </div>
                            <div class="alert-message">
                                <strong>
                                    Ya tiene varias solicitudes pendientes, ha superado el límite de solicitudes disponibles.
                                </strong>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Datos de la Cita</h5>
                                </div>
                                <div class="card-body">
                                    <form id="create_form" action="request-appointment" method="post" novalidate="novalidate">
                                        <!-- To set an unique ID to this post form, to avoid duplicates -->
                                        <input type='hidden' name='post_id' value='<?= gen_uuid() ?>'>
                                        <!--  -->

                                        <?php if ($_SESSION["dep_user_is_employee"] == true) { ?>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="mb-3 form-group">
                                                        <label for="select_person" class="form-label" style="padding: 10px; text-align: center; font-weight: 700; font-size: 16px; color: #5d6986; width: 100%;">¿Para quién es la cita? <span style="color: red;">*</span></label>
                                                        <select class="form-select" id="select_person" name="select_person" style="width: 100%;" required>
                                                            <option value="<?= urlencode(strrev(base64_encode($loggedPersonID))) ?>" <?= ($usFunctions->existsActiveAppointmentByPersonID($loggedPersonID)) ? "style='color: red;'" : "" ?>>Para mí</option>
                                                            <?php
                                                            $familiars = $usFunctions->getMyFamiliars("");
                                                            while ($row = $familiars->fetch_object()) {
                                                            ?>
                                                                <option value="<?= urlencode(strrev(base64_encode($row->id))) ?>" <?= ($usFunctions->existsActiveAppointmentByPersonID($row->id)) ? "style='color: red;'" : "" ?>><?= $row->name . " " . $row->last_name . " (" . $row->kin . ")" ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <small class="form-text d-block text-muted" style="color: red !important;">Si el nombre se encuentra de color rojo, ya existe solicitud o tiene una cita asignada.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="row div-type-selector">
                                            <h4>Tipo de consulta <span style="color: red;">*</span></h4>
                                            <div>
                                                <button type="button" class="active" data-value="P">
                                                    Presencial<br>🧑‍⚕️
                                                </button>

                                                <button type="button" data-value="T">
                                                    Teleconsulta<br>🧑‍💻
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row div-area-selector">
                                            <h4>Area <span style="color: red;">*</span></h4>
                                            <div>
                                                <button type="button" class="active" data-value="iZzMxITN5gTZ4QGNtYmYmhTL3gjZ00iMklTOtUGOkRGOzgDO">
                                                    Campus Central <br>Medicina
                                                </button>

                                                <button type="button" data-value="hRTZlZTYyIWMiRGMtM2NxkTLwUTN00iMzkTNtMzMkFDZjNDZ">
                                                    Campus Central <br>Odontología
                                                </button>

                                                <button type="button" data-value="hZWY5IGM4AzMjF2YtMzNxgTLldDZ00iMhRjNtIzNxIDNlJzM">
                                                    Campus La María <br>Medicina
                                                </button>

                                                <button type="button" data-value="0cTZlJTYlZmZidDOtkTNzgTL1kDM00yMmRjZtgzY1UWYhZDN">
                                                    Campus La María <br>Odontología
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3 form-group">
                                                    <label for="txt_description" class="form-label" style="padding: 10px; text-align: center; font-weight: 700; font-size: 16px; color: #5d6986; width: 100%;">Motivo de consulta <span style="color: red;">*</span></label>
                                                    <textarea class="form-control" id="txt_description" name="txt_description" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row div-datetime-selector">
                                            <h4>Selecciona un día</h4>
                                            <div class="div-date-selector">
                                                <p><?= ucfirst(strftime("%B %Y", strtotime(getCurrentTimestamp()))) ?></p>
                                                <ul>
                                                    <?php
                                                    $currentTimeStamp = new DateTime(getCurrentTimestamp());
                                                    $currentDate = $currentTimeStamp->format('Y-m-d');
                                                    $oneDay = new DateInterval("P1D");
                                                    for ($i = 0; $i < 7; $i++) {
                                                    ?>
                                                        <li role="button" class="weekday <?= ($i == 0) ? "selected" : "" ?> disabled" data-value="<?= $currentTimeStamp->format('Y-m-d') ?>" data-day="<?= strftime("%u", strtotime($currentTimeStamp->format('Y-m-d H:i:s'))) ?>">
                                                            <span>
                                                                <?= utf8_encode(strftime("%a", strtotime($currentTimeStamp->format('Y-m-d H:i:s')))) ?>
                                                            </span>
                                                            <?= ucfirst(strftime("%b %d", strtotime($currentTimeStamp->format('Y-m-d H:i:s')))) ?>
                                                        </li>
                                                    <?php
                                                        $currentTimeStamp->add($oneDay);
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                            <h4>Selecciona una hora</h4>
                                            <ul class="div-time-selector">
                                                <span>
                                                    El área no está disponible durante esta fecha.
                                                </span>
                                            </ul>
                                        </div>

                                        <input type='hidden' id="txt_type" name="txt_type" value="P">
                                        <input type='hidden' id="txt_area" name="txt_area" value="iZzMxITN5gTZ4QGNtYmYmhTL3gjZ00iMklTOtUGOkRGOzgDO">
                                        <input type='hidden' id="txt_date" name="txt_date" value="<?= $currentDate ?>">
                                        <input type='hidden' id="txt_init_time" name="txt_init_time" value="">
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
    include_once("modals/request_appointment.php");
    include_once("includes/scripts.php");
    ?>

    <script>
        var _hours = null,
            _duration = null;

        function loadAvailableSchedule() {
            $("div.div-date-selector li").addClass("disabled");
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                async: false,
                method: 'POST',
                data: {
                    action: "load_available_schedule",
                    type: $("#txt_type").val(),
                    area: $("#txt_area").val()
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        $.each(response.data.days, function(each_i, each_v) {
                            $("div.div-date-selector").find("li[data-day=" + each_v + "]").removeClass("disabled");
                        });
                        if ($("div.div-date-selector li.selected").length == 0 || $("div.div-date-selector li.selected").hasClass("disabled")) {
                            $("div.div-date-selector li.selected").removeClass("selected");
                            $("#txt_date").val("");
                            $("#txt_init_time").val("");
                            $("ul.div-time-selector").empty();
                            _hours = response.data.hours;
                            _duration = response.data.duration;
                        } else {
                            setAvailableHours(response.data.hours, response.data.duration);
                        }
                    }
                }
            });
        }

        function addZ(n) {
            return (n < 10) ? '0' + n : '' + n;
        }

        function setAvailableHours(hours, duration) {
            _hours = hours;
            _duration = duration;
            $("ul.div-time-selector").empty();
            if (hours.length == 0 || parseInt(timeToSeconds(hours[0].start)) >= parseInt(timeToSeconds(hours[0].end))) {
                $("ul.div-time-selector").append(`
                    <span>
                        El área no está disponible durante esta fecha.
                    </span>
                `);
            } else {
                for (let i = parseInt(timeToSeconds(hours[0].start)); i <= parseInt(timeToSeconds(hours[0].end)); i += duration) {
                    //console.log(new Date(i * 1000).toISOString().substr(11, 8));
                    $("ul.div-time-selector").append(`
                        <li>
                            ` + new Date(i * 1000).toISOString().substr(11, 5) + `
                        </li>
                    `);
                }
                $("ul.div-time-selector li").on("click", function() {
                    if (!$(this).hasClass("disabled")) {
                        if ($.trim($("#txt_description").val()).length > 0) {
                            let band = <?= ($usFunctions->isAppointmentRequestLimited()) ? "true" : "false" ?>;
                            if (($("#select_person").length == 0 && !band) || ($("#select_person").length > 0 && $("#select_person option:selected").css("color") !== "rgb(255, 0, 0)")) {
                                let area = $.trim($(".div-area-selector button[data-value=" + $("#txt_area").val() + "]").text()).split(" ");
                                area = area[area.length - 1];
                                let campus = $.trim($(".div-area-selector button[data-value=" + $("#txt_area").val() + "]").text()).split(" ");
                                campus.pop();
                                campus = campus.join(" ");
                                let date = new Date($.trim($("#txt_date").val()));
                                let days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                                let months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"]
                                let type = ($.trim($("#txt_type").val()) == "P") ? "Presencial" : "Teleconsulta";
                                let patientFullname = ($("#select_person").length > 0) ? (($("#select_person")[0].selectedIndex == 0) ? "<?= $_SESSION["dep_user_name"] . " " . $_SESSION["dep_user_lastname"] ?>" : $("#select_person option:selected").text()) : "<?= $_SESSION["dep_user_name"] . " " . $_SESSION["dep_user_lastname"] ?>";
                                $("#txt_init_time").val($.trim($(this).text()));
                                $("#modalConfirmRequestAppointment ._form-left span.content-text").text("Cita en el área " + area);
                                $("#modalConfirmRequestAppointment ._date").html("<i class=\"fa fa-clock\"></i>" + $.trim($("#txt_init_time").val()) + " - " + days[date.getDay()] + " " + date.getUTCDate() + " de " + months[date.getMonth()]);
                                $("#modalConfirmRequestAppointment ._type").html("<i class=\"fa fa-medkit\"></i>" + type);
                                $("#modalConfirmRequestAppointment ._address").html("<i class=\"fa fa-map-marker\"></i>" + campus);
                                $("#modalConfirmRequestAppointment ._patient-data ._description").text(patientFullname);
                                $("#modalConfirmRequestAppointment ._reason-data ._description").text($.trim($("#txt_description").val()));
                                $("#modalConfirmRequestAppointment").modal("show");
                            } else {
                                toastr.error("Este paciente ya se encuentra con una cita registrada");
                            }
                        } else {
                            toastr.error("El motivo de la consulta es obligatorio");
                        }
                    }
                });
            }
            updateAvailableHours($("#txt_type").val(), $("#txt_area").val(), $("#txt_date").val());
        }

        function updateAvailableHours(type, area, date) {
            if ($("ul.div-time-selector li").length > 0) {
                $("ul.div-time-selector li").removeClass("disabled");
                let currentDate = new Date();
                let selectedData = new Date($.trim($("#txt_date").val()));
                //if (currentDate == selectedData) {console.log("a");}
                //hora actual + 1 hora en adelante disponibles
                if ($.trim($("#txt_date").val()) == currentDate.getFullYear() + "-" + addZ(currentDate.getMonth() + 1) + "-" + addZ(currentDate.getUTCDate())) {
                    $("ul.div-time-selector li").each(function(each_i, each_v) {
                        if (timeToSeconds($.trim($(this).text()) + ":00") <= timeToSeconds(currentDate.getHours() + ":" + currentDate.getMinutes() + ":00") + 3600) {
                            $(this).addClass("disabled");
                        }
                    });
                }
                //las horas que no se encuentren en turnos confirmados disponibles
                $.ajax({
                    url: '/<?= BASE_URL ?>ajax.php',
                    async: false,
                    method: 'POST',
                    data: {
                        action: "load_disabled_hours",
                        type: $("#txt_type").val(),
                        area: $("#txt_area").val(),
                        date: $("#txt_date").val()
                    },
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.status == 500) {
                            console.log(response.error);
                        } else {
                            $.each(response.data, function(each_i, each_v) {
                                if ($("ul.div-time-selector li:contains('"+each_v.substr(0,5)+"')").length > 0 && !$("ul.div-time-selector li:contains('"+each_v.substr(0,5)+"')").hasClass("disabled")) {
                                    $("ul.div-time-selector li:contains('"+each_v.substr(0,5)+"')").addClass("disabled");
                                }
                            });
                        }
                    }
                });
            }
        }

        function confirmAppointment() {
            $("#create_form").submit();
        }

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

            <?php if ($_SESSION["dep_user_is_employee"] == true) { ?>
                $("#select_person").select2({
                    width: "resolve",
                    minimumResultsForSearch: -1,
                    templateSelection: function(data) {
                        let color = $(data.element).css("color");
                        return $('<span style="color: ' + color + '">' + data.text + '<span>');
                    }
                }).on("select2:opening", function(e) {
                    setTimeout(function() {
                        $("#select_person option").each(function(each_i, each_v) {
                            if ($(each_v).css("color") == "rgb(255, 0, 0)") {
                                $(".select2-results ul li:nth-child(" + (each_i + 1) + ")").css("color", "red");
                            }
                        });
                    }, 100);
                });
            <?php } ?>

            $("div.row.div-type-selector button").on("click", function() {
                $("div.row.div-type-selector button").removeClass("active");
                $(this).addClass("active");
                $("#txt_type").val($(this).data("value"));
                loadAvailableSchedule();
            });
            $("div.row.div-area-selector button").on("click", function() {
                $("div.row.div-area-selector button").removeClass("active");
                $(this).addClass("active");
                $("#txt_area").val($(this).data("value"));
                loadAvailableSchedule();
            });
            $("div.div-date-selector li").on("click", function() {
                if (!$(this).hasClass("disabled")) {
                    let band = false;
                    if ($("div.div-date-selector li.selected").length > 0) {
                        band = true;
                    }
                    $("div.div-date-selector li").removeClass("selected");
                    $(this).addClass("selected");
                    $("#txt_date").val($(this).data("value"));
                    $("#txt_init_time").val("");
                    if (band) {
                        updateAvailableHours($("#txt_type").val(), $("#txt_area").val(), $("#txt_date").val());
                    } else {
                        setAvailableHours(_hours, _duration);
                    }
                }
            });

            $('#create_form').validate({
                rules: {
                    select_person: {
                        required: true
                    },
                    txt_descrition: {
                        required: true
                    }
                },
                messages: {
                    select_person: {
                        required: "Este campo es requerido"
                    },
                    txt_descrition: {
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

            loadAvailableSchedule();

            setInterval(function() {
                updateAvailableHours();
            }, 15000);
        });
    </script>

</body>

</html>
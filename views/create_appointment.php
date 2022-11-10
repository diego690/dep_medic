<?php
require_once(__DIR__ . "/../system/loader.php");
require_once(__DIR__ . "/../system/security/session.php");
require_once(__DIR__ . "/../data/mysql/doctor.functions.php");
require_once(__DIR__ . "/../data/mysql/us.functions.php");

$doctorFunctions = new DoctorFunctions();
$usFunctions = new UsFunctions();
if ($_SESSION["dep_user_role"] != "DR" || !in_array($_SESSION["dep_user_area"], [2, 3])) {
    header("Location: /" . BASE_URL . "home");
    exit();
}
$area = $doctorFunctions->getMyArea();
if (!$area) {
    header("Location: /" . BASE_URL . "home");
    exit();
}

$msg_response = array(
    "errors" => array(),
    "success" => array()
);

$schedule = $usFunctions->getScheduleByAreaID($area);
$nonWorkingDays = array();
for ($i = 0; $i < 7; $i++) {
    if (($i == 0 && !in_array(7, json_decode($schedule->days))) || !in_array($i, json_decode($schedule->days))) {
        $nonWorkingDays["" . $i] = true;
    }
}
$workingHours = array(explode(":", json_decode($schedule->hours_p)[0]->start)[0], explode(":", json_decode($schedule->hours_t)[0]->end)[0]);

//Validate to avoid POST duplicates
$post_validation_session = (isset($_SESSION['post_id'])) ? $_SESSION['post_id'] : "";
$post_validation_form = (isset($_POST['post_id'])) ? $_POST['post_id'] : "";
$is_post = (count($_POST) > 0) && ($post_validation_session != $post_validation_form);

if ($is_post) {
    $_SESSION['post_id'] = $_POST['post_id'];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= ADMIN_PANEL_NAME ?> - Registrar Cita</title>

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

            <main class="content py-3 px-4">
                <div class="container-fluid p-0">

                    <!--<h1 class="h3 mb-3">Registrar Cita</h1>-->

                    <div class="row">
                        <div class="col-12 col-lg-8 p-0">
                            <div class="card border shadow-none mb-0" style="height: 663px;">
                                <!--<div class="card-header">
                                    <h5 class="card-title mb-0">Calendario</h5>
                                </div>-->
                                <div class="card-body p-0">
                                    <div class="p-3 text-center">
                                        <span style="float: left;">
                                            <button type="button" class="btn btn-pill btn-outline-success btn-sm py-0 px-3" style="line-height: 30px;" onclick="calendarAction('move-today');">Hoy</button>
                                            <button type="button" class="btn btn-pill btn-outline-success btn-sm p2" style="font-size: 14px;" onclick="calendarAction('move-prev');"><i class="fa fa-chevron-left" style="width: 14px"></i></button>
                                            <button type="button" class="btn btn-pill btn-outline-success btn-sm p2" style="font-size: 14px;" onclick="calendarAction('move-next');"><i class="fa fa-chevron-right" style="width: 14px"></i></button>
                                        </span>
                                        <span id="renderRange" style="padding-left: 12px; font-size: 19px; vertical-align: middle;">28 jun – 4 jul 2021</span> <!-- 28 de junio de 2021 -->
                                        <span style="float: right;">
                                            <div class="btn-group btn-group" role="group">
                                                <button type="button" class="btn btn-pill btn-outline-success btn-sm py-0 px-3" style="line-height: 30px;" onclick="calendarAction('toggle-daily');">Día</button>
                                                <button type="button" class="btn btn-pill btn-outline-success btn-sm py-0 px-3" style="line-height: 30px;" onclick="calendarAction('toggle-weekly');">Semana</button>
                                            </div>
                                        </span>
                                    </div>
                                    <div id="div_appointment_calendar" style="height: 600px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 p-0">
                            <div class="row h-100">
                                <div class="col-12">
                                    <div class="card border shadow-none mb-0" style="height: 331px;">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Nueva Cita</h5>
                                        </div>
                                        <div class="card-body" style="overflow-y: auto;">
                                            <form id="create_form" action="" method="post" novalidate="novalidate">
                                                <div class="mb-3 form-group">
                                                    <label for="select_patient" class="form-label">Paciente <span style="color: red;">*</span></label>
                                                    <select class="form-control" id="select_patient" name="select_patient" style="width: 100%;" required>

                                                    </select>
                                                </div>
                                                <div class="mb-3 form-group">
                                                    <label for="select_type" class="form-label">Tipo <span style="color: red;">*</span></label>
                                                    <select class="form-control" id="select_type" name="select_type" style="width: 100%;" required>
                                                        <option value="P">Presencial</option>
                                                        <option value="T">Telemedicina</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3 form-group">
                                                    <label for="txt_description" class="form-label">Motivo de consulta <span style="color: red;">*</span></label>
                                                    <textarea class="form-control" id="txt_description" name="txt_description" required></textarea>
                                                </div>
                                                <div class="mb-3 form-group">
                                                    <label for="txt_date" class="form-label">Fecha <span style="color: red;">*</span></label>
                                                    <div>
                                                        <input type="text" class="form-control" id="txt_date" name="txt_date">
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-success">Crear</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card border shadow-none mb-0" style="height: 331px;">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Solicitudes</h5>
                                        </div>
                                        <div class="card-body" style="overflow-y: auto;">
                                            <table id="tb_requests" class="table table-striped table-hover table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Paciente</th>
                                                        <th class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
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
    include_once("modals/create_appointment.php");
    include_once("includes/scripts.php");
    ?>

    <script>
        var appointment_calendar = null,
            dt_requests = null;

        function calendarAction(action) {
            switch (action) {
                case "move-today":
                    appointment_calendar.today();
                    break;
                case "move-prev":
                    appointment_calendar.prev();
                    break;
                case "move-next":
                    appointment_calendar.next();
                    break;
                case "toggle-daily":
                    appointment_calendar.changeView("day", true);
                    break;
                case "toggle-weekly":
                    appointment_calendar.changeView("week", true);
                    break;
            }
            setRenderRangeText();
        }

        function currentCalendarDate(format) {
            let currentDate = moment([appointment_calendar.getDate().getFullYear(), appointment_calendar.getDate().getMonth(), appointment_calendar.getDate().getDate()]);
            return currentDate.format(format);
        }

        function setRenderRangeText() {
            let renderRange = document.getElementById("renderRange");
            let options = appointment_calendar.getOptions();
            let viewName = appointment_calendar.getViewName();
            let html = [];
            if (viewName === "day") {
                html.push(currentCalendarDate('DD [de] MMMM [de] YYYY'));
            } else {
                html.push(moment(appointment_calendar.getDateRangeStart().getTime()).format("DD MMM"));
                html.push(" - ");
                html.push(moment(appointment_calendar.getDateRangeEnd().getTime()).format("DD MMM YYYY"));
            }
            renderRange.innerHTML = html.join("");
        }

        function setSchedules() {
            appointment_calendar.clear();
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "load_appointments_by_area"
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        console.log(response.error);
                    } else {
                        let scheduleList = [];
                        $.each(response.data, function(each_i, each_v) {
                            let schedule = {
                                id: chance.guid(),
                                calendarId: each_v.calendar,
                                title: each_v.title,
                                body: "<input id='txt_appointment_id' type='hidden' value='" + each_v.id + "'><b>Motivo de consulta: </b><br/>" + each_v.body,
                                isReadOnly: true,
                                isAllday: false,
                                category: "time",
                                start: each_v.start,
                                end: each_v.end,
                                isPrivate: false,
                                location: null,
                                attendees: [],
                                recurrenceRule: "",
                                state: null,
                                color: appointment_calendar.color,
                                bgColor: appointment_calendar.bgColor,
                                dragBgColor: appointment_calendar.dragBgColor,
                                borderColor: appointment_calendar.borderColor
                            };
                            scheduleList.push(schedule);
                        });
                        appointment_calendar.createSchedules(scheduleList);
                    }
                }
            });
        }

        function openModalCancel(data) {
            $('#modalConfirmCancelDo').attr('data-value', data);
            $('#modalConfirmCancel').modal("show");
        }

        function cancelAppointment(appointmentID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "cancel_appointment",
                    appointment_id: appointmentID
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        toastr.success(response.data.msg);
                        setSchedules();
                    }
                }
            });
        }

        function openModalViewRequest(data) {
            data = JSON.parse(atob(decodeURIComponent(data).split('').reverse().join('')));
            //set data
            $('#modalConfirmViewRequest .mcvr_created_at').text(data.created_at);
            $('#modalConfirmViewRequest .mcvr_identification').text(data.identification);
            $('#modalConfirmViewRequest .mcvr_fullname').text(data.fullname);
            $('#modalConfirmViewRequest .mcvr_area').text(data.full_area);
            $('#modalConfirmViewRequest .mcvr_type').text(data.type);
            $('#modalConfirmViewRequest .mcvr_date').text(data.date);
            $('#modalConfirmViewRequest .mcvr_time').text(data.time);
            $('#modalConfirmViewRequest .mcvr_description').text(data.description);
            $('#modalConfirmViewRequest').modal("show");
        }

        function openModalAccept(data) {
            $('#modalConfirmAcceptDo').attr('data-value', data);
            $('#modalConfirmAccept').modal("show");
        }

        function acceptRequest(requestID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "accept_appointment_request",
                    request_id: requestID
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        toastr.success(response.data.msg);
                        if (dt_requests != null) {
                            dt_requests.draw();
                        }
                        setSchedules();
                    }
                }
            });
        }

        function openModalDecline(data) {
            $('#modalConfirmDeclineDo').attr('data-value', data);
            $('#modalConfirmDecline').modal("show");
        }

        function declineRequest(requestID) {
            $.ajax({
                url: '/<?= BASE_URL ?>ajax.php',
                method: 'POST',
                data: {
                    action: "decline_appointment_request",
                    request_id: requestID
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.status == 500) {
                        toastr.error(response.error);
                    } else {
                        toastr.success(response.data.msg);
                        if (dt_requests != null) {
                            dt_requests.draw();
                        }
                    }
                }
            });
        }

        $(document).ready(function() {
            $("#mnu_appointments").addClass("active");
            $("#mnu_appointments a:first").removeClass("collapsed");
            $("#mnugrp_appointments").addClass("show");
            $("#mnu_appointments_create").addClass("active");
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

            $("#select_patient").select2({
                ajax: {
                    url: "/<?= BASE_URL ?>ajax.php",
                    method: "POST",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            action: "search_patient",
                            q: params.term
                        }
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                width: "resolve"
            });
            $("#select_type").select2({
                width: "resolve",
                minimumResultsForSearch: -1
            });

            let a_m = moment().minutes();
            let a_interval = 20;
            if (a_m > 40) {
                a_interval = 60;
            } else if (a_m > 20) {
                a_interval = 40;
            } else if (a_m == 0) {
                a_interval = 0;
            }
            let a_rest = a_interval - a_m;
            let a_startDate = moment().add(a_rest, "minutes").add(1, "hour");
            $("#txt_date").daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                timePicker: true,
                timePicker24Hour: true,
                timePickerIncrement: 20,
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
                    ],
                    format: 'MM/DD/YYYY H:mm'
                },
                minDate: a_startDate,
                startDate: a_startDate,
                autoUpdate: false,
                opens: 'left',
                stepping: 20
            });
            $('#txt_date').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
            $("#create_form").on("submit", function(e) {
                e.preventDefault();
                if ($(this).valid()) {
                    $.ajax({
                        url: '/<?= BASE_URL ?>ajax.php',
                        method: 'POST',
                        data: {
                            action: "create_appointment",
                            patient_id: $("#select_patient").val(),
                            date: $("#txt_date").val(),
                            type: $("#select_type").val(),
                            description: $("#txt_description").val()
                        },
                        success: function(response) {
                            response = JSON.parse(response);
                            if (response.status == 500) {
                                toastr.error(response.error);
                            } else {
                                toastr.success(response.data.msg);
                                $("#select_patient").val("").trigger("change");
                                $("#txt_date").val(a_startDate.format("MM/DD/YYYY H:mm"));
                                $("#select_type").val("P").trigger("change");
                                $("#txt_description").val("");
                                setSchedules();
                            }
                        }
                    });
                }
            });

            appointment_calendar = new tui.Calendar(document.getElementById("div_appointment_calendar"), {
                defaultView: "week",
                taskView: false,
                scheduleView: ["time"],
                template: {
                    weekDayname: function(model) {
                        return '<center><span class="tui-full-calendar-dayname-name">' + model.dayName + ' ' + model.date + '</span></center>';
                    },
                    timegridDisplayPrimaryTime: function(time) {
                        var strHour = time.hour + ":00";
                        if (time.hour < 10) {
                            strHour = "0" + time.hour + ":00";
                        }

                        return strHour;
                    },
                    time: function(schedule) {
                        return getTimeTemplate(schedule, false);
                    },
                    popupDetailDate: function(isAllDay, start, end) {
                        var isSameDate = moment(start).isSame(end);
                        var endFormat = (isSameDate ? '' : 'DD/MM/YYYY ') + 'HH:mm';

                        if (isAllDay) {
                            return moment(start.getTime()).format('YYYY.MM.DD') + (isSameDate ? '' : ' - ' + moment(end.getTime()).format('YYYY.MM.DD'));
                        }

                        return (moment(start.getTime()).format('DD/MM/YYYY HH:mm') + ' - ' + moment(end.getTime()).format(endFormat));
                    },
                    popupDelete: function() {
                        return 'Delete';
                    }
                },
                week: {
                    startDayOfWeek: 1,
                    daynames: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                    hourStart: <?= $workingHours[0] - 1 ?>,
                    hourEnd: <?= $workingHours[1] + 1 ?>
                },
                calendars: [{
                        id: '1',
                        name: 'Presencial',
                        color: '#000000',
                        bgColor: '#dbf2e3',
                        dragBgColor: '#dbf2e3',
                        borderColor: '#4bbf73'
                    },
                    {
                        id: '2',
                        name: 'Telemedicina',
                        color: '#000000',
                        bgColor: '#d9e6fb',
                        dragBgColor: '#d9e6fb',
                        borderColor: '#3f80ea'
                    }
                ],
                useCreationPopup: false,
                useDetailPopup: true,
                disableDblClick: true,
                disableClick: true,
                isReadOnly: true
            });
            appointment_calendar.on({
                clickSchedule: function(e) {
                    let element = $("#div_appointment_calendar .tui-full-calendar-popup-container");
                    if (element.length > 0) {
                        let id_v = element.find("#txt_appointment_id").val();
                        element.append(`
                            <div class="tui-full-calendar-section-button p-2">
                                <button type="button" class="btn btn-danger" onclick="openModalCancel('` + id_v + `');">Cancelar</button>
                            </div>
                        `);
                    } else {
                        console.log("Error to load buttons");
                    }
                }
            });
            setRenderRangeText();
            var tbOptions = {
                dom: "frtp",
                responsive: true,
                autoWidth: false,
                lengthChange: false,
                info: false,
                processing: true,
                serverSide: true,
                serverMethod: 'post',
                ajax: {
                    url: '/<?= BASE_URL ?>ajax.php',
                    data: {
                        action: 'get_appointment_requests',
                    }
                },
                columns: [{
                        data: "patient",
                        render: function(data, type, row, meta) {
                            return `
                                <span><b>` + data + `</b></span></br>
                                <span><em>` + row.date + ` - ` + row.time + `</em></span>
                            `;
                        },
                        orderable: false
                    },
                    {
                        data: "column_actions",
                        orderable: false
                    }
                ],
                order: [],
                language: {
                    url: "/<?= BASE_URL ?>assets/plugins/datatable-languages/es_es.lang"
                },
                drawCallback: function(settings) {
                    feather.replace();
                    $("[data-toggle=tooltip]").mouseenter(function() {
                        $(this).tooltip('show');
                    });
                }
            }
            var tb_requests = $('#tb_requests');
            dt_requests = tb_requests.DataTable(tbOptions);

            $('#create_form').validate({
                rules: {
                    select_patient: {
                        required: true
                    },
                    txt_description: {
                        required: true
                    },
                    txt_date: {
                        required: true
                    }
                },
                messages: {
                    select_patient: {
                        required: "Este campo es requerido"
                    },
                    txt_description: {
                        required: "Este campo es requerido"
                    },
                    txt_date: {
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

            setSchedules();
            setInterval(function() {
                setSchedules();
                if (dt_requests != null) {
                    dt_requests.draw();
                }
            }, 15000);
        });
    </script>

</body>

</html>
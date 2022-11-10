<?php
?>
<script src="/<?= BASE_URL ?>assets/dist/js/app.js"></script>
<script src="/<?= BASE_URL ?>assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/<?= BASE_URL ?>assets/plugins/toastr/toastr.min.js"></script>
<script src="/<?= BASE_URL ?>assets/plugins/bootstrap-filestyle/bootstrap-filestyle.min.js"></script>
<script src="/<?= BASE_URL ?>assets/plugins/sweetalert2/sweetalert2@11.js"></script>

<!-- Bryntum Calendar -->
<!--<script src="/<?= BASE_URL ?>assets/plugins/bryntum-calendar/calendar.umd.js"></script>-->

<!-- jsPdf -->
<script src="/<?= BASE_URL ?>assets/plugins/jsPdf/jspdf.min.js"></script>
<script src="/<?= BASE_URL ?>assets/plugins/jsPdf/jspdf.plugin.autotable.min.js"></script>

<!-- ToastUI Calendar -->
<script src="/<?= BASE_URL ?>assets/plugins/tui-calendar/js/tui-code-snippet.min.js"></script>
<script src="/<?= BASE_URL ?>assets/plugins/tui-calendar/js/tui-time-picker.min.js"></script>
<script src="/<?= BASE_URL ?>assets/plugins/tui-calendar/js/tui-date-picker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/locale/es.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chance/1.0.13/chance.min.js"></script>
<script src="/<?= BASE_URL ?>assets/plugins/tui-calendar/tui-calendar.js"></script>

<script>
    function timeToSeconds(time) {
        time = time.split(/:/);
        return parseInt(time[0]) * 3600 + parseInt(time[1]) * 60 + parseInt(time[2]);
    }

    function getTimeTemplate(schedule, isAllDay) {
        var html = [];
        var start = moment(schedule.start.toUTCString());
        var end = moment(schedule.end.toUTCString());
        let fullname = schedule.title.split(" ");
        let icon = "user";
        fullname = fullname[0] + " " + fullname[1];
        if (schedule.calendarId == 2) {
            icon = "video";
        }
        html.push('<small><strong><i class="fa fa-' + icon + '"></i> ' + fullname + "</strong></small><br/>");
        if (!isAllDay) {
            html.push('<strong style="font-weight: 700;">' + start.format('HH:mm') + ' - ' + end.format('HH:mm') + '</strong>');
        }
        return html.join('');
    }

    $(document).ready(function() {
        (function($) {
            $.fn.inputFilter = function(inputFilter) {
                //Integer                       /^-?\d*$/.test(value)
                //Integer >= 0                  /^\d*$/.test(value)
                //Integer >= 0 and <= 500       /^\d*$/.test(value) && (value === "" || parseInt(value) <= 500)
                //Float (use . or , as decimal separator)                  /^-?\d*[.,]?\d*$/.test(value)
                //Currency (at most two decimal places)                    /^-?\d*[.,]?\d{0,2}$/.test(value)
                //A-Z only                      /^[a-z]*$/i.test(value)
                //Hexadecimal                   /^[0-9a-f]*$/i.test(value)
                return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
                    if (inputFilter(this.value)) {
                        this.oldValue = this.value;
                        this.oldSelectionStart = this.selectionStart;
                        this.oldSelectionEnd = this.selectionEnd;
                    } else if (this.hasOwnProperty("oldValue")) {
                        this.value = this.oldValue;
                        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                    }
                });
            }
        }(jQuery));

        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };
        moment.locale("es-mx");
    });
</script>
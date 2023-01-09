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
<script>
    function soloLetras(e) {
        var key = e.keyCode || e.which,
            tecla = String.fromCharCode(key).toLowerCase(),
            letras = " áéíóúabcdefghijklmnñopqrstuvwxyz",
            especiales = [8, 37, 39, 46],
            tecla_especial = false;

        for (var i in especiales) {
            if (key == especiales[i]) {
                tecla_especial = true;
                break;
            }
        }

        if (letras.indexOf(tecla) == -1 && !tecla_especial) {
            return false;
        }
    }
    function soloNumeros(e) {
        var key = e.keyCode || e.which,
            tecla = String.fromCharCode(key).toLowerCase(),
            letras = " 0123456789",
            especiales = [8, 37, 39, 46],
            tecla_especial = false;

        for (var i in especiales) {
            if (key == especiales[i]) {
                tecla_especial = true;
                break;
            }
        }

        if (letras.indexOf(tecla) == -1 && !tecla_especial) {
            return false;
        }
    }
</script>
<script>
    $(document).ready(function() {
        var cedula = document.getElementById("txt_identification").value.trim();
        if (cedula.length == 10) {
            //Obtenemos el digito de la region que sonlos dos primeros digitos
            var digito_region = cedula.substring(0, 2);

            //Pregunto si la region existe ecuador se divide en 24 regiones
            if (digito_region >= 1 && digito_region <= 24) {
                // Extraigo el ultimo digito
                var ultimo_digito = cedula.substring(9, 10);

                //Agrupo todos los pares y los sumo
                var pares = parseInt(cedula.substring(1, 2)) + parseInt(cedula.substring(3, 4)) + parseInt(cedula.substring(5, 6)) + parseInt(cedula.substring(7, 8));

                //Agrupo los impares, los multiplico por un factor de 2, si la resultante es > que 9 le restamos el 9 a la resultante
                var numero1 = cedula.substring(0, 1);
                var numero1 = (numero1 * 2);
                if (numero1 > 9) {
                    var numero1 = (numero1 - 9);
                }

                var numero3 = cedula.substring(2, 3);
                var numero3 = (numero3 * 2);
                if (numero3 > 9) {
                    var numero3 = (numero3 - 9);
                }

                var numero5 = cedula.substring(4, 5);
                var numero5 = (numero5 * 2);
                if (numero5 > 9) {
                    var numero5 = (numero5 - 9);
                }

                var numero7 = cedula.substring(6, 7);
                var numero7 = (numero7 * 2);
                if (numero7 > 9) {
                    var numero7 = (numero7 - 9);
                }

                var numero9 = cedula.substring(8, 9);
                var numero9 = (numero9 * 2);
                if (numero9 > 9) {
                    var numero9 = (numero9 - 9);
                }

                var impares = numero1 + numero3 + numero5 + numero7 + numero9;

                //Suma total
                var suma_total = (pares + impares);

                //extraemos el primero digito
                var primer_digito_suma = String(suma_total).substring(0, 1);

                //Obtenemos la decena inmediata
                var decena = (parseInt(primer_digito_suma) + 1) * 10;

                //Obtenemos la resta de la decena inmediata - la suma_total esto nos da el digito validador
                var digito_validador = decena - suma_total;

                //Si el digito validador es = a 10 toma el valor de 0
                if (digito_validador == 10)
                    var digito_validador = 0;

                //Validamos que el digito validador sea igual al de la cedula
                if (digito_validador == ultimo_digito) {
                    console.log('la cedula:' + cedula + ' es correcta');
                } else {
                    console.log('la cedula:' + cedula + ' es incorrecta');
                }
            } else {
                // imprimimos en consola si la region no pertenece
                console.log('Esta cedula no pertenece a ninguna region');
            }
        } else {
            //imprimimos en consola si la cedula tiene mas o menos de 10 digitos
            console.log('Esta cedula tiene menos de 10 Digitos');
        }
    });
</script>
<script>
    $(document).ready(function () {
        $("#select_diagnosis1").select2({
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

        $("#select_diagnosis1").on("select2:select", function (e) {
            let data = e.params.data;

            $("#select_diagnosis1 option[value=" + data.id + "]").data('text', data.text);
            $("#select_diagnosis1").trigger('change');
            $("#select_diagnosis1").parent().find("small").text("Código CIE10: " + data.qty);
            /* let details= {
                 product: (data.id),
                 productID: (data.qty),
                 quantity: (data.text)
             };
             addToTable(details);*/
        });
    });
</script>
<div class="modal fade" id="modalConfirmRequestAppointment" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmRequestAppointmentLabel">Confirmar Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                <div class="_request">
                    <div class="_form-left">
                        <div class="content-title">
                            <div style="background-image: url(/<?= BASE_URL ?>assets/dist/img/estetoscopio_icon.png)" class="content-avatar-image content-avatar-image--is-big">
                            </div>
                            <span class="content-text">
                                Cita en el área...
                            </span>
                        </div>
                        <ul class="content-data">
                            <li class="_date">
                                <i class="fa fa-clock"></i>
                                
                            </li>

                            <li class="_type">
                                <i class="fa fa-medkit"></i>
                                
                            </li>

                            <li class="_address">
                                <i class="fa fa-map-marker"></i>
                                
                            </li>
                        </ul>
                    </div>
                    <div class="_form-right">
                        <div class="_patient-data">
                            <h2>Paciente</h2>
                            <div class="_description">
                                
                            </div>
                        </div>
                        <div class="_reason-data">
                            <h2>Motivo de consulta</h2>
                            <div class="_description">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="modalConfirmRequestAppointmentClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="modalConfirmRequestAppointmentDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="confirmAppointment();">Solicitar agendamiento</button>
            </div>
        </div>
    </div>
</div>
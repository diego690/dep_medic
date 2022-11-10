<div class="modal fade" id="modalConfirmCancel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmCancelLabel">Cancelar Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                ¿Está seguro de que quiere cancelar esta cita<b><span id="modalConfirmCancelText"></span></b>?
            </div>
            <div class="modal-footer">
                <button id="modalConfirmCancelClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmCancelDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="cancelAppointment($(this).attr('data-value'));">Si</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmViewRequest" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmViewRequestLabel">Detalle de la Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                <div class="row" style="margin-bottom: 1rem;">
                    <div class="col-12">
                        <span>Creada en:
                            <span class="mcvr_created_at"></span>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <span><b>Cédula / Pasaporte:</b>
                            <p class="mcvr_identification"></p>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span><b>Nombres y Apellidos:</b>
                            <p class="mcvr_fullname"></p>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <span><b>Área:</b>
                            <p class="mcvr_area"></p>
                        </span>
                    </div>
                    <div class="col-6">
                        <span><b>Tipo:</b>
                            <p class="mcvr_type"></p>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <span><b>Fecha:</b>
                            <p class="mcvr_date"></p>
                        </span>
                    </div>
                    <div class="col-6">
                        <span><b>Hora:</b>
                            <p class="mcvr_time"></p>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span><b>Motivo de consulta:</b>
                            <p class="mcvr_description"></p>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="modalConfirmViewRequestClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmAccept" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmAcceptLabel">Aceptar Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                ¿Está seguro de que quiere aceptar esta solicitud de cita<b><span id="modalConfirmAcceptText"></span></b>?.</br></br>Si existen otras solicitudes que coincidan con la fecha y hora de la que va a aceptar, se rechazarán automáticamente para evitar choques.
            </div>
            <div class="modal-footer">
                <button id="modalConfirmAcceptClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmAcceptDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="acceptRequest($(this).attr('data-value'));">Si</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmDecline" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmDeclineLabel">Rechazar Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                ¿Está seguro de que quiere rechazar esta solicitud de cita<b><span id="modalConfirmDeclineText"></span></b>?
            </div>
            <div class="modal-footer">
                <button id="modalConfirmDeclineClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmDeclineDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="declineRequest($(this).attr('data-value'));">Si</button>
            </div>
        </div>
    </div>
</div>
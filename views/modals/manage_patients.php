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
                    <div class="col-6">
                        <span><b>Parentesco:</b> 
                            <p class="mcvr_kin"></p>
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
                        <span><b>Estado civil:</b>
                            <p class="mcvr_civil_state"></p>
                        </span>
                    </div>
                    <div class="col-6">
                        <span><b>Fecha de nacimiento:</b>
                            <p class="mcvr_birth_date"></p>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <span><b>Celular:</b>
                            <p class="mcvr_phone"></p>
                        </span>
                    </div>
                    <div class="col-6">
                        <span><b>Correo personal:</b>
                            <p class="mcvr_email"></p>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <span><b>Domicilio:</b>
                            <p class="mcvr_address"></p>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <b>Documento de respaldo:</b>
                    <div class="col-12 mcvr_backup_doc">
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
                ¿Está seguro de que quiere aceptar esta solicitud<b><span id="modalConfirmAcceptText"></span></b>?
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
                ¿Está seguro de que quiere rechazar esta solicitud<b><span id="modalConfirmDeclineText"></span></b>?
            </div>
            <div class="modal-footer">
                <button id="modalConfirmDeclineClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmDeclineDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="declineRequest($(this).attr('data-value'));">Si</button>
            </div>
        </div>
    </div>
</div>
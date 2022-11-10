<div class="modal fade" id="modalConfirmDelete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmDeleteLabel">Eliminar Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                ¿Está seguro de que quiere eliminar esta solicitud<b><span id="modalConfirmDeleteText"></span></b>?
            </div>
            <div class="modal-footer">
                <button id="modalConfirmDeleteClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmDeleteDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="deleteRequest($(this).attr('data-value'));">Si</button>
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
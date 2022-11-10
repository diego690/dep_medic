<div class="modal fade" id="modalConfirmActivate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmActivateLabel">Habilitar Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                ¿Está seguro de que quiere habilitar esta cuenta<b><span id="modalConfirmActivateText"></span></b>?
            </div>
            <div class="modal-footer">
                <button id="modalConfirmActivateClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmActivateDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="activateUser($(this).attr('data-value'));">Si</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalConfirmDeactivate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmDeactivateLabel">Deshabilitar Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                ¿Está seguro de que quiere deshabilitar esta cuenta<b><span id="modalConfirmDeactivateText"></span></b>?
            </div>
            <div class="modal-footer">
                <button id="modalConfirmDeactivateClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmDeactivateDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="deactivateUser($(this).attr('data-value'));">Si</button>
            </div>
        </div>
    </div>
</div>
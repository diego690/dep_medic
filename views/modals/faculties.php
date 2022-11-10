<div class="modal fade" id="modalConfirmDelete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmDeleteLabel">Eliminar Facultad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                ¿Está seguro de que quiere eliminar esta facultad<b><span id="modalConfirmDeleteText"></span></b>?
            </div>
            <div class="modal-footer">
                <button id="modalConfirmDeleteClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmDeleteDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="deleteFaculty($(this).attr('data-value'));">Si</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalConfirmDelete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmDeleteLabel">Eliminar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                ¿Está seguro de que quiere eliminar esta producto<b><span id="modalConfirmDeleteText"></span></b>?
            </div>
            <div class="modal-footer">
                <button id="modalConfirmDeleteClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmDeleteDo" type="button" class="btn btn-primary" data-bs-dismiss="modal" data-value="-1" onclick="deleteProduct($(this).attr('data-value'));">Si</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmIncreaseStock" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmIncreaseStockLabel">Aumentar Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body m-3">
                <form id="stock_form" action="" method="post" novalidate="novalidate">
                    <div class="mb-3 form-group">
                        <label for="txt_units">Unidades por caja</label>
                        <input type="number" class="form-control" id="txt_units" name="txt_units" min="1" required>
                    </div>
                    <div class="mb-3 form-group">
                        <label for="txt_stock">Cantidad</label>
                        <input type="number" class="form-control" id="txt_stock" name="txt_stock" min="1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="modalConfirmIncreaseStockClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button id="modalConfirmIncreaseStockDo" type="button" class="btn btn-primary" data-value="-1" onclick="increaseStock($(this).attr('data-value'));">Si</button>
            </div>
        </div>
    </div>
</div>
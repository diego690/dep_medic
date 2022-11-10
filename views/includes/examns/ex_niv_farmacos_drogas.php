<div class="col-12 col-sm-3 col-xxl d-flex">
    <div class="card flex-fill">
        <div class="card-body py-3">
            <div class="d-flex align-items-start">
                <div class="flex-grow-1">
                    <h3 class="mb-2">NIVELES DE FARMACOS Y DROGAS DE ABUSO</h3>
                    <div class="col-12 col-md-6">
                        <div class="mb-3 form-group">
                            <label for="select_niv_farmacos_drogas" class="form-label">Seleccione <span style="color: red;">*</span></label>
                            <select multiple class="form-select" id="select_niv_farmacos_drogas" name="select_exam[]" style="width: 200%;" >
                                <?php
                                $nivelesFarDrogas = $doctorFunctions->getNivelesFarmacosDrogas();
                                while ($r = $nivelesFarDrogas->fetch_object()) {
                                    ?>
                                    <option value="<?= $r->id ?>"><?= $r->type_exam ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
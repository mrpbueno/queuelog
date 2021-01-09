<form autocomplete="off" action="" method="post" class="fpbx-submit" id="hwform" name="hwform">
    <input type="hidden" name="action" id="action" value="search">
    <div class="row">
        <div class="col-md-6">
            <!--calldate start-->
            <div class="element-container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-5">
                                    <label class="control-label" for="body"><?php echo _("Data/Hora Início") ?></label>
                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="calldate"></i>
                                </div>
                                <div class="col-md-4">
                                    <input type='date' class="form-control" id="startDate" name="startDate" value="<?php echo empty($_POST['startDate']) ? date('Y-m-d') : $_POST['startDate']; ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type='time' class="form-control" id="startime" name="startTime" value="<?php echo empty($_POST['startTime']) ? '00:00' : $_POST['startTime']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span id="calldate-help" class="help-block fpbx-help-block"><?php echo _("Selecione a data/hora de início")?></span>
                    </div>
                </div>
            </div>
            <!--END calldate start-->
            <!--queuename-->
            <div class="element-container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-5">
                                    <label class="control-label" for="body"><?php echo _("Fila") ?></label>
                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="queuename"></i>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="queuename" name="queuename" value="<?php echo $_POST['queuename']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span id="queuename-help" class="help-block fpbx-help-block"><?php echo _("Digite o número da fila")?></span>
                    </div>
                </div>
            </div>
            <!--END queuename-->
        </div>
        <div class="col-md-6">
            <!--calldate end-->
            <div class="element-container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-5">
                                    <label class="control-label" for="body"><?php echo _("Data/Hora Fim") ?></label>
                                    <i class="fa fa-question-circle fpbx-help-icon" data-for="endDate"></i>
                                </div>
                                <div class="col-md-4">
                                    <input type='date' class="form-control" id="endDate" name="endDate" value="<?php echo empty($_POST['endDate']) ? date('Y-m-d') : $_POST['endDate']; ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type='time' class="form-control" id="endTime" name="endTime" value="<?php echo empty($_POST['endTime']) ? '23:59' : $_POST['endTime']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span id="endDate-help" class="help-block fpbx-help-block"><?php echo _("Selecione a data/hora do fim")?></span>
                    </div>
                </div>
            </div>
            <!--END calldate end-->

        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i> <?php echo _("Buscar")?></button>
        </div>
    </div>
    <br/>
</form>
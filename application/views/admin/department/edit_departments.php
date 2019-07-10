<?php
$created = can_action('70', 'created');
$edited = can_action('70', 'edited');
if (!empty($created) || !empty($edited)) {
    ?>
    <div class="panel panel-custom">
        <div class="panel-heading">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit') . ' ' . lang('departments') ?></h4>
        </div>
        <div class="modal-body wrap-modal wrap">
            <form data-parsley-validate="" novalidate=""
                  action="<?php echo base_url() ?>admin/departments/edit_departments/<?php if (!empty($department_info->departments_id)) echo $department_info->departments_id; ?>"
                  method="post" class="form-horizontal form-groups-bordered">

                <div class="form-group" id="border-none">
                    <label for="field-1" class="col-sm-4 control-label"><?= lang('edit') . ' ' . lang('departments') ?>
                        <span
                            class="required">*</span></label>
                    <div class="col-sm-5">
                        <input
                            type="text" name="deptname" required class="form-control"
                            value="<?= (!empty($department_info->deptname) ? $department_info->deptname : '') ?>"/>
                    </div>
                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
                    <button type="submit" class="btn btn-primary"><?= lang('update') ?></button>
                </div>
            </form>
        </div>
    </div>
<?php } ?>

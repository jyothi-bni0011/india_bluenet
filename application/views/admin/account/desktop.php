<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        function checkbox(x) {
            return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + x + '" /></div>';
        }

        oTable = $('#EXPData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": 5,
            'Processing': true,
            'ServerSide': true,
            'sAjaxSource': '<?= base_url('admin/account/getExpenses'); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            }
        });

    });

</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('expenses'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="EXPData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th class="col-xs-2"><?= lang("code"); ?></th>
                            <th class="col-xs-2"><?= lang("subject"); ?></th>
                            <th class="col-xs-2"><?= lang("date"); ?></th>
                            <th class="col-xs-1"><?= lang("departments"); ?></th>
                            <th class="col-xs-2"><?= lang("created_by"); ?></th>
                            <th style="width:100px;"><?= lang("action"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

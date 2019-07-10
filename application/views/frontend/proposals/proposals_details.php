<?= message_box('success') ?>
<?= message_box('error');?>
<div class="row mb">
    <div class="col-sm-8">
        <?php
        if ($proposals_info->module == 'client') {
            $client_info = $this->proposal_model->check_by(array('client_id' => $proposals_info->module_id), 'tbl_client');
            $currency = $this->proposal_model->client_currency_sambol($proposals_info->module_id);
            $client_lang = $client_info->language;
        } else if ($proposals_info->module == 'leads') {
            $client_info = $this->proposal_model->check_by(array('leads_id' => $proposals_info->module_id), 'tbl_leads');
            $client_info->name = $client_info->lead_name;
            $client_info->zipcode = null;
            $client_lang = 'english';
            $currency = $this->proposal_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
        } else {
            $client_lang = 'english';
            $currency = $this->proposal_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
        }
        unset($this->lang->is_loaded[5]);
        $language_info = $this->lang->load('sales_lang', $client_lang, TRUE, FALSE, '', TRUE);
        ?>
    </div>
    <div class="col-sm-4 pull-right">
        <a onclick="print_proposals('print_proposals')" href="#" data-toggle="tooltip" data-placement="top" title=""
           data-original-title="Print" class="mr-sm btn btn-xs btn-danger pull-right">
            <i class="fa fa-print"></i>
        </a>
        <a style="margin-right: 5px"
           href="<?= base_url() ?>frontend/pdf_proposals/<?= $proposals_info->proposals_id ?>"
           data-toggle="tooltip" data-placement="top" title="" data-original-title="PDF"
           class="btn btn-xs btn-success pull-right">
            <i class="fa fa-file-pdf-o"></i>
        </a>

    </div>
</div>
<!-- Start Display Details -->
<?php
if (strtotime($proposals_info->due_date) < strtotime(date('Y-m-d')) && $proposals_info->status == 'draft') {
    $start = strtotime(date('Y-m-d'));
    $end = strtotime($proposals_info->due_date);
    $days_between = ceil(abs($end - $start) / 86400);
    ?>
    <div class="alert bg-danger-light hidden-print">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <i class="fa fa-warning"></i>
        <?= lang('proposal_overdue') . ' ' . lang('by') . ' ' . $days_between . ' ' . lang('days') ?>
    </div>
    <?php
}
?>
<!-- Main content -->
<div class="panel" id="print_proposals">
    <!-- title row -->
    <div class="show_print ">
        <div class="col-xs-12">
            <h4 class="page-header">
                <img style="width: 60px;width: 60px;margin-top: -10px;margin-right: 10px;"
                     src="<?= base_url() . config_item('invoice_logo') ?>"><?= config_item('company_name') ?>
            </h4>
        </div><!-- /.col -->
    </div>
    <!-- info row -->
    <div class="panel-body">
        <?php if (!empty($can_edit) && !empty($edited)) { ?>
            <a href="<?= base_url() ?>admin/proposals/index/edit_proposals/<?= $proposals_info->proposals_id ?>"
               class="pull-right btn btn-primary btn-xs"><?= $language_info['edit'] . ' ' . $language_info['proposal'] ?></a>
        <?php } ?>
        <h3 class="mt0 mb-sm"><?= $proposals_info->reference_no ?></h3>
        <hr class="m0">
        <div class="row mb-lg">
            <div class="col-lg-4 col-xs-6 br pv">
                <div class="row">
                    <div class="col-md-2 text-center visible-md visible-lg">
                        <em class="fa fa-truck fa-4x text-muted"></em>
                    </div>
                    <div class="col-md-10">
                        <h4 class="ml-sm"><?= (config_item('company_legal_name_' . $client_lang) ? config_item('company_legal_name_' . $client_lang) : config_item('company_legal_name')) ?></h4>
                        <address></address><?= (config_item('company_address_' . $client_lang) ? config_item('company_address_' . $client_lang) : config_item('company_address')) ?>
                        <br><?= (config_item('company_city_' . $client_lang) ? config_item('company_city_' . $client_lang) : config_item('company_city')) ?>
                        , <?= config_item('company_zip_code') ?>
                        <br><?= (config_item('company_country_' . $client_lang) ? config_item('company_country_' . $client_lang) : config_item('company_country')) ?>
                        <br/><?= $language_info['phone'] ?> : <?= config_item('company_phone') ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-xs-6 br pv">
                <div class="row">
                    <div class="col-md-2 text-center visible-md visible-lg">
                        <em class="fa fa-plane fa-4x text-muted"></em>
                    </div>
                    <?php
                    if (!empty($client_info)) {
                        $client_name = $client_info->name;
                        $address = $client_info->address;
                        $city = $client_info->city;
                        $zipcode = $client_info->zipcode;
                        $country = $client_info->country;
                        $phone = $client_info->phone;
                        $email = $client_info->email;
                    } else {
                        $client_name = '-';
                        $address = '-';
                        $city = '-';
                        $zipcode = '-';
                        $country = '-';
                        $phone = '-';
                        $email = '-';
                    }
                    ?>
                    <div class="col-md-10">
                        <h4><?= $client_name ?></h4>
                        <address></address><?= $address ?>
                        <br> <?= $city ?>, <?= $zipcode ?>
                        <br><?= $country ?>
                        <br><?= $language_info['phone'] ?>: <?= $phone ?>
                        <br><?= lang('email') ?>:<a href="mailto:<?= $email ?>"> <?= $email ?></a>
                    </div>
                </div>
            </div>
            <div class="clearfix hidden-md hidden-lg">
                <hr>
            </div>
            <div class="col-lg-4 col-xs-12 pv">
                <div class="clearfix">
                    <p class="pull-left text-uppercase"><?= lang('proposals') . ' ' . lang('no') ?></p>
                    <p class="pull-right mr"><?= $proposals_info->reference_no ?></p>
                </div>
                <div class="clearfix">
                    <p class="pull-left"><?= lang('proposal_date') ?></p>
                    <p class="pull-right mr"><?= strftime(config_item('date_format'), strtotime($proposals_info->proposal_date)); ?></p>
                </div>
                <div class="clearfix"><?php
                    if (strtotime($proposals_info->due_date) < strtotime(date('Y-m-d')) && $proposals_info->status == 'pending' || strtotime($proposals_info->due_date) < strtotime(date('Y-m-d')) && $proposals_info->status == ('draft')) {
                        $danger = 'text-danger';
                    } else {
                        $danger = null;
                    }
                    ?>
                    <p class="pull-left text-uppercase <?= $danger ?>"> <?= $language_info['valid_until'] ?></p>
                    <p class="pull-right mr <?= $danger ?>"><?= strftime(config_item('date_format'), strtotime($proposals_info->due_date)); ?></p>
                </div>
                <?php if (!empty($proposals_info->user_id)) { ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('assigned') ?></p>
                        <p class="pull-right mr"><?php

                            $profile_info = $this->db->where('user_id', $proposals_info->user_id)->get('tbl_account_details')->row();
                            if (!empty($profile_info)) {
                                echo $profile_info->fullname;
                            }
                            ?></p>
                    </div>
                <?php } ?>
                <div class="clearfix">
                    <?php
                    if ($proposals_info->status == 'accepted') {
                        $label = 'success';
                    } else {
                        $label = 'danger';
                    }
                    ?>
                    <p class="pull-left "><?= lang('status') ?></p>
                    <p class="pull-right mr"><span
                            class="label label-<?= $label ?>"><?= lang($proposals_info->status) ?></span></p>
                </div>
                <?php $show_custom_fields = custom_form_label(10, $proposals_info->proposals_id);

                if (!empty($show_custom_fields)) {
                    foreach ($show_custom_fields as $c_label => $v_fields) {
                        if (!empty($v_fields)) {
                            ?>
                            <div class="clearfix">
                                <p class="pull-left"><?= $c_label ?></p>
                                <p class="pull-right mr"><?= $v_fields ?></p>

                            </div>
                        <?php }
                    }
                }
                ?>
            </div>
        </div><!-- /.row -->
        <div class="table-responsive mb-lg " style="margin-top: 25px">
            <table class="table items proposal-items-preview" page-break-inside: auto;>
                <thead style="background: #3a3f51;color: #fff;">
                <tr>
                    <th><?= $language_info['items'] ?></th>
                    <?php
                    $invoice_view = config_item('invoice_view');
                    if (!empty($invoice_view) && $invoice_view == '2') {
                        ?>
                        <th><?= $language_info['hsn_code'] ?></th>
                    <?php } ?>
                    <?php
                    $qty_heading = $language_info['qty'];
                    if (isset($proposals_info) && $proposals_info->show_quantity_as == 'hours' || isset($hours_quantity)) {
                        $qty_heading = lang('hours');
                    } else if (isset($proposals_info) && $proposals_info->show_quantity_as == 'qty_hours') {
                        $qty_heading = lang('qty') . '/' . lang('hours');
                    }
                    ?>
                    <th><?php echo $qty_heading; ?></th>
                    <th class="col-sm-1"><?= $language_info['price'] ?></th>
                    <th class="col-sm-2"><?= $language_info['tax'] ?></th>
                    <th class="col-sm-1"><?= $language_info['total'] ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $invoice_items = $this->proposal_model->ordered_items_by_id($proposals_info->proposals_id);

                if (!empty($invoice_items)) :
                    foreach ($invoice_items as $key => $v_item) :
                        $item_name = $v_item->item_name ? $v_item->item_name : $v_item->item_desc;
                        $item_tax_name = json_decode($v_item->item_tax_name);
                        ?>
                        <tr class="sortable item" data-item-id="<?= $v_item->proposals_items_id ?>">
                            <td><strong class="block"><?= $item_name ?></strong>
                                <?= nl2br($v_item->item_desc) ?>
                            </td>
                            <?php
                            $invoice_view = config_item('invoice_view');
                            if (!empty($invoice_view) && $invoice_view == '2') {
                                ?>
                                <td><?= $v_item->hsn_code ?></td>
                            <?php } ?>
                            <td><?= $v_item->quantity . '   &nbsp' . $v_item->unit ?></td>
                            <td><?= display_money($v_item->unit_cost) ?></td>
                            <td><?php
                                if (!empty($item_tax_name)) {
                                    foreach ($item_tax_name as $v_tax_name) {
                                        $i_tax_name = explode('|', $v_tax_name);
                                        echo '<small class="pr-sm">' . $i_tax_name[0] . ' (' . $i_tax_name[1] . ' %)' . '</small>' . display_money($v_item->total_cost / 100 * $i_tax_name[1]) . ' <br>';
                                    }
                                }
                                ?></td>
                            <td><?= display_money($v_item->total_cost) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8"><?= lang('nothing_to_display') ?></td>
                    </tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
        <div class="row" style="margin-top: 35px">
            <div class="col-xs-8">
                <p class="well well-sm mt">
                    <?= $proposals_info->notes ?>
                </p>
            </div>
            <div class="col-sm-4 pv">
                <div class="clearfix">
                    <p class="pull-left"><?= $language_info['sub_total'] ?></p>
                    <p class="pull-right mr">
                        <?= display_money($this->proposal_model->proposal_calculation('proposal_cost', $proposals_info->proposals_id)); ?>
                    </p>
                </div>
                <?php if ($proposals_info->discount_total > 0): ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= $language_info['discount'] ?>
                            (<?php echo $proposals_info->discount_percent; ?>
                            %)</p>
                        <p class="pull-right mr">
                            <?= display_money($this->proposal_model->proposal_calculation('discount', $proposals_info->proposals_id)); ?>
                        </p>
                    </div>
                <?php endif ?>
                <?php
                $tax_info = json_decode($proposals_info->total_tax);
                $tax_total = 0;
                if (!empty($tax_info)) {
                    $tax_name = $tax_info->tax_name;
                    $total_tax = $tax_info->total_tax;
                    if (!empty($tax_name)) {
                        foreach ($tax_name as $t_key => $v_tax_info) {
                            $tax = explode('|', $v_tax_info);
                            $tax_total += $total_tax[$t_key];
                            ?>
                            <div class="clearfix">
                                <p class="pull-left"><?= $tax[0] . ' (' . $tax[1] . ' %)' ?></p>
                                <p class="pull-right mr">
                                    <?= display_money($total_tax[$t_key]); ?>
                                </p>
                            </div>
                        <?php }
                    }
                } ?>
                <?php if ($tax_total > 0): ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= $language_info['total'] . ' ' . $language_info['tax'] ?></p>
                        <p class="pull-right mr">
                            <?= display_money($tax_total); ?>
                        </p>
                    </div>
                <?php endif ?>
                <?php if ($proposals_info->adjustment > 0): ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= $language_info['adjustment'] ?></p>
                        <p class="pull-right mr">
                            <?= display_money($proposals_info->adjustment); ?>
                        </p>
                    </div>
                <?php endif;
                $total_proposal = $this->proposal_model->proposal_calculation('total', $proposals_info->proposals_id);
                ?>

                <div class="clearfix">
                    <p class="pull-left"><?= $language_info['total'] ?></p>
                    <p class="pull-right mr">
                        <?= display_money($total_proposal, $currency->symbol); ?>
                    </p>
                </div>
                <?php if (config_item('amount_to_words') == 'Yes') { ?>
                    <div class="clearfix">
                        <p class="pull-right h4"><strong class="h3"><?= lang('num_word') ?>
                                : </strong> <?= number_to_word($proposals_info->module_id, $total_proposal); ?></p>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
    <?= !empty($invoice_view) && $invoice_view > 0 ? $this->gst->summary($invoice_items) : ''; ?>
</div>
<script type="text/javascript">
    function print_proposals(print_proposals) {
        var printContents = document.getElementById(print_proposals).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>
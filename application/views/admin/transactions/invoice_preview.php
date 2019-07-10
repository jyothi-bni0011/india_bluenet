<?php include_once 'assets/admin-ajax.php'; ?>
<style>
    .note-editor .note-editable {
        height: 150px;
    }
</style>
<?php
$category_info = $this->db->where('expense_category_id', $expense_info->category_id)->get('tbl_expense_category')->row();
if (!empty($category_info)) {
    $category = $category_info->expense_category;
} else {
    $category = lang('undefined_category');
}
?>
<form data-parsley-validate="" novalidate=""
      action="<?php echo base_url() ?>admin/projects/save_invoice/<?php if (!empty($expense_info->project_id)) echo $expense_info->project_id; ?>"
      method="post" class="form-horizontal form-groups-bordered">
    <div class="panel panel-custom">
        <div class="panel-heading">
            <h4 class="modal-title"
                id="myModalLabel"><?= '[' . $category . '] ' . $expense_info->name . ' - ' . lang('preview_invoice') ?></h4>
        </div>

        <?php
        $client_info = $this->invoice_model->check_by(array('client_id' => $expense_info->paid_by), 'tbl_client');
        $currency = $this->invoice_model->client_currency_sambol($expense_info->paid_by);
        $client_lang = $client_info->language;
        unset($this->lang->is_loaded[5]);
        $language_info = $this->lang->load('sales_lang', $client_lang, TRUE, FALSE, '', TRUE);
        ?>

        <div class="panel-body">
            <div class="row mb-lg">
                <div class="col-xs-6 br pv">
                    <div class="row">

                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('reference_no') ?> <span
                                    class="text-danger">*</span></label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control" value="<?php
                                if (!empty($invoice_info)) {
                                    echo $invoice_info->reference_no;
                                } else {
                                    if (empty(config_item('invoice_number_format'))) {
                                        echo config_item('invoice_prefix');
                                    }
                                    if (config_item('increment_invoice_number') == 'FALSE') {
                                        $this->load->helper('string');
                                        echo random_string('nozero', 6);
                                    } else {
                                        echo $this->invoice_model->generate_invoice_number();
                                    }
                                }
                                ?>" name="reference_no">
                            </div>
                            <div class="btn btn-xs btn-info" id="start_recurring"><?= lang('recurring') ?></div>

                        </div>
                        <div id="recurring" class="hide">
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('recur_frequency') ?> </label>
                                <div class="col-lg-4">
                                    <select name="recuring_frequency" id="recuring_frequency"
                                            class="form-control">
                                        <option value="none"><?= lang('none') ?></option>
                                        <option
                                            value="7D"><?= lang('week') ?></option>
                                        <option
                                            value="1M"><?= lang('month') ?></option>
                                        <option
                                            value="3M"><?= lang('quarter') ?></option>
                                        <option
                                            value="6M"><?= lang('six_months') ?></option>
                                        <option
                                            value="1Y"><?= lang('1year') ?></option>
                                        <option
                                            value="2Y"><?= lang('2year') ?></option>
                                        <option
                                            value="3Y"><?= lang('3year') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('start_date') ?></label>
                                <div class="col-lg-7">
                                    <?php
                                    if (!empty($invoice_info) && $invoice_info->recurring == 'Yes') {
                                        $recur_start_date = date('Y-m-d', strtotime($invoice_info->recur_start_date));
                                        $recur_end_date = date('Y-m-d', strtotime($invoice_info->recur_end_date));
                                    } else {
                                        $recur_start_date = date('Y-m-d');
                                        $recur_end_date = date('Y-m-d');
                                    }
                                    ?>
                                    <div class="input-group">
                                        <input class="form-control datepicker" type="text"
                                               value="<?= $recur_start_date; ?>"
                                               name="recur_start_date"
                                               data-date-format="<?= config_item('date_picker_format'); ?>">
                                        <div class="input-group-addon">
                                            <a href="#"><i class="fa fa-calendar"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('end_date') ?></label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input class="form-control datepicker" type="text"
                                               value="<?= $recur_end_date; ?>"
                                               name="recur_end_date"
                                               data-date-format="<?= config_item('date_picker_format'); ?>">
                                        <div class="input-group-addon">
                                            <a href="#"><i class="fa fa-calendar"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('client') ?> <span
                                    class="text-danger">*</span>
                            </label>
                            <div class="col-lg-7">
                                <div class="input-group">
                                    <select class="form-control select_box" required style="width: 100%"
                                            name="client_id"
                                            onchange="get_project_by_id(this.value)">
                                        <option value="-"><?= lang('select') . ' ' . lang('client') ?></option>
                                        <?php
                                        $all_client = $this->db->get('tbl_client')->result();
                                        if (!empty($all_client)) {
                                            foreach ($all_client as $v_client) {
                                                if (!empty($expense_info->paid_by)) {
                                                    $client_id = $expense_info->paid_by;
                                                } elseif ($invoice_info->client_id) {
                                                    $client_id = $invoice_info->client_id;
                                                }
                                                ?>
                                                <option value="<?= $v_client->client_id ?>"
                                                    <?php
                                                    if (!empty($client_id)) {
                                                        echo $client_id == $v_client->client_id ? 'selected' : null;
                                                    }
                                                    ?>
                                                ><?= ucfirst($v_client->name) ?></option>
                                                <?php
                                            }
                                        }
                                        $acreated = can_action('4', 'created');
                                        ?>
                                    </select>
                                    <?php if (!empty($acreated)) { ?>
                                        <div class="input-group-addon"
                                             title="<?= lang('new') . ' ' . lang('client') ?>"
                                             data-toggle="tooltip" data-placement="top">
                                            <a data-toggle="modal" data-target="#myModal"
                                               href="<?= base_url() ?>admin/client/new_client"><i
                                                        class="fa fa-plus"></i></a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('project') ?></label>
                            <div class="col-lg-7">
                                <select class="form-control " style="width: 100%" name="project_id"
                                        id="client_project">
                                    <option value=""><?= lang('none') ?></option>
                                    <?php
                                    if (!empty($client_id)) {
                                        if (!empty($expense_info->project_id)) {
                                            $project_id = $expense_info->project_id;
                                        } elseif ($invoice_info->project_id) {
                                            $project_id = $invoice_info->project_id;
                                        }
                                        $all_project = $this->db->where('client_id', $client_id)->get('tbl_project')->result();
                                        if (!empty($all_project)) {
                                            foreach ($all_project as $v_project) {
                                                ?>
                                                <option value="<?= $v_project->project_id ?>" <?php
                                                if (!empty($project_id)) {
                                                    echo $v_project->project_id == $project_id ? 'selected' : '';
                                                }
                                                ?>><?= $v_project->project_name ?></option>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('due_date') ?></label>
                            <div class="col-lg-7">
                                <div class="input-group">
                                    <input type="text" name="due_date" class="form-control datepicker" value="<?php
                                    if (!empty($invoice_info->due_date)) {
                                        echo $invoice_info->due_date;
                                    } else {
                                        echo date('Y-m-d');
                                    }
                                    ?>" data-date-format="<?= config_item('date_picker_format'); ?>">
                                    <div class="input-group-addon">
                                        <a href="#"><i class="fa fa-calendar"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group" id="border-none">
                            <label for="field-1" class="col-sm-3 control-label"><?= lang('permission') ?> <span
                                    class="required">*</span></label>
                            <div class="col-sm-9">
                                <div class="checkbox c-radio needsclick">
                                    <label class="needsclick">
                                        <input id="" <?php
                                        if (!empty($invoice_info->permission) && $invoice_info->permission == 'all') {
                                            echo 'checked';
                                        } elseif (empty($invoice_info)) {
                                            echo 'checked';
                                        }
                                        ?> type="radio" name="permission" value="everyone">
                                        <span class="fa fa-circle"></span><?= lang('everyone') ?>
                                        <i title="<?= lang('permission_for_all') ?>"
                                           class="fa fa-question-circle" data-toggle="tooltip"
                                           data-placement="top"></i>
                                    </label>
                                </div>
                                <div class="checkbox c-radio needsclick">
                                    <label class="needsclick">
                                        <input id="" <?php
                                        if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                            echo 'checked';
                                        }
                                        ?> type="radio" name="permission" value="custom_permission"
                                        >
                                        <span class="fa fa-circle"></span><?= lang('custom_permission') ?> <i
                                            title="<?= lang('permission_for_customization') ?>"
                                            class="fa fa-question-circle" data-toggle="tooltip"
                                            data-placement="top"></i>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group <?php
                        if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                            echo 'show';
                        }
                        ?>" id="permission_user_1">
                            <label for="field-1"
                                   class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('users') ?>
                                <span
                                    class="required">*</span></label>
                            <div class="col-sm-9">
                                <?php
                                if (!empty($permission_user)) {
                                    foreach ($permission_user as $key => $v_user) {

                                        if ($v_user->role_id == 1) {
                                            $role = '<strong class="badge btn-danger">' . lang('admin') . '</strong>';
                                        } else {
                                            $role = '<strong class="badge btn-primary">' . lang('staff') . '</strong>';
                                        }

                                        ?>
                                        <div class="checkbox c-checkbox needsclick">
                                            <label class="needsclick">
                                                <input type="checkbox"
                                                    <?php
                                                    if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                                        $get_permission = json_decode($invoice_info->permission);
                                                        foreach ($get_permission as $user_id => $v_permission) {
                                                            if ($user_id == $v_user->user_id) {
                                                                echo 'checked';
                                                            }
                                                        }

                                                    }
                                                    ?>
                                                       value="<?= $v_user->user_id ?>"
                                                       name="assigned_to[]"
                                                       class="needsclick">
                                                        <span
                                                            class="fa fa-check"></span><?= $v_user->username . ' ' . $role ?>
                                            </label>

                                        </div>
                                        <div class="action_1 p
                                                <?php

                                        if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                            $get_permission = json_decode($invoice_info->permission);

                                            foreach ($get_permission as $user_id => $v_permission) {
                                                if ($user_id == $v_user->user_id) {
                                                    echo 'show';
                                                }
                                            }

                                        }
                                        ?>
                                                " id="action_1<?= $v_user->user_id ?>">
                                            <label class="checkbox-inline c-checkbox">
                                                <input id="<?= $v_user->user_id ?>" checked type="checkbox"
                                                       name="action_1<?= $v_user->user_id ?>[]"
                                                       disabled
                                                       value="view">
                                                        <span
                                                            class="fa fa-check"></span><?= lang('can') . ' ' . lang('view') ?>
                                            </label>
                                            <label class="checkbox-inline c-checkbox">
                                                <input id="<?= $v_user->user_id ?>"
                                                    <?php

                                                    if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                                        $get_permission = json_decode($invoice_info->permission);

                                                        foreach ($get_permission as $user_id => $v_permission) {
                                                            if ($user_id == $v_user->user_id) {
                                                                if (in_array('edit', $v_permission)) {
                                                                    echo 'checked';
                                                                };

                                                            }
                                                        }

                                                    }
                                                    ?>
                                                       type="checkbox"
                                                       value="edit" name="action_<?= $v_user->user_id ?>[]">
                                                        <span
                                                            class="fa fa-check"></span><?= lang('can') . ' ' . lang('edit') ?>
                                            </label>
                                            <label class="checkbox-inline c-checkbox">
                                                <input id="<?= $v_user->user_id ?>"
                                                    <?php

                                                    if (!empty($invoice_info->permission) && $invoice_info->permission != 'all') {
                                                        $get_permission = json_decode($invoice_info->permission);
                                                        foreach ($get_permission as $user_id => $v_permission) {
                                                            if ($user_id == $v_user->user_id) {
                                                                if (in_array('delete', $v_permission)) {
                                                                    echo 'checked';
                                                                };
                                                            }
                                                        }

                                                    }
                                                    ?>
                                                       name="action_<?= $v_user->user_id ?>[]"
                                                       type="checkbox"
                                                       value="delete">
                                                        <span
                                                            class="fa fa-check"></span><?= lang('can') . ' ' . lang('delete') ?>
                                            </label>
                                            <input id="<?= $v_user->user_id ?>" type="hidden"
                                                   name="action_<?= $v_user->user_id ?>[]" value="view">

                                        </div>


                                        <?php
                                    }
                                }
                                ?>


                            </div>
                        </div>
                        <?php
                        if (!empty($invoice_info)) {
                            $invoices_id = $invoice_info->invoices_id;
                        } else {
                            $invoices_id = null;
                        }
                        ?>
                        <?= custom_form_Fields(9, $invoices_id); ?>

                    </div>
                </div>
                <div class="col-xs-6 br pv">
                    <div class="row">
                        <?php if (config_item('paypal_status') == 'active'): ?>
                            <div class="form-group">
                                <label for="field-1"
                                       class="col-sm-4 control-label"><?= lang('allow_paypal') ?></label>
                                <div class="col-sm-7">
                                    <div class="checkbox c-checkbox">
                                        <label class="needsclick">
                                            <input type="checkbox" value="Yes"
                                                <?php if (!empty($invoice_info) && $invoice_info->allow_paypal == 'Yes') {
                                                    echo 'checked';
                                                } ?> name="allow_paypal">
                                            <span class="fa fa-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
                        <?php if (config_item('stripe_status') == 'active'): ?>
                            <div class="form-group">
                                <label for="field-1"
                                       class="col-sm-4 control-label"><?= lang('allow_stripe') ?></label>
                                <div class="col-sm-7">
                                    <div class="checkbox c-checkbox">
                                        <label class="needsclick">
                                            <input type="checkbox" value="Yes"
                                                <?php if (!empty($invoice_info) && $invoice_info->allow_stripe == 'Yes') {
                                                    echo 'checked';
                                                } ?>
                                                   name="allow_stripe"><span class="fa fa-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (config_item('2checkout_status') == 'active'): ?>
                            <div class="form-group">
                                <label for="field-1"
                                       class="col-sm-4 control-label"><?= lang('allow_2checkout') ?></label>

                                <div class="col-sm-7">

                                    <div class="checkbox c-checkbox">
                                        <label class="needsclick">
                                            <input type="checkbox" value="Yes"
                                                <?php if (!empty($invoice_info) && $invoice_info->allow_2checkout == 'Yes') {
                                                    echo 'checked';
                                                } ?>
                                                   name="allow_2checkout"><span class="fa fa-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (config_item('authorize_status') == 'active'): ?>
                            <div class="form-group">
                                <label for="field-1"
                                       class="col-sm-4 control-label"><?= lang('allow_authorize.net') ?></label>

                                <div class="col-sm-7">
                                    <div class="checkbox c-checkbox">
                                        <label class="needsclick">
                                            <input type="checkbox" value="Yes"
                                                <?php if (!empty($invoice_info) && $invoice_info->allow_authorize == 'Yes') {
                                                    echo 'checked';
                                                } ?>
                                                   name="allow_authorize"><span class="fa fa-check"></span>
                                        </label>
                                    </div>

                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (config_item('ccavenue_status') == 'active'): ?>
                            <div class="form-group">
                                <label for="field-1"
                                       class="col-sm-4 control-label"><?= lang('allow_ccavenue') ?></label>

                                <div class="col-sm-7">
                                    <div class="checkbox c-checkbox">
                                        <label class="needsclick">
                                            <input type="checkbox" value="Yes"
                                                <?php if (!empty($invoice_info) && $invoice_info->allow_ccavenue == 'Yes') {
                                                    echo 'checked';
                                                } ?>
                                                   name="allow_ccavenue"><span class="fa fa-check"></span>
                                        </label>
                                    </div>

                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (config_item('braintree_status') == 'active'): ?>
                            <div class="form-group">
                                <label for="field-1"
                                       class="col-sm-4 control-label"><?= lang('allow_braintree') ?></label>

                                <div class="col-sm-7">
                                    <div class="checkbox c-checkbox">
                                        <label class="needsclick">
                                            <input type="checkbox" value="Yes"
                                                <?php if (!empty($invoice_info) && $invoice_info->allow_braintree == 'Yes') {
                                                    echo 'checked';
                                                } ?>
                                                   name="allow_braintree"><span class="fa fa-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($project_id)): ?>
                            <div class="form-group">
                                <label for="field-1"
                                       class="col-sm-4 control-label"><?= lang('visible_to_client') ?>
                                    <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input data-toggle="toggle" name="client_visible" value="Yes" <?php
                                    if (!empty($invoice_info->client_visible) && $invoice_info->client_visible == 'Yes') {
                                        echo 'checked';
                                    }
                                    ?> data-on="<?= lang('yes') ?>" data-off="<?= lang('no') ?>"
                                           data-onstyle="success" data-offstyle="danger" type="checkbox">
                                </div>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
                <div class="col-sm-12 ">

                    <div class="">
                        <label class="col-lg-1 control-label"><?= lang('notes') ?> </label>
                        <div class="col-lg-11 row">
                        <textarea name="notes" class="textarea"><?php
                            if (!empty($invoice_info)) {
                                echo $invoice_info->notes;
                            } else {
                                echo $this->config->item('default_terms');
                            }
                            ?></textarea>
                        </div>
                    </div>
                </div>
            </div>


            <div class="table-responsive table-bordered   mb-lg">
                <table class="table " id="add_new">
                    <thead style="background: #e8e8e8">
                    <tr>
                        <th><?= $language_info['item_name'] ?></th>
                        <th><?= $language_info['description'] ?></th>

                        <th class="col-sm-1"><?= $language_info['qty'] ?></th>
                        <th class="col-sm-1"><?= $language_info['unit_price'] ?></th>
                        <th class="col-sm-1"><?= $language_info['tax_rate'] ?> </th>
                        <th class="col-sm-1"><?= $language_info['tax'] ?></th>
                        <th class="col-sm-1"><?= $language_info['total'] ?></th>
                        <th class="col-sm-1 hidden-print"><?= $language_info['action'] ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    $project_info = $this->db->where('project_id', $expense_info->project_id)->get('tbl_project')->row();
                    $category_info = $this->db->where('expense_category_id', $expense_info->category_id)->get('tbl_expense_category')->row();
                    if (!empty($category_info)) {
                        $category = $category_info->expense_category;
                    } else {
                        $category = 'Undefined Category';
                    }
                    ?>
                    <tr class="hidden-print">

                        <td>
                                                <textarea name="item_name[]" placeholder="Item Name"
                                                          class="form-control"><?php echo '[ ' . lang('expense') . ' ] - ' . $category . ' - ' . $expense_info->name; ?>
                                                </textarea>
                        </td>
                        <td><textarea rows="1" name="item_desc[]" placeholder="Item Description"
                                      class="form-control"><?php
                                if (!empty($expense_info->notes)) {
                                    $description = '[ ' . lang('expense') . ' ] - ' . $project_info->project_name . ' - ' . $expense_info->notes;
                                } else {
                                    $description = '[ ' . lang('expense') . ' ] - ' . $project_info->project_name;
                                }
                                echo $description;
                                ?></textarea></td>
                        <td><input type="number" data-parsley-type="number" name="quantity[]"
                                   value="<?php
                                   echo '1';
                                   ?>" placeholder="1"
                                   class="p_qty form-control"></td>

                        <td><input type="number" data-parsley-type="number" name="unit_cost[]"
                                   value="<?php
                                   if (!empty($expense_info->amount)) {
                                       echo $expense_info->amount;
                                   }
                                   ?>" placeholder="100" class="unit_cost form-control"></td>
                        <td>
                            <select name="item_tax_rate[]" class="form-control  ">
                                <option value="0.00"><?= lang('none') ?></option>
                                <?php
                                $tax_rates = $this->db->get('tbl_tax_rates')->result();
                                if (!empty($tax_rates)) {
                                    foreach ($tax_rates as $v_tax) {
                                        ?>
                                        <option value="<?= $v_tax->tax_rate_percent ?>" <?php
                                        if (!empty($item_info) && $item_info->item_tax_rate == $v_tax->tax_rate_percent) {
                                            echo 'selected';
                                        }
                                        ?>><?= $v_tax->tax_rate_name . ' (' . $v_tax->tax_rate_percent . '%)' ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td><input type="text" value="<?php
                            if (!empty($item_info)) {
                                echo $item_info->item_tax_total;
                            }
                            ?>" name="product_tax" placeholder="0.00" readonly="" class="form-control"></td>

                        <td>
                            <?php
                            $qty = 1;
                            $expense_total = $qty * $expense_info->amount;
                            ?>
                            <span class="linetotal"><?php echo $qty * $expense_info->amount; ?></span>
                        </td>

                        <td class="hidden-print">
                            <a href="javascript:void(0);" class="remCF btn btn-danger btn-xs"><i
                                    class="fa fa-trash-o"></i></a></td>
                    </tr>

                    <tr class="hidden-print">
                        <input type="hidden" name="invoices_id"
                               value="<?= (!empty($invoice_info->invoices_id)) ? $invoice_info->invoices_id : null ?>">
                        <td><textarea rows="1" name="item_name[]" placeholder="Item name" class="form-control"><?php
                                if (!empty($item_info)) {
                                    echo $item_info->item_name;
                                }
                                ?></textarea>
                        </td>
                        <td><textarea rows="1" name="item_desc[]" placeholder="Item Description"
                                      class="form-control"><?php
                                if (!empty($item_info)) {
                                    echo $item_info->item_desc;
                                }
                                ?></textarea></td>
                        <td><input type="number" data-parsley-type="number" name="quantity[]" value="<?php
                            if (!empty($item_info)) {
                                echo $item_info->quantity;
                            }
                            ?>" placeholder="1" class="form-control"></td>

                        <td><input type="number" data-parsley-type="number" name="unit_cost[]"
                                   value="<?php
                                   if (!empty($item_info)) {
                                       echo $item_info->unit_cost;
                                   }
                                   ?>" placeholder="100" class="form-control"></td>
                        <td>
                            <select name="item_tax_rate[]" class="form-control  ">
                                <option value="0.00"><?= lang('none') ?></option>
                                <?php
                                $tax_rates = $this->db->get('tbl_tax_rates')->result();
                                if (!empty($tax_rates)) {
                                    foreach ($tax_rates as $v_tax) {
                                        ?>
                                        <option value="<?= $v_tax->tax_rate_percent ?>" <?php
                                        if (!empty($item_info) && $item_info->item_tax_rate == $v_tax->tax_rate_percent) {
                                            echo 'selected';
                                        }
                                        ?>><?= $v_tax->tax_rate_name . ' (' . $v_tax->tax_rate_percent . '%)' ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td><input type="text" value="<?php
                            if (!empty($item_info)) {
                                echo $item_info->item_tax_total;
                            }
                            ?>" name="product_tax" placeholder="0.00" readonly="" class="form-control"></td>

                        <td><span class="linetotal"></span></td>

                        <td>
                            <strong><a href="javascript:void(0);" id="add_more" class="addCF "><i
                                        class="fa fa-plus"></i>&nbsp;&nbsp;More</a></strong>
                        </td>
                    </tr>
                    <table class="table" id="add_new">

                    </table>


                    </tbody>

                </table>
            </div>
            <?php
            $sub_total = $expense_total;
            $total = $language_info['total'];
            ?>
            <div class="row">
                <div class="col-xs-8">
                    <p class="well well-sm mt" style="visibility: hidden">

                    </p>
                </div>

                <div class="col-sm-4 pv">
                    <div class="clearfix">
                        <p class="pull-left"><?= $language_info['sub_total'] ?></p>
                        <p class="pull-right mr">
                            <?php echo $currency->symbol; ?>
                            <span id="sub_total"></span>
                        </p>
                    </div>

                    <div class="table clearfix">
                        <p class="pull-left"><?= $language_info['default_tax'] ?> (%)
                            <input style="width: 50%;" type="number" data-parsley-type="number" name="tax"
                                   value="<?php echo $this->config->item('default_tax') ?>"
                                   class="pull-right form-control">
                        </p>
                        <p class="pull-right mr">
                            <?php echo $currency->symbol; ?>
                            <span id="default_tax"></span>
                        </p>
                    </div>
                    <div class="table clearfix">
                        <p class="pull-left"><?= $language_info['discount'] ?> (
                            %)
                            <input style="width: 50%;" type="number" data-parsley-type="number" name="discount"
                                   class="pull-right form-control">
                        </p>
                        <p class="pull-right mr">
                            <?php echo $currency->symbol; ?> -
                            <span id="discount"></span>
                        </p>
                    </div>

                    <div class="table clearfix">
                        <p class="pull-left h3"><?= $total ?></p>
                        <p class="pull-right mr h3"><?php echo $currency->symbol; ?>
                            <span id="grand_total"></span></p>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="transactions_id"
               value="<?= (!empty($expense_info->transactions_id)) ? $expense_info->transactions_id : null ?>">
        <script type="text/javascript">
            $(document).ready(function () {
                $(".table").on("change", 'input[name^="unit_cost[]"], input[name^="quantity[]"], input[name^="tax"], input[name^="discount"], select[name^="item_tax_rate[]"]', function (event) {
                    calculateRow($(this).closest("tr"));
                    calculateGrandTotal();
                });
                calculateGrandTotal();
            });

            function calculateRow(row) {
                var price = +row.find('input[name^="unit_cost[]"]').val();
                var qty = +row.find('input[name^="quantity[]"]').val();
                var tax = +row.find('select[name^="item_tax_rate[]"]').val();
                var linetotal = price * qty;
                total_tax = ((tax / 100) * linetotal).toFixed(2);
                row.find('input[name^="product_tax"]').val(total_tax);
                line_total = parseFloat(linetotal) + parseFloat(total_tax);
                row.find('span[class^="linetotal"]').text(line_total.toFixed(2));
            }

            function calculateGrandTotal() {
                var sub_total = 0;
                $(".table").find('span[class^="linetotal"]').each(function () {
                    if ($(this).text()) {
                        sub_total += +$(this).text();
                    }
                });

                var tax = $(".table").find('input[name^="tax"]').val();
                var default_tax = ((tax / 100) * sub_total).toFixed(2);
                var discount = $(".table").find('input[name^="discount"]').val();
                var total_discount = ((discount / 100) * sub_total).toFixed(2);
                var grand_total = parseFloat(sub_total) + parseFloat(default_tax) - parseFloat(total_discount);
                $("#default_tax").text(default_tax);
                $("#discount").text(total_discount);
                $("#sub_total").text(sub_total.toFixed(2));
                $("#grand_total").text(grand_total.toFixed(2));

            }
        </script>
        <div class="modal-footer">
            <a href="<?= base_url() ?>admin/projects/project_details/<?= $expense_info->project_id ?>"
               class="btn btn-default"><?= lang('close') ?></a>
            <input type="submit" value="<?= lang('save_as_draft') ?>" name="save_as_draft" class="btn btn-primary">
            <input type="submit" value="<?= lang('update') ?>" name="update" class="btn btn-success">
        </div>
    </div>
</form>
<script type="text/javascript">

    $(document).ready(function () {
        $('#start_recurring').click(function () {
            $('#recurring').slideToggle("fast");
            $('#recurring').removeClass("hide");
            $('#recuring_frequency').prop('disabled', false);
        });
    });

    function slideToggle($id) {
        $($id).slideToggle("slow");
    }
    $(document).ready(function () {
        $("#select_all_tasks").click(function () {
            $(".tasks_list").prop('checked', $(this).prop('checked'));
        });
        $("#select_all_expense").click(function () {
            $(".expense_list").prop('checked', $(this).prop('checked'));
        });
        $('[data-toggle="popover"]').popover();

    });

    $(document).ready(function () {
        var maxAppend = 0;
        $("#add_more").click(function () {

            var add_new = $('<tr style="">\n\
                    <td><textarea rows="1" name="item_name[]" required placeholder="Item name" class="form-control"></textarea></td>">\n\
                    <td><textarea rows="1" name="item_desc[]" placeholder="Item Description" class="form-control"></textarea></td>\n\
                        <td class="col-sm-1"><input type="number" data-parsley-type="number" name="quantity[]" placeholder="1" required class="form-control"></td>\n\
                        <td class="col-sm-1"><input type="number" data-parsley-type="number" name="unit_cost[]" required placeholder="100" class="form-control"></td>\n\
                        <td ><select name="item_tax_rate[]" class="form-control"><option value="0.00"><?= lang('none') ?></option>\n\\n\
<?php
                $tax_rates = $this->db->get('tbl_tax_rates')->result();
                if (!empty($tax_rates)) {
                foreach ($tax_rates as $v_tax) {
                ?><option value="<?= $v_tax->tax_rate_percent ?>"><?= $v_tax->tax_rate_name ?></option><?php
                }
                }
                ?></select></td>\n\
<td class="col-sm-1"><input type="text" name="product_tax" placeholder="0.00" readonly="" class="form-control"></td>\n\
<td><span class="linetotal"></span></td>\n\
<td><a href="javascript:void(0);" class="remCF btn btn-danger btn-xs"><i class="fa fa-trash-o"></i></a></strong></td></tr>\n<br/>');
            maxAppend++;
            $("#add_new").append(add_new);
        });

        $("#add_new").on('click', '.remCF', function () {
            $(this).parent().parent().remove();
            calculateGrandTotal();
        });
    });
</script>

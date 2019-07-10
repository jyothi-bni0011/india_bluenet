<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
        <h4 class="modal-title">Paying
            <strong><?= display_money($invoice_info['amount'], $invoice_info['currency']); ?></strong> for Invoice
            #<?= $invoice_info['item_name'] ?> via Stripe</h4>
    </div>
    <div class="modal-body">
        <?php
        $allow_customer_edit_amount = config_item('allow_customer_edit_amount');
        ?>
        <?php
        if (!empty($post)) {
            $form = '<form name="redirect" action="' . site_url('payment/stripe/purchase') . '" method="POST">'; ?>
            <?php $form .= '<script
            src="https://checkout.stripe.com/checkout.js" class="stripe-button"
            data-key="' . config_item('stripe_public_key') . '"
            data-amount="' . ($amount * 100) . '"
            data-image="' . base_url() . config_item('company_logo') . '"
            data-name="' . config_item('company_name') . '"
            data-billing-address="true"
            data-description="Invoice ' . $item_name . ' via Stripe " ;
            data-locale="auto"
            data-currency="' . $currency . '">
        </script>
        ' . form_hidden('invoice_id', $invoice_info['item_number']) . '
        ' . form_hidden('amount', $amount) . '
        ' . form_hidden('currency', $invoice_info['currency']) . '
        </form>';
            echo $form;
        }else{
        ?>
        <form action="<?= base_url($this->uri->uri_string()) ?>" method="post">
            <div id="payment-errors"></div>
            <input type="hidden" name="invoice_id" value="<?= $invoice_info['item_number'] ?>">
            <input type="hidden" name="currency" value="<?= $invoice_info['currency'] ?>">
            <?php if (!empty($allow_customer_edit_amount) && $allow_customer_edit_amount == 'No') { ?>
                <input name="amount" id="amount" value="<?= $invoice_info['amount'] ?>" type="hidden">
            <?php } ?>
            <div class="form-group">
                <label class="col-lg-4 control-label"><?= lang('amount') ?> ( <?= $invoice_info['currency'] ?>) </label>
                <div class="col-lg-4">
                    <?php if (!empty($allow_customer_edit_amount) && $allow_customer_edit_amount == 'Yes') { ?>
                        <input type="text" id="amount" required name="amount" data-parsley-type="number"
                               data-parsley-max="<?= $invoice_info['amount'] ?>" class="form-control"
                               value="<?= ($invoice_info['amount']) ?>">
                    <?php } else { ?>
                        <input type="text" class="form-control" value="<?= display_money($invoice_info['amount']) ?>"
                               readonly>
                    <?php } ?>
                </div>
            </div>
            <div class="form-group">
                <a href="#" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></a>
                <input type="submit" value="Submit Payment" class="btn btn-success"/>
            </div>
        </form>
    </div>
    <?php } ?>
</div>

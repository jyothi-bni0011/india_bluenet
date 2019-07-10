<?php
$group = $this->uri->segment(4);

if (!empty($group)) {
    $group = $group;
} else {
    $group = 'user';
}
$template_group = $group;

$editor = $this->data;
switch ($template_group) {
    case "extra":
        $default = "estimate_email";
        break;
    case "invoice":
        $default = "invoice_message";
        break;
    case "tasks":
        $default = "task_assigned";
        break;
    case "bugs":
        $default = "bug_assigned";
        break;
    case "project":
        $default = "client_notification";
        break;
    case "ticket":
        $default = "ticket_client_email";
        break;
    case "hrm":
        $default = "leave_request_email";
        break;
    case "user":
        $default = "activate_account";
        break;
}
$sub_menu = $this->uri->segment(5);
if (!empty($sub_menu)) {
    $sub_menu = $sub_menu;
} else {
    $sub_menu = $default;
}
$setting_email = $sub_menu;

$email['extra'] = array("estimate_email","estimate_overdue_email", "proposal_email", "proposal_overdue_email", "message_received", "quotations_form", "goal_achieve", "goal_not_achieve");
$email['invoice'] = array("invoice_message", "invoice_reminder", "payment_email", "invoice_overdue_email", "refund_confirmation");
$email['tasks'] = array("task_assigned", "tasks_comments", "tasks_attachment", "tasks_updated");
$email['bugs'] = array("bug_assigned", "bug_comments", "bug_attachment", "bug_updated", 'bug_reported');
$email['project'] = array("client_notification", "assigned_project", 'complete_projects', "project_comments", "project_attachment", 'responsible_milestone', 'project_overdue_email');
$email['ticket'] = array("ticket_client_email", "ticket_closed_email", "ticket_reply_email", "ticket_staff_email", "auto_close_ticket", "ticket_reopened_email");
$email['user'] = array("activate_account", "change_email", "forgot_password", "registration", "reset_password", 'wellcome_email');
$email['hrm'] = array("leave_request_email", "leave_approve_email", "leave_reject_email", "overtime_request_email", "overtime_approved_email", "overtime_reject_email", "payslip_generated_email"
, "advance_salary_email", "advance_salary_approve_email", "advance_salary_reject_email", "award_email", "new_job_application_email", "call_for_interview"
, "new_notice_published", "new_training_email", 'deposit_email', 'expense_request_email', 'expense_approved_email', 'expense_paid_email', 'trying_clock_email','clock_in_email', 'clock_out_email');
//"performance_appraisal_email"
?>
<form action="<?= base_url() ?>admin/settings/templates/<?= $setting_email ?>" method="post"
      class="bs-example form-horizontal">
    <section class="panel panel-custom">
        <header class="panel-heading  "> <?= lang('email_templates') ?>
            <div class="btn-group pull-right" style="margin-bottom: 10px;">
                <button type="button" class="btn btn-xs btn-primary" title="Filter" data-toggle="dropdown"><i
                        class="fa fa-cogs"></i> <?= lang('choose_template') ?><span class="caret"></span></button>
                <ul class="dropdown-menu animated zoomIn">
                    <li><a href="<?= base_url() ?>admin/settings/templates/user"><?= lang('account_emails') ?></a></li>
                    <li><a href="<?= base_url() ?>admin/settings/templates/invoice"><?= lang('invoicing_emails') ?></a>
                    </li>
                    <li><a href="<?= base_url() ?>admin/settings/templates/tasks"><?= lang('tasks_email') ?></a></li>
                    <li><a href="<?= base_url() ?>admin/settings/templates/bugs"><?= lang('bugs_email') ?></a></li>
                    <li><a href="<?= base_url() ?>admin/settings/templates/project"><?= lang('project_emails') ?></a>
                    </li>
                    <li><a href="<?= base_url() ?>admin/settings/templates/ticket"><?= lang('ticketing_emails') ?></a>
                    </li>
                    <li><a href="<?= base_url() ?>admin/settings/templates/hrm"><?= lang('hrm_emails') ?></a>
                    </li>
                    <li class="divider"></li>
                    <li><a href="<?= base_url() ?>admin/settings/templates/extra"><?= lang('extra_emails') ?></a></li>
                </ul>
            </div>
        </header>
        <div class="panel-body">
            <div class="col-sm-12">
                <div class="btn-group">
                    <?php
                    foreach ($email[$template_group] as $temp) :
                        $lang = $temp;

                        switch ($temp) {
                            case "registration":
                                $lang = 'register_email';
                        }
                        ?>
                        <a href="<?= base_url() ?>admin/settings/templates/<?= $template_group; ?>/<?= $temp; ?>"
                           class="<?php
                           if ($setting_email == $temp) {
                               echo "active";
                           }
                           ?> btn btn-default mb-sm"><?= lang($lang) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" name="email_group" value="<?= $setting_email; ?>">
            <input type="hidden" name="return_url"
                   value="<?= base_url() ?>admin/settings/templates/<?= $template_group . '/' . $setting_email; ?>">
            <div class="form-group">
                <label class="col-lg-12"><?= lang('subject') ?></label>
                <div class="col-lg-12">
                    <input class="form-control" name="subject" value="<?=
                    $this->settings_model->get_any_field('tbl_email_templates', array(
                        'email_group' => $setting_email
                    ), 'subject')
                    ?>"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-12"><?= lang('message') ?></label>
                <div class="col-lg-12">
                            <textarea class="form-control" id="ck_editor" style="height: 600px;" name="email_template">
                                <?=
                                $this->settings_model->get_any_field('tbl_email_templates', array(
                                    'email_group' => $setting_email
                                ), 'template_body')
                                ?></textarea>
                    <?php echo display_ckeditor($editor['ckeditor']); ?>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-sm btn-primary"><?= lang('save_changes') ?></button>
        </div>
    </section>
</form>
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Return_stock extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('return_stock_model');
    }

    public function index($id = NULL)
    {
        $data['title'] = lang('all') . ' ' . lang('return_stock');
        if (!empty($id)) {
            $data['active'] = 2;
            $edited = can_action('153', 'edited');
            if (!empty($edited) && is_numeric($id)) {
                $data['return_stock_info'] = $this->return_stock_model->check_by(array('return_stock_id' => $id), 'tbl_return_stock');
            }
        } else {
            $data['active'] = 1;
        }
        $data['dropzone'] = true;
        $data['all_return_stocks'] = $this->return_stock_model->get_permission('tbl_return_stock');
        $data['permission_user'] = $this->return_stock_model->all_permission_user('153');
        $data['all_supplier'] = $this->return_stock_model->get_permission('tbl_suppliers');
        $data['subview'] = $this->load->view('admin/return_stock/manage_return_stock', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function return_stockList()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_return_stock';
            $this->datatables->join_table = array('tbl_suppliers');
            $this->datatables->join_where = array('tbl_suppliers.supplier_id=tbl_return_stock.supplier_id');
            $this->datatables->column_order = array('reference_no', 'tbl_suppliers.name', 'return_stock_date', 'due_date', 'status', 'amount');
            $this->datatables->column_search = array('reference_no', 'tbl_suppliers.name', 'return_stock_date', 'due_date', 'status', 'amount');
            $this->datatables->order = array('return_stock_id' => 'desc');
            $fetch_data = make_datatables();

            $data = array();

            $edited = can_action('153', 'edited');
            $deleted = can_action('153', 'deleted');
            foreach ($fetch_data as $_key => $v_return_stock) {
                if (!empty($v_return_stock)) {
                    $action = null;
                    $sub_array = array();
                    $can_edit = $this->return_stock_model->can_action('tbl_return_stock', 'edit', array('return_stock_id' => $v_return_stock->return_stock_id));
                    $can_delete = $this->return_stock_model->can_action('tbl_return_stock', 'delete', array('return_stock_id' => $v_return_stock->return_stock_id));

                    $currency = $this->return_stock_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');

                    $sub_array[] = '<a href="' . base_url() . 'admin/return_stock/return_stock_details/' . $v_return_stock->return_stock_id . '">' . ($v_return_stock->reference_no) . '</a>';
                    $sub_array[] = !empty($v_return_stock) ? $v_return_stock->name : '-';
                    $sub_array[] = display_date($v_return_stock->return_stock_date);
                    $sub_array[] = display_money($this->return_stock_model->calculate_to('return_stock_due', $v_return_stock->return_stock_id), $currency->symbol);
                    $status = $v_return_stock->status;
                    if ($status == ('accepted') || $status == ('paid')) {
                        $label = "success";
                    } elseif ($status == ('draft')) {
                        $label = "default";
                    } elseif ($status == ('cancelled')) {
                        $label = "danger";
                    } elseif ($status == ('declined')) {
                        $label = "warning";
                    } elseif ($status == 'sent') {
                        $label = "info";
                    } else {
                        $label = "danger";
                    }

                    $sub_array[] = '<span class="badge bg-' . $label . '">' . lang($status) . '</span>';


                    if (!empty($can_edit) && !empty($edited)) {
                        $action .= btn_edit('admin/return_stock/index/' . $v_return_stock->return_stock_id) . ' ';
                    }
                    if (!empty($can_delete) && !empty($deleted)) {
                        $action .= ajax_anchor(base_url("admin/return_stock/delete_return_stock/" . $v_return_stock->return_stock_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                    }
                    if (!empty($can_edit) && !empty($edited)) {
                        $action .= btn_view('admin/return_stock/return_stock_details/' . $v_return_stock->return_stock_id) . ' ';
                    }
                    $sub_array[] = $action;
                    $data[] = $sub_array;
                }
            }

            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function save_return_stock($id = NULL)
    {
        $data = $this->return_stock_model->array_from_post(array('reference_no', 'supplier_id', 'discount_type', 'discount_percent', 'user_id', 'adjustment', 'discount_total', 'show_quantity_as'));

        $data['update_stock'] = ($this->input->post('update_stock') == 'Yes') ? 'Yes' : 'No';
        $data['return_stock_date'] = date('Y-m-d', strtotime($this->input->post('return_stock_date', TRUE)));
        if (empty($data['return_stock_date'])) {
            $data['return_stock_date'] = date('Y-m-d');
        }
        $data['due_date'] = date('Y-m-d', strtotime($this->input->post('due_date', TRUE)));
        $data['notes'] = $this->input->post('notes', TRUE);
        $tax['tax_name'] = $this->input->post('total_tax_name', TRUE);
        $tax['total_tax'] = $this->input->post('total_tax', TRUE);
        $data['total_tax'] = json_encode($tax);
        $i_tax = 0;
        if (!empty($tax['total_tax'])) {
            foreach ($tax['total_tax'] as $v_tax) {
                $i_tax += $v_tax;
            }
        }
        $data['tax'] = $i_tax;

        $permission = $this->input->post('permission', true);
        if (!empty($permission)) {
            if ($permission == 'everyone') {
                $assigned = 'all';
            } else {
                $assigned_to = $this->return_stock_model->array_from_post(array('assigned_to'));
                if (!empty($assigned_to['assigned_to'])) {
                    foreach ($assigned_to['assigned_to'] as $assign_user) {
                        $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                    }
                }
            }
            if (!empty($assigned)) {
                if ($assigned != 'all') {
                    $assigned = json_encode($assigned);
                }
            } else {
                $assigned = 'all';
            }
            $data['permission'] = $assigned;
        } else {
            set_message('error', lang('assigned_to') . ' Field is required');
            redirect($_SERVER['HTTP_REFERER']);
        }
        // get all client
        $this->return_stock_model->_table_name = 'tbl_return_stock';
        $this->return_stock_model->_primary_key = 'return_stock_id';
        if (!empty($id)) {
            $return_stock_id = $id;
            $this->return_stock_model->save($data, $id);
            $action = ('return_stock_updated');
            $msg = lang('return_stock_updated');

        } else {
            $data['created_by'] = my_id();
            $return_stock_id = $this->return_stock_model->save($data);
            $action = ('return_stock_created');
            $msg = lang('return_stock_created');
        }

        $removed_items = $this->input->post('removed_items', TRUE);
        if (!empty($removed_items)) {
            foreach ($removed_items as $r_id) {
                if ($r_id != 'undefined') {
                    $this->return_items($r_id);
                    $this->db->where('items_id', $r_id);
                    $this->db->delete('tbl_return_stock_items');
                }
            }
        }
        $items_data = $this->input->post('items', true);
        if (!empty($items_data)) {
            $index = 0;
            foreach ($items_data as $items) {
                $items['return_stock_id'] = $return_stock_id;
                $tax = 0;
                if (!empty($items['taxname'])) {
                    foreach ($items['taxname'] as $tax_name) {
                        $tax_rate = explode("|", $tax_name);
                        $tax += $tax_rate[1];
                    }
                    $items['item_tax_name'] = $items['taxname'];
                    unset($items['taxname']);
                    $items['item_tax_name'] = json_encode($items['item_tax_name']);
                }
                if (empty($items['saved_items_id'])) {
                    $items['saved_items_id'] = 0;
                }
                if ($data['update_stock'] == 'Yes') {
                    if (!empty($items['saved_items_id']) && $items['saved_items_id'] != 'undefined') {
                        if (!empty($items['items_id'])) {
                            $old_quantity = get_any_field('tbl_return_stock_items', array('items_id' => $items['items_id']), 'quantity');
                            if ($old_quantity != $items['quantity']) {
                                // $a < $b	Less than TRUE if $a is strictly less than $b.
                                // $a > $b	Greater than TRUE if $a is strictly greater than $b.
                                if ($old_quantity > $items['quantity']) {
                                    $quantity = $old_quantity - $items['quantity'];
                                    $this->return_stock_model->return_items($items['saved_items_id'], $quantity);
                                } else {
                                    $quantity = $items['quantity'] - $old_quantity;
                                    $this->return_stock_model->reduce_items($items['saved_items_id'], $quantity);
                                }
                            }
                        } else {
                            $this->return_stock_model->reduce_items($items['saved_items_id'], $items['quantity']);
                        }
                    }
                }

                $price = $items['quantity'] * $items['unit_cost'];
                $items['item_tax_total'] = ($price / 100 * $tax);
                $items['total_cost'] = $price;
                // get all client
                $this->return_stock_model->_table_name = 'tbl_return_stock_items';
                $this->return_stock_model->_primary_key = 'items_id';
                if (!empty($items['items_id'])) {
                    $items_id = $items['items_id'];
                    $this->return_stock_model->save($items, $items_id);
                } else {
                    $items_id = $this->return_stock_model->save($items);
                }
                $index++;
            }
        }
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'return_stock',
            'module_field_id' => $return_stock_id,
            'activity' => $action,
            'icon' => 'fa fa-truck',
            'link' => 'admin/return_stock/return_stock_details/' . $return_stock_id,
            'value1' => $data['reference_no']
        );
        $this->return_stock_model->_table_name = 'tbl_activities';
        $this->return_stock_model->_primary_key = 'activities_id';
        $this->return_stock_model->save($activity);

        // messages for user
        $type = "success";
        $message = $msg;
        set_message($type, $message);
        redirect('admin/return_stock/return_stock_details/' . $return_stock_id);
    }

    function return_items($items_id)
    {
        $items_info = $this->db->where('items_id', $items_id)->get('tbl_return_stock_items')->row();
        if (!empty($items_info->saved_items_id)) {
            $this->return_stock_model->return_items($items_info->saved_items_id, $items_info->quantity);
        }
        return true;
    }

    public function return_stock_details($id)
    {
        $data['title'] = lang('return_stock') . ' ' . lang('details'); //Page title
        $data['return_stock_info'] = $this->return_stock_model->check_by(array('return_stock_id' => $id), 'tbl_return_stock');
        if (empty($data['return_stock_info'])) {
            set_message('error', lang('there_in_no_value'));
            redirect('admin/return_stock/manage_return_stock');
        }
        $data['subview'] = $this->load->view('admin/return_stock/return_stock_details', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }


    public
    function change_status($action, $id)
    {
        $return_stock_info = $this->return_stock_model->check_by(array('return_stock_id' => $id), 'tbl_return_stock');
        $return_stock_items = $this->db->where('return_stock_id', $id)->get('tbl_return_stock_items')->result();
        if ($action == 'mark_as_sent') {
            $data = array('emailed' => 'Yes', 'date_sent' => date("Y-m-d:s", time()));
        } elseif ($action == 'mark_as_cancelled') {
            $data = array('status' => 'cancelled');
        } elseif ($action == 'unmark_as_cancelled') {
            $data = array('status' => 'pending');
        } elseif ($action == 'declined') {
            $data = array('status' => 'declined');
        } elseif ($action == 'accepted') {
            $data = array('status' => 'accepted');
        } else {
            $data = array('status' => $action);
        }
        $this->return_stock_model->_table_name = 'tbl_return_stock';
        $this->return_stock_model->_primary_key = 'return_stock_id';
        $this->return_stock_model->save($data, $id);

        // messages for user
        $type = "success";
        $imessage = lang('return_stock_update');
        set_message($type, $imessage);
        redirect('admin/return_stock/return_stock_details/' . $id);
    }

    function send_return_stock_email($return_stock_id)
    {
        $return_stock_info = $this->return_stock_model->check_by(array('return_stock_id' => $return_stock_id), 'tbl_return_stock');
        $supplier_info = $this->return_stock_model->check_by(array('supplier_id' => $return_stock_info->supplier_id), 'tbl_suppliers');
        $currency = $this->return_stock_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
        $message = " < p>Hello $supplier_info->name </p >
<p >&nbsp;</p >

<p > This is a return_stock details of " . display_money($this->return_stock_model->calculate_to('total', $return_stock_info->return_stock_id), $currency->symbol) . " < br />
Please check the attachment bellow:<br />
<br />
Best Regards,<br />
The " . config_item('company_name') . " Team </p > ";
        $params = array(
            'recipient' => $supplier_info->email,
            'subject' => '[ ' . config_item('company_name') . ' ]' . ' return_stock' . ' ' . $return_stock_info->reference_no,
            'message' => $message
        );
        $params['resourceed_file'] = 'uploads/' . lang('return_stock') . '_' . $return_stock_info->reference_no . '.pdf';
        $params['resourcement_url'] = base_url() . 'uploads/' . lang('return_stock') . '_' . $return_stock_info->reference_no . '.pdf';
        $this->attach_pdf($return_stock_id);
        $this->return_stock_model->send_email($params);
        //Delete invoice in tmp folder
        if (is_file('uploads/' . lang('return_stock') . '_' . $return_stock_info->reference_no . '.pdf')) {
            unlink('uploads/' . lang('return_stock') . '_' . $return_stock_info->reference_no . '.pdf');
        }

        $data = array('emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()));

        $this->return_stock_model->_table_name = 'tbl_return_stock';
        $this->return_stock_model->_primary_key = 'return_stock_id';
        $this->return_stock_model->save($data, $return_stock_info->return_stock_id);

        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'return_stock',
            'module_field_id' => $return_stock_info->return_stock_id,
            'activity' => ('activity_return_stock_sent'),
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/return_stock/return_stock_details/' . $return_stock_info->return_stock_id,
            'value1' => $return_stock_info->reference_no,
            'value2' => display_money($this->return_stock_model->calculate_to('total', $return_stock_info->return_stock_id), $currency->symbol),
        );
        $this->return_stock_model->_table_name = 'tbl_activities';
        $this->return_stock_model->_primary_key = 'activities_id';
        $this->return_stock_model->save($activity);
        // messages for user
        $type = "success";
        $imessage = lang('invoice_sent');
        set_message($type, $imessage);
        redirect('admin/return_stock/return_stock_details/' . $return_stock_info->return_stock_id);
    }

    public function attach_pdf($id)
    {
        $data['page'] = lang('return_stock');
        $data['return_stock_info'] = $this->return_stock_model->check_by(array('return_stock_id' => $id), 'tbl_return_stock');
        $data['title'] = lang('invoices'); //Page title
        $this->load->helper('dompdf');
        $html = $this->load->view('admin/return_stock/return_stock_pdf', $data, TRUE);
        $result = pdf_create($html, lang('return_stock') . '_' . $data['return_stock_info']->reference_no, 1, null, true);
        return $result;
    }

    public function pdf_return_stock($id)
    {
        $data['return_stock_info'] = $this->return_stock_model->check_by(array('return_stock_id' => $id), 'tbl_return_stock');
        $data['title'] = lang('return_stock') . ' ' . "PDF"; //Page title
        $this->load->helper('dompdf');
        $viewfile = $this->load->view('admin/return_stock/return_stock_pdf', $data, TRUE);

        pdf_create($viewfile, lang('return_stock') . '# ' . $data['return_stock_info']->reference_no);
    }

    public function delete_return_stock($id)
    {
        $deleted = can_action('153', 'deleted');
        $can_delete = $this->return_stock_model->can_action('tbl_return_stock', 'delete', array('return_stock_id' => $id));
        if (!empty($can_delete) && !empty($deleted)) {
            $return_stock_info = $this->return_stock_model->check_by(array('return_stock_id' => $id), 'tbl_return_stock');
            $return_stock_items_info = $this->return_stock_model->check_by(array('return_stock_id' => $id), 'tbl_return_stock_items');
            if ($return_stock_info->update_stock == 'Yes') {
                if (!empty($return_stock_items_info)) {
                    foreach ($return_stock_items_info as $v_items) {
                        if ($v_items->saved_items_id != 0) {
                            $this->return_stock_model->return_items($v_items->saved_items_id, $v_items->quantity);
                        }
                    }
                }
            }
            $this->return_stock_model->_table_name = 'tbl_return_stock_items';
            $this->return_stock_model->delete_multiple(array('return_stock_id' => $id));

            $this->return_stock_model->_table_name = 'tbl_return_stock';
            $this->return_stock_model->_primary_key = 'return_stock_id';
            $this->return_stock_model->delete($id);

            $type = "success";
            if (!empty($return_stock_info->reference_no)) {
                $val = $return_stock_info->reference_no;
            } else {
                $val = NULL;
            }
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'return_stock',
                'module_field_id' => $id,
                'activity' => ('activity_delete_return_stock'),
                'icon' => 'fa fa-truck',
                'value1' => $val,

            );
            $this->return_stock_model->_table_name = 'tbl_activities';
            $this->return_stock_model->_primary_key = 'activities_id';
            $this->return_stock_model->save($activity);

            echo json_encode(array("status" => $type, 'message' => lang('activity_delete_return_stock')));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('there_in_no_value')));
            exit();
        }
    }

}

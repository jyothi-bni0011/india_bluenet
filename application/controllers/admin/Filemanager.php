<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
ini_set('memory_limit', '-1');
// set max execution time 2 hours / mostly used for exporting PDF
ini_set('max_execution_time', 3600);

class Filemanager extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
    }

    public function index()
    {
        $data['title'] = lang('filemanager');
        $data['subview'] = $this->load->view('admin/filemanager/filemanager', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }
    public function sample()
    {
        $data['title'] = lang('filemanager');
        $data['subview'] = $this->load->view('admin/filemanager/sample', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }
    public function sample_form()
    {
        $data['title'] = lang('filemanager');
        $data['subview'] = $this->load->view('admin/filemanager/sample_form', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function elfinder_init()
    {
        if ($this->input->is_ajax_request() || $this->input->post()) {
            $this->load->helper('path');
            $_allowed_files = explode('|', config_item('allowed_files'));
            $config_allowed_files = array();
            if (is_array($_allowed_files)) {
                foreach ($_allowed_files as $v_extension) {
                    array_push($config_allowed_files, '.' . $v_extension);
                }
            }

            $allowed_files = array();
            if (is_array($config_allowed_files)) {
                foreach ($config_allowed_files as $extension) {
                    $_mime = get_mime_by_extension($extension);

                    if ($_mime == 'application/x-zip') {
                        array_push($allowed_files, 'application/zip');
                    }
                    if ($extension == '.exe') {
                        array_push($allowed_files, 'application/x-executable');
                        array_push($allowed_files, 'application/x-msdownload');
                        array_push($allowed_files, 'application/x-ms-dos-executable');
                    }
                    array_push($allowed_files, $_mime);
                }
            }

            $root_options = array(
                'driver' => 'LocalFileSystem',
                'path' => set_realpath('filemanager'),
                'URL' => site_url('filemanager') . '/',
                'uploadMaxSize' => config_item('max_file_size') . 'M',
                'accessControl' => 'access',
                'uploadAllow' => $allowed_files,
                'uploadOrder' => array(
                    'allow',
                    'deny'
                ),
                'attributes' => array(
                    array(
                        'pattern' => '/.tmb/',
                        'hidden' => true
                    ),
                    array(
                        'pattern' => '/.quarantine/',
                        'hidden' => true
                    )
                )
            );
            if ($this->session->userdata('user_type') == 3) {
                $user = $this->db->where('user_id', $this->session->userdata('user_id'))->get('tbl_users')->row();
                $path = set_realpath('filemanager/' . $user->media_path_slug);
                if (empty($user->media_path_slug)) {
                    $this->db->where('user_id', $user->user_id);
                    $slug = slug_it($user->username);
                    $this->db->update('tbl_users', array(
                        'media_path_slug' => $slug
                    ));
                    $user->media_path_slug = $slug;
                    $path = set_realpath('filemanager/' . $user->media_path_slug);
                }
                if (!is_dir($path)) {
                    mkdir($path);
                }
                if (!file_exists($path . '/index.html')) {
                    fopen($path . '/index.html', 'w');
                }
                array_push($root_options['attributes'], array(
                    'pattern' => '/.(' . $user->media_path_slug . '+)/', // Prevent deleting/renaming folder
                    'read' => true,
                    'write' => true,
                    'locked' => true
                ));
                $root_options['path'] = $path;
                $root_options['URL'] = site_url('filemanager/' . $user->media_path_slug) . '/';
            }
            $opts = array(
                'roots' => array(
                    $root_options
                )
            );
            $this->load->library('elfinder_lib', $opts);
        } else {
            redirect('admin/dashboard');
        }
    }

}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Group extends My_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Sys/User_group_access_model');

		$this->auth->canAccessApi('system_user_group');
	}

	public function data()
	{
		$groups = $this->User_group_model
			->table()
			->order_by('group_name');

		if (!empty($this->input->get('is_active'))) {
			$groups->where('is_active', $this->input->get('is_active'));
		}

		$groups = $groups->get()->result();


		echo json_encode([
			'data' => ['groups' => $groups],
			'errors' => null
		]);
		set_status_header(200);
	}

	public function check_group_name_duplicat()
	{
		# param 
		$raw_input = file_get_contents("php://input");
		$input = json_decode($raw_input);

		$not_duplicat = true;
		$query = $this->User_group_model->find(['group_name' => trim($input->group_name)]);
		if(!empty($query)){
			$not_duplicat = false;	
		}
        

		echo json_encode([
			'ok' => $not_duplicat
		]);
		set_status_header(200);
	}

	public function save()
	{
		// tangkap input 
		$input = $this->_get_input('add');

		// valid nama double
		$duplicat = $this->User_group_model->find(['group_name' => $input['header']['group_name']]);
		if (!empty($duplicat)) {
			echo json_encode([
				'errors' => ['Nama kelompok <b> '. $input['header']['group_name'] . ' </b> sudah ada.']
			]);
			set_status_header(403);
			exit;
		}

		// proses simpan
		$this->db->trans_start();
			$this->User_group_model->insert($input['header']);
			$this->User_group_access_model->insert($input['menus']);
		$this->db->trans_complete();

		// alihkan 
		echo json_encode([
			'data' => null,
			'errors' => null
		]);
		set_status_header(200);
	}

	private function _get_input($evt = 'add')
	{
		# param
		$raw_input = file_get_contents('php://input');
		$input = json_decode($raw_input);
		$insert = [];

		# get header
		if ($evt == 'add') {
			$insert['group_id'] = $this->User_group_model->primaryKeyInc();
		}
		$insert['group_name'] = trim($input->group_name);

		# get detail
		$given_access = $input->page_access;
		$insert_menus = [];
		if(!empty($given_access))
		{
			foreach ($given_access as $key => $value) {
				$insert_menus[] = array(
					"group_id" => $insert['group_id'],
					"menu_id" => $value,
					"can_access" => "Y"
				);
			}
		}

		return array(
			'header' => $insert,
			'menus' => $insert_menus
		);
	}
}

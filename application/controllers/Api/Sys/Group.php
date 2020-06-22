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
		$query = $this->User_group_model
			->table()
			->order_by('group_name');

		if (!empty($this->input->get('is_active'))) {
			$query->where('is_active', $this->input->get('is_active'));
		}

		if( !empty($this->input->get('group_id')) ){
			$query->where('group_id', $this->input->get('group_id'));
		}

		$groups = $query->get()->result();

		# ambil 
		$my_page_access = null;
        if (!empty($this->input->get('group_id'))) {
			$my_page_access = $this->User_group_access_model
									->table()
									->where('group_id', $this->input->get('group_id'))
									->get()
									->result();
        }

		echo json_encode([
			'data' => ['groups' => $groups, 'my_page_access' => $my_page_access],
			'errors' => null
		]);
		set_status_header(200);
	}

	public function check_group_name_duplicat()
	{
		# param 
		$raw_input = file_get_contents("php://input");
		$input = json_decode($raw_input);
		$page = $input->page;

		if($page == 'add')
		{
			$not_duplicat = true;
			$query = $this->User_group_model->find(['group_name' => trim($input->group_name)]);
			if(!empty($query)){
				$not_duplicat = false;	
			}
		}
		else if($page == 'edit')
		{
			$not_duplicat = true;

			$data_before = $this->User_group_model->find(['group_id' => trim($input->group_id)]);
			if( trim(strtolower($data_before->group_name)) != strtolower(trim($input->group_name)) )
			{
				$query = $this->User_group_model->find(['group_name' => trim($input->group_name)]);
				if(!empty($query)){
					$not_duplicat = false;	
				}
			}
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

	public function update()
	{
		// param 
		$raw_input = file_get_contents('php://input');
		$objInput = json_decode($raw_input);

		// data sebelumnya 
		if( empty($objInput->group_id) ){
			echo json_encode([
				'errors' => ['GROUP ID IS INVALID']
			]);
			set_status_header(403);
			exit;
		}
		$data_before = $this->User_group_model->find(['group_id' => $objInput->group_id]);

		// tangkap input 
		$input = $this->_get_input('edit');

		// valid nama double
		if( trim(strtolower($data_before->group_name)) != trim(strtolower($input['header']['group_name'])) )
		{
			$duplicat = $this->User_group_model->find(['group_name' => $input['header']['group_name']]);
			if (!empty($duplicat)) {
				echo json_encode([
					'errors' => ['Nama kelompok <b> '. $input['header']['group_name'] . ' </b> sudah ada.']
				]);
				set_status_header(403);
				exit;
			}
		}

		// proses simpan
		$this->db->trans_start();
			$this->User_group_model->update(['group_id' => $objInput->group_id], $input['header']);
			$this->db->delete($this->User_group_access_model->table, ['group_id' => $objInput->group_id]);
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
		}else{
			$insert['group_id'] = $input->group_id;
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

	public function delete()
	{
		# param 
		$raw_input = file_get_contents("php://input");
		$input = json_decode($raw_input);

		# valid 
		if(empty($input->id_trx)){
			echo json_encode([
				'errors' => ['ID Trx empty']
			]);
			set_status_header(403);
			exit;
		}

		# get val 
		$id = $input->id_trx;
		
		# proses 
		$this->db->trans_start();
			$this->db->delete(
				$this->User_group_model->table,
				['group_id' => $id]
			);

			$this->db->delete(
				$this->User_group_access_model->table,
				['group_id' => $id]
			);
		$this->db->trans_complete();

		# lempar
		echo json_encode([
			'data' => null,
			'errors' => null
		]);
		set_status_header(200);
		exit;
	}
}

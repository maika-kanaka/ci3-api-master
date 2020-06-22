<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends My_Controller 
{

	public function __construct()
	{		
		parent::__construct();

		$this->auth->canAccessApi('system_users');
	}

	public function data()
	{	
		$query = $this->User_model
						->view()
						->order_by('tu.user_fullname');

		if( !empty($this->input->get('user_id')) ){
			$query->where('user_id', $this->input->get('user_id'));
		}

		$query = $query->get()->result();

		$users = [];
		foreach ($query as $key => $value) {
			unset($value->user_password);
			$users[] = $value;
		}

		echo json_encode([
			'data' => ['users' => $users],
			'errors' => null
		]);
		set_status_header(200);
	}







	public function check_email_duplicat()
	{
		# param 
		$raw_input = file_get_contents("php://input");
		$input = json_decode($raw_input);

		$not_duplicat_email = true;

		if( $input->page == 'profile' )
		{
			if( trim($this->current_user->user_email) != trim($input->user_email) )
			{
				$query_email = $this->User_model->find(['user_email' => trim($input->user_email)]);
				if(!empty($query_email)){
					$not_duplicat_email = false;	
				}
			}
		}
		else if( $input->page == 'add' )
		{
			$query_email = $this->User_model->find(['user_email' => trim($input->user_email)]);
			if(!empty($query_email)){
				$not_duplicat_email = false;	
			}
		}
		else if( $input->page == 'edit' )
		{
			$user_before = $this->User_model->find(['user_id' => trim($input->user_id)]);

            if (trim($user_before->user_email) != trim($input->user_email)) {
                $query_email = $this->User_model->find(['user_email' => trim($input->user_email)]);
                if (!empty($query_email)) {
                    $not_duplicat_email = false;
                }
            }
		}

		echo json_encode([
			'ok' => $not_duplicat_email
		]);
		set_status_header(200);
	}

	public function check_username_duplicat()
	{
		# param 
		$raw_input = file_get_contents("php://input");
		$input = json_decode($raw_input);

		$not_duplicat_username = true;
		if( $input->page == 'profile' )
		{
			if ( trim($this->current_user->user_name) != trim($input->user_name) ) 
			{
				$query_username = $this->User_model->find(['user_name' => trim($input->user_name)]);
				if(!empty($query_username)){
					$not_duplicat_username = false;	
				}
			}
		}
		else if( $input->page == 'add' )
		{
			$query_username = $this->User_model->find(['user_name' => trim($input->user_name)]);
			if(!empty($query_username)){
				$not_duplicat_username = false;	
			}
		}
		else if( $input->page == 'edit' )
		{
			$user_before = $this->User_model->find(['user_id' => trim($input->user_id)]);

            if (trim($user_before->user_name) != trim($input->user_name)) {
                $query_username = $this->User_model->find(['user_name' => trim($input->user_name)]);
                if (!empty($query_username)) {
                    $not_duplicat_username = false;
                }
            }
		}		

		echo json_encode([
			'ok' => $not_duplicat_username
		]);
		set_status_header(200);
	}

	public function profile_update()
	{
		# param 
		$raw_input = file_get_contents("php://input");
		$input = json_decode($raw_input);
		$update = [];

		# catch input
		if( !empty($input->user_password) ){
			$update['user_password'] = password_hash($input->user_password, PASSWORD_DEFAULT);
		}

		if( !empty($input->user_email) ){
			$update['user_email'] = trim($input->user_email);

            if (trim($this->current_user->user_email) != trim($update['user_email'])) {
                $duplicat = $this->User_model->find(['user_email' => $update['user_email']]);
                if (!empty($duplicat)) {
                    echo json_encode([
                    'errors' => [
                        'Surel <b> '. $update['user_email'] . ' </b> sudah dipakai.'
                    ]
                ]);
                    set_status_header(403);
                    exit;
                }
            }
		}

		if( !empty($input->user_name) ){
			$update['user_name'] = trim($input->user_name);

            if (trim($this->current_user->user_name) != trim($update['user_name'])) {
                $duplicat = $this->User_model->find(['user_name' => $update['user_name']]);
                if (!empty($duplicat)) {
                    echo json_encode([
                    'errors' => [
                        'Nama pengguna <b> '. $update['user_name'] . ' </b> sudah dipakai.'
                    ]
                ]);
                    set_status_header(403);
                    exit;
                }
            }
		}

		if(!empty($input->user_fullname)){
			$update['user_fullname'] = trim($input->user_fullname);
		}

		# update 
		$this->User_model->update(['user_id' => $this->current_user->user_id], $update);

		# data ter-update
		$user = $this->User_model->find($this->current_user->user_id);
		unset( $user->user_password );

		# lempar
		echo json_encode([
			'data' => [
				'user' => $user
			],
			'errors' => null
		]);
		set_status_header(200);
		exit;
	}

	public function save()
	{
		# param 
		$raw_input = file_get_contents("php://input");
		$input = json_decode($raw_input);
		
		# tangkap input 
		$insert = $this->_get_input('add');

		# valid: email
		$duplicat = $this->User_model->find(['user_email' => $insert['user_email']]);
        if (!empty($duplicat)) {
            echo json_encode([
                'errors' => ['Surel <b> '. $insert['user_email'] . ' </b> sudah dipakai.']
            ]);
            set_status_header(403);
            exit;
        }

		# valid: username
		$duplicat = $this->User_model->find(['user_name' => $insert['user_name']]);
		if (!empty($duplicat)) {
			echo json_encode([
				'errors' => ['Nama pengguna <b> '. $insert['user_name'] . ' </b> sudah dipakai.']
			]);
			set_status_header(403);
			exit;
		}

		# proses simpan
		$this->User_model->insert($insert);

		# lempar
		echo json_encode([
			'data' => null,
			'errors' => null
		]);
		set_status_header(200);
		exit;
	}

	public function update()
	{
		# param 
		$raw_input = file_get_contents("php://input");
		$input = json_decode($raw_input);

		# vali d
		if( empty($input->user_id) )
		{
			show_404();
			return;
		}

		# data sebelum
		$user_id = $input->user_id;
		$data_before = $this->User_model->find(['user_id' => $user_id]);
		
		# tangkap input 
		$insert = $this->_get_input('edit');

		# valid: email
		if( trim($data_before->user_email) != trim($insert['user_email']) )
		{
			$duplicat = $this->User_model->find(['user_email' => $insert['user_email']]);
			if (!empty($duplicat)) {
				echo json_encode([
					'errors' => ['Surel <b> '. $insert['user_email'] . ' </b> sudah dipakai.']
				]);
				set_status_header(403);
				exit;
			}
		}

		# valid: username
		if (trim($data_before->user_name) != trim($insert['user_name'])) 
		{
            $duplicat = $this->User_model->find(['user_name' => $insert['user_name']]);
            if (!empty($duplicat)) {
                echo json_encode([
                    'errors' => ['Nama pengguna <b> '. $insert['user_name'] . ' </b> sudah dipakai.']
                ]);
                set_status_header(403);
                exit;
            }
        }

		# proses simpan
		$this->User_model->update(['user_id' => $user_id], $insert);

		# lempar
		echo json_encode([
			'data' => null,
			'errors' => null
		]);
		set_status_header(200);
		exit;
	}

	private function _get_input($event = 'add', $param = [])
	{
		# param 
		$raw_input = file_get_contents("php://input");
		$input = json_decode($raw_input);

		if( $event == 'add' ) {
			$insert['created_at'] = date('Y-m-d H:i:s');
			$insert['created_by'] = $this->current_user->user_id;
			$insert['user_password'] = password_hash($input->user_password, PASSWORD_DEFAULT);
		}else if($event == 'edit'){
			if(!empty($input->user_password)){
				$insert['user_password'] = password_hash($input->user_password, PASSWORD_DEFAULT);
			}
		}

		$insert['group_id'] = (int) $input->group_id;
		$insert['user_fullname'] = trim(strip_tags(substr($input->user_fullname ,0, 160)));
		$insert['user_name'] = trim(strip_tags(substr($input->user_name, 0, 50)));
		$insert['user_email'] = trim(strip_tags(substr($input->user_email, 0, 220)));
		$insert['is_block'] = $input->is_block == 'N' ? 'N' : 'Y';
		
		return $insert;
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
		$this->db->delete(
			$this->User_model->table,
			['user_id' => $id]
		);

		# lempar
		echo json_encode([
			'data' => null,
			'errors' => null
		]);
		set_status_header(200);
		exit;
	}
}

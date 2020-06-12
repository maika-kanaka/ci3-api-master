<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends My_Controller 
{

	public function __construct()
	{		
		parent::__construct();
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
		if( trim($this->current_user->user_email) != trim($input->user_email) )
		{
			$query_email = $this->User_model->find(['user_email' => trim($input->user_email)]);
			if(!empty($query_email)){
				$not_duplicat_email = false;	
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
		if ( trim($this->current_user->user_name) != trim($input->user_name) ) 
		{
			$query_username = $this->User_model->find(['user_name' => trim($input->user_name)]);
			if(!empty($query_username)){
				$not_duplicat_username = false;	
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
}

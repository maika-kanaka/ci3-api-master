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
		$users = $this->User_model
						->view()
						->order_by('tu.user_fullname')
						->get()
						->result();

		echo json_encode([
			'data' => ['users' => $users],
			'errors' => null
		]);
		set_status_header(200);
	}
}

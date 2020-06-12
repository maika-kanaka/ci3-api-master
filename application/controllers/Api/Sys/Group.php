<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Group extends My_Controller 
{

	public function __construct()
	{		
		parent::__construct();
	}

	public function data()
	{	
		$groups = $this->User_group_model
						->table()
						->order_by('group_name');

		if( !empty($this->input->get('is_active')) ){
			$groups->where('is_active', $this->input->get('is_active'));
		}

		$groups = $groups->get()->result();
						

		echo json_encode([
			'data' => ['groups' => $groups],
			'errors' => null
		]);
		set_status_header(200);
	}

}

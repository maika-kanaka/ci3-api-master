<?php 

use Eloquent\Eloquent;

class User_model extends Eloquent 
{

	public $table = 'system_users';
	public $primary_key = 'user_id';

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Sys/User_group_model');
	}

	public function view()
	{
		return $this->table('tu')
					->join($this->User_group_model->table . " AS tug", "tug.group_id = tu.group_id");
	}

}

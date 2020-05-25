<?php 

use Eloquent\Eloquent;

class User_group_model extends Eloquent 
{

	public $table = 'system_user_group';
	public $primary_key = 'group_id';

	public function __construct()
	{
		parent::__construct();
	}

}

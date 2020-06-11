<?php 

use Eloquent\Eloquent;

class User_group_access_model extends Eloquent 
{

	public $table = 'system_user_group_access';

	public function __construct()
	{
		parent::__construct();
	}

	public function initSession($group_id)
	{
		$query = $this->table()
					  ->where('group_id', $group_id)
					  ->where('can_access', 'Y')
					  ->get()->result();

		$roles = [];
		foreach ($query as $key => $value) {
			$roles[] = $value->menu_id;
		}

		return $roles;
	}

}

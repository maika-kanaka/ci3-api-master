<?php 

use Eloquent\Eloquent;

class Menu_model extends Eloquent 
{

	public $table = 'system_menus';
	public $primary_key = 'menu_id';

	public function __construct()
	{
		parent::__construct();
	}

}

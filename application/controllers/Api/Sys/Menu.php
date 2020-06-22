<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends My_Controller 
{

	public function __construct()
	{		
		parent::__construct();

		$this->load->model('Sys/Menu_model');

		$this->auth->canAccessApi('system_user_group');
	}

	public function data_for_page_access()
	{
		$query_top = $this->Menu_model
						->table()
						->where('is_active', 'Y')
						->where('menu_id_top IS NULL', NULL, FALSE)
						->order_by('menu_order')
						->get()
						->result();

		$menus = [];
		foreach ($query_top as $kt => $vt) 
		{
			$menus[] = $vt;

			$query_child = $this->Menu_model->table()
										->where('is_active', 'Y')
										->where('menu_id_top', $vt->menu_id)
										->order_by('menu_order')
										->get()
										->result();

			foreach ($query_child as $kc => $vc) {
				$menus[] = $vc;
			}
		}

		echo json_encode([
			'data' => ['menus' => $menus],
			'errors' => null
		]);
		set_status_header(200);
	}

}

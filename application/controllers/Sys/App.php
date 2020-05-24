<?php defined('BASEPATH') or exit('No direct script access allowed');

class App extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
	}

	public function migrate()
	{
		$this->load->library('migration');

		if ($this->migration->current() === FALSE) {
			show_error($this->migration->error_string());
		}
	}
}

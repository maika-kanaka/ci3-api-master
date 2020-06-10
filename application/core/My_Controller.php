<?php 

use \Firebase\JWT\JWT;

class MY_Controller extends CI_Controller 
{
	public $current_user;

	public function __construct()
	{
		if(in_array(ENVIRONMENT, ['development', 'test']))
		{
			header('Access-Control-Allow-Origin: *');
			header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
			$method = $_SERVER['REQUEST_METHOD'];
			if($method == "OPTIONS") {
				die('options method');
			}
		}
		
		parent::__construct();

		// auth for api with jwt
		if( strpos(uri_string(), "api") !== false )
		{
			// except url 
			if( in_array(uri_string(), [
				"api/login"
			]) ){
				return true;
			}

			$raw_input = file_get_contents("php://input");
			$input     = json_decode($raw_input);
			if(!empty($input->jwtAuth)){
				$token = $input->jwtAuth;
			}else if(!empty($_GET['jwtAuth'])){
				$token = $_GET['jwtAuth'];
			}else if(!empty($_POST['jwtAuth'])){
				$token = $_POST['jwtAuth'];
			}else{
				$token = "";
			}
			$jwt_key   = $this->config->item('jwt_key');

			if( empty($token) ){
				set_status_header(403);
				echo json_encode([
					'errors' => [
						'jwt is empty '
					]
				]);
				exit;
			}

			try {
				$decoded = JWT::decode($token, $jwt_key, array('HS256'));

				$user = $this->User_model->find(['user_id' => $decoded->user_id]);
				$this->current_user = $user;
			} 
			catch (Exception $e) 
			{
				set_status_header(403);
				echo json_encode([
					'errors' => [
						'jwt is invalid ',
						'e: '. $e
					]
				]);
				exit;
			}
		}
	}
}

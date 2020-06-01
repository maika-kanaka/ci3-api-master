<?php defined('BASEPATH') or exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Login extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
    {
        $raw_input = file_get_contents("php://input");
        $input     = json_decode($raw_input);
        
        # param
        $email    = trim( $input->email );
        $password = trim( $input->password );

        # data 
        $user = $this->User_model->find([
            'user_email' => $email
        ]);

        # valid 
        if( empty($user) ){
            echo json_encode([
                'data' => null,
                'errors' => [
                    'Akun dengan pengguna tsb tidak ditemukan'
                ]
            ]);
            set_status_header(200);
            exit;
        }

        # valid
        if( password_verify($password, $user->user_password) === false )
        {
            echo json_encode([
                'data' => null,
                'errors' => [
                    'Kata sandi salah'
                ]
            ]);
            set_status_header(200);
            exit;
        }

        # valid
        if( $user->is_block == "Y" ){
            echo json_encode([
                'data' => null,
                'errors' => [
                    'Akun pengguna anda di blokir'
                ]
            ]);
            set_status_header(200);
            exit;
        }

        $key = $this->config->item("jwt_key");
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            "nbf" => 1357000000
        );

        $jwt = JWT::encode($payload, $key);

        # lempar 
        echo json_encode([
            'data' => [
                'user' => $user,
                'jwt' => $jwt
            ],
            'errors' => null
        ]);
        set_status_header(200);
        exit;
    }
}

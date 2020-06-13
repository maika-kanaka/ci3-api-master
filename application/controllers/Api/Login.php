<?php defined('BASEPATH') or exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Login extends MY_Controller
{

	public function __construct()
	{
        parent::__construct();
        $this->load->model('Sys/User_group_access_model');
	}

	public function index()
    {
        $raw_input = file_get_contents("php://input");
        $input     = json_decode($raw_input);
        
        # param
        $email_or_username = trim( $input->email_or_username );
        $password = trim( $input->password );

        # data 
		$user = $this->User_model
						->table()
						->where('user_email', $email_or_username)
						->or_where('user_name', $email_or_username)
						->get()->row();

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

        # data again 
        $pageAccess = $this->User_group_access_model->initSession($user->group_id);

        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone("Asia/Jakarta"));
        $key = $this->config->item("jwt_key");
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => $now->getTimestamp(),
            "nbf" => $now->getTimestamp(),
            "user_id" => $user->user_id
        );

        $jwt = JWT::encode($payload, $key);

        unset($user->user_password);

        # lempar 
        echo json_encode([
            'data' => [
                'user' => $user,
                'pageAccess' => $pageAccess,
                'jwt' => $jwt
            ],
            'errors' => null
        ]);
        set_status_header(200);
        exit;
    }
}

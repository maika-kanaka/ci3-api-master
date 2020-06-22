<?php 

class Auth 
{

    private $ci;

    public function __construct()
    {
      $this->ci =& get_instance();
    }

    public function canAccessApi($menu_id)
    {
        $can_access = $this->ci->User_group_access_model
                ->table()
                ->where('menu_id', $menu_id)
                ->where('group_id', $this->ci->current_user->group_id)
                ->where('can_access', 'Y')
                ->get()
                ->row();

        if( empty($can_access) )
        {
            set_status_header(403);
            echo json_encode([
                'errors' => [
                    'you do not have permission to access this page'
                ]
            ]);
            exit;
        }
    }

}
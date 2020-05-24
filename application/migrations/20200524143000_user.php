<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_User extends CI_Migration 
{

    public function up()
    {
        ## TABLE: system_users
        $this->dbforge->add_field(array(
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => True,
                'auto_increment' => True
			),
			'group_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
			'user_fullname' => array(
                'type' => 'VARCHAR',
                'constraint' => '160',
                'null' => FALSE
			),
			'user_email' => array(
                'type' => 'VARCHAR',
                'constraint' => '220',
                'null' => FALSE
            ),
            'user_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => FALSE
            ),
            'user_password' => array(
                'type' => 'VARCHAR',
                'constraint' => '220',
                'null' => FALSE
            ),
            'is_block' => array(
                'type' => 'CHAR',
                'constraint' => 1,
                'default' => 'N'
			),
			


			'updated' => array(
				'type' => 'INT',
				'default' => 0
			),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => True
            ),
            'created_by' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            )
        ));

        $this->dbforge->add_key('user_id', True);
        $this->dbforge->create_table('system_users');

        $this->db->insert('system_users', [
			'user_fullname' => 'Maika Kanaka',
			'user_email' => 'maika-kanaka@gmail.com',
            'user_name' => 'maika-kanaka',
            'user_password' => password_hash('123456', PASSWORD_DEFAULT),
            'group_id' => 1 // root
        ]);

        ## TABLE: system_menus
        $this->dbforge->add_field(array(
            'menu_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 120
            ),
            'menu_id_top' => array(
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => True
            ),
            'menu_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '220',
                'null' => FALSE
            ),
            'menu_order' => array(
                'type' => 'INT',
                'null' => FALSE
            ),
            'is_active' => array(
                'type' => 'CHAR',
                'constraint' => 1,
                'default' => 'Y'
            )
        ));

        $this->dbforge->add_key('menu_id', True);
        $this->dbforge->create_table('system_menus');

        $this->db->insert_batch('system_menus', [
            [
                'menu_id' => 'system',
                'menu_id_top' => NULL,
                'menu_name' => 'System',
                'menu_order' => 9999,
                'is_active' => 'Y'
            ],

            [
                'menu_id' => 'system_user_group',
                'menu_id_top' => 'system',
                'menu_name' => 'Page Access',
                'menu_order' => 1,
                'is_active' => 'Y'
            ],

            [
                'menu_id' => 'system_users',
                'menu_id_top' => 'system',
                'menu_name' => 'Users',
                'menu_order' => 2,
                'is_active' => 'Y'
            ]
        ]);

        ## TABLE: system_user_group
        $this->dbforge->add_field(array(
            'group_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => True,
                'auto_increment' => True
            ),
            'group_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 120
            ),
            'is_active' => array(
                'type' => 'CHAR',
                'constraint' => 1,
                'default' => 'Y'
            ),
        ));

        $this->dbforge->add_key('group_id', True);
        $this->dbforge->create_table('system_user_group');

        $this->db->insert('system_user_group', [
            'group_id' => 1,
            'group_name' => 'Root / superadmin',
            'is_active' => 'Y'
        ]);

        ## TABLE: system_user_group_access
        $this->dbforge->add_field(array(
            'group_id' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'menu_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 120
            ),
            'can_access' => array(
                'type' => 'CHAR',
                'constraint' => 1,
                'default' => 'Y'
            ),
        ));

        $this->dbforge->add_key('group_id', True);
        $this->dbforge->add_key('menu_id', True);
        $this->dbforge->create_table('system_user_group_access');

        $this->db->query("
            INSERT INTO system_user_group_access
            SELECT 1, menu_id, 'Y'
            FROM system_menus 
        ");

        ## TABLE: system_user_log
        $this->dbforge->add_field(array(
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            ),
            'log_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => True,
                'auto_increment' => True
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'menu_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => True
            ),
            'id_trx' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => True
            ),
            'log_event' => array(
                'type' => 'VARCHAR',
                'constraint' => 70,
                'null' => True
            ),
            'log_message' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => True
            )
        ));

        $this->dbforge->add_key('log_id', True);
        $this->dbforge->create_table('system_user_log');
    }

    public function down()
    {
        $this->dbforge->drop_table('system_users');
        $this->dbforge->drop_table('system_menus');
        $this->dbforge->drop_table('system_user_group');
        $this->dbforge->drop_table('system_user_group_access');
        $this->dbforge->drop_table('system_user_log');
    }
    
}

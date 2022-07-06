<?php

//is checked


namespace HWP_Ticket\core\includes;




use WP_REST_Request;
use WP_REST_Server;


class Users
{

    protected static $_instance = null;

    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public $namespace;
    public $version;
    public $endpoint;
    public $api;
    private $params;
    private $userID;


    public function __construct()
    {
        add_action('rest_api_init',[$this, 'Routes' ]);

        $this->namespace = 'hamfy';
        $this->version = 'v1.1';
        $this->endpoint = 'user_p';

        $this->api = $this->namespace . '/' . $this->version . '/' . $this->endpoint;
    }


    public function Routes()
    {
        register_rest_route($this->namespace, $this->version . '/' . $this->endpoint, array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'createPermission'),
            'permission_callback' => array($this, 'authentication')
        ));
    }


    public function authentication(WP_REST_Request $request)
    {
        $this->params = (object)$request->get_params();
        $this->userID = Functions::decryptID(
            $request->get_headers()['usertoken'][0]);

        return $this->userID;
    }


    public function createPermission()
    {
        if (is_numeric($this->userID) && $this->userID > 0) {
            wp_send_json(['Result' => Course::get_instance()::getFormListCourse( $this->userID ) ], 200);
        } else {
            wp_send_json(['Result' => [] ], 404);
        }
    }


    public function userValidator( $userID )
    {
        $user = get_user_by('id', $userID );
        if ( !$user ){
            return true;
        }
        return true;
    }


    public static function searchUserAdmin()
    {
        global $wpdb;
        $who = $_POST['keyword'] ?? '';
        $ex_users = $_POST['ex_users'] ?? [];
        $extra_query = '';

        if ( !empty( $ex_users ) ){
            $exclude_users = implode("','", $ex_users);
            $extra_query = "AND id NOT IN ('$exclude_users')";
        }
        $users = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE id = %s  ".$extra_query." limit 100  ;" , $who )
        );

        if ( !empty($users ) ) {
            $result = [];
            foreach ( $users as $user ) {
                $result  [] = [
                    'id'    => $user->ID,
                    'name'  => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->user_email,
                    'phone' => Users::getMobile( $user->ID )
                ];
            }
            wp_send_json( $result, 200 );
        }
        Functions::returnResult( 202 );
    }


    public static function searchUserPublic()
    {
        global $wpdb;
        $who = $_POST['keyword'] ?? '';
        $usersID = $wpdb->get_results(
            $wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE ID = %s OR user_login LIKE %s OR user_nicename LIKE %s OR user_email LIKE %s OR display_name LIKE %s limit 100  ;"
                , $who, '%' . $who . '%', '%' . $who . '%', '%' . $who . '%', '%' . $who . '%'));

        if ( empty( $usersID ) ) {
            $usersID = $wpdb->get_results(
                $wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE ( meta_key ='first_name' AND meta_value LIKE %s ) OR (meta_key ='last_name' AND meta_value LIKE %s ) OR (meta_key = 'force_verified_mobile' AND meta_value LIKE %s ) limit 100  ;"
                    , '%' . $who . '%', '%' . $who . '%' , '%' . $who . '%'  ));
            $usersID = array_column( $usersID, 'user_id');
        }else{
            $usersID = array_column( $usersID, 'ID');
        }
        $usersID = array_unique( $usersID );

        if (!empty($usersID ) ) {
            $result = [];
            foreach ( $usersID as $u ) {
                $user = Functions::getUser( (int) $u );
                $result  [] = [
                    'id' => $user->ID,
                    'title' => $user->first_name . ' ' . $user->last_name,
                    'slug' => $user->user_nicename
                ];
            }
            wp_send_json($result, 200 );
        }
        Functions::returnResult( 202 );
    }


    public static function addTextTemplate( $sort, $userID, $title, $content )
    {
        $template_array = self::getTextTemplate($userID);
        end($template_array);
        $last_id = key($template_array) + 1; // array_key_last( $this->getTextTemplate( $userID ) ) + 1;

        $templates = self::getTextTemplate($userID);
        if ($sort === false) {
            if (empty($templates)) $templates = [];
            $templates[$last_id] = [
                'title' => $title,
                'content' => $content
            ];
        } else {
            $templates = $sort;
        }
        update_user_meta($userID, 'hamyar_user_text_template', serialize($templates));
        return true;
    }


    public static function getTextTemplate( $userID )
    {
        return maybe_unserialize(get_user_meta($userID, 'hamyar_user_text_template', true));
    }


    public static function getSupportUsers()
    {
        global $wpdb;
        $users = [];
        $IDs = $wpdb->get_col("SELECT user_id  FROM {$wpdb->usermeta}  WHERE meta_key='_hamfy_user_permissions'; ");
        if ( !empty( $IDs ) ) {
            foreach ( $IDs as $id ){
                $user    = Functions::getUser( (int) $id );
                $users[] = [
                    'id'   => $user->ID  ,
                    'name' => $user->first_name .' '.$user->last_name
                ];
            }
        }
        return $users;
    }

    public static function getRemainSupport( $userID, $object )
    {
        if ( !empty( $userID ) && is_numeric($userID ) ) {
            $userMeta = get_user_meta( $userID, '_product_support', true );
            if ( !empty( $userMeta ) ) {
                $productsList = maybe_unserialize( $userMeta );
                if ( is_array( $productsList ) ) {
                    if (isset($productsList[$object])) {
                        if ($productsList[$object] < time()) {
                            return "پشتیبانی تمام شده";
                        } else {
                            return '<b dir="rtl"><time> ' . date_i18n("d F Y", $productsList[$object]) . '</time></b>';
                        }
                    }
                }
            }
        }
        return '';
    }

    public static function getUserTrustMeta( $userID )
    {
        if ( is_numeric( $userID ) ){
            $trust = get_user_meta( $userID,'_hamfy_user_trust_status' , true );
            if ( !empty( $trust ) || self::isAdmin( $userID )) {
                return true;
            }
        }
        return false;
    }

    public static function getUserSupportMeta( $userID )
    {
        if ( is_numeric( $userID ) ){
            return get_user_meta( $userID,'_product_support' , true );
        }
        return false;
    }

    public static function getUserPermissionsMeta( $userID )
    {
        if ( is_numeric( $userID ) ){
            return get_user_meta( $userID,'_hamfy_user_permissions' , true );
        }
        return false;
    }

    public static function isAdmin( $userID )
    {
        if ( is_numeric( $userID ) ){
            return user_can( $userID , 'administrator' );
        }
        return false;
    }

    public static function getMobile( $userID )
    {
        if ( is_numeric( $userID) ){
            return get_user_meta( $userID , 'force_verified_mobile' ,true );
        }
        return false;
    }


}
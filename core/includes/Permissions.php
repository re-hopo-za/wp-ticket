<?php


namespace HWP_Ticket\core\includes;



class Permissions
{



    protected static  $_instance  = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }



    public function __construct()
    {
        add_action('woocommerce_order_status_changed'  , [$this , 'addPermissionAuto'], 10 , 4 );
    }


    public static function removeAllPermission()
    {
        if ( isset( $_POST['user_id'] )){
            delete_user_meta( $_POST['user_id'] , '_hamfy_user_permissions' );
            wp_send_json_error('User Removed',200  );
        }else{
            wp_send_json_error('User Id Error!!!',403  );
        }
    }


    public static function updateUserPermission()
    {
        if ( !Users::isAdmin( get_current_user_id() ) || !isset( $_POST['user_id']) || !is_numeric( $_POST['user_id'] ) ) {
            wp_send_json( ['result' => 'just admin assign permission'] , 403 );
        }

        $userID       = sanitize_text_field( $_POST['user_id'] );
        $u_trust      = sanitize_text_field( $_POST['u_trust'] );
        $permissions  = $_POST['permissions'];
        if ( $u_trust == 'true' ){
            add_user_meta( $userID , '_hamfy_user_trust_status'  ,time() );
        }else{
            delete_user_meta( $userID , '_hamfy_user_trust_status' );
        }
        update_user_meta( $userID , '_hamfy_user_permissions' , $permissions );
        wp_send_json( ['result' => 'updated' , 200 ] );
    }



    public function addPermissionAuto( $orderID, $from_status, $to_status, $order  ){
        if( $to_status == 'completed' ) {
            $order       = wc_get_order( $orderID );
            $items       = $order->get_items();
            $user_id     = $order->get_customer_id();
            $products    = maybe_unserialize( get_user_meta( $user_id , '_product_support' ,true )  );

            $coupon_codes=$order->get_coupon_codes();
            $ignore_code=[170449=>'wbdupgradeths']; //ignore support time for  0 ta 100 tarahi site old for spacial coupon code

            if (empty($products))
                $products=[];

            foreach ( $items as $p_item ){
                $product_id = (int) $p_item->get_product_id();
                if (Functions::isFill( $products ,$product_id)) {
                    continue;
                }
                if (array_key_exists($product_id,$ignore_code) && in_array($ignore_code[$product_id],$coupon_codes)){
                    $lily=\Lily_Course_Management::get_instance();
                    $lily->lily_force_quiz_unlock_status(
                        array(
                             'user_id' => $user_id,
                             'product_id' => $product_id,
                             'force_quiz_unlock'=>true
                        ));
                    continue;
                }

                $p_period   = (int) get_post_meta(  $product_id , '_support_duration' , true );
                $p_period   = '+'.$p_period.' days';
                $products[ $product_id ] =  strtotime( $p_period );
            }

            update_user_meta(  $user_id , '_product_support'  , $products );

        }

    }



    public static function whoIs( $userID )
    {
        $user_trust    = Users::getUserTrustMeta( $userID );
        $course_bought = Users::getUserSupportMeta( $userID );
        $user_team     = Users::getUserPermissionsMeta( $userID );
        $user_admin    = Users::isAdmin( $userID );

        if ( $userID == 0 ){
            return 'guest';
        }
        if ( $user_admin ){
            $who = 'admin';
        }elseif ( !empty( $user_team ) && !empty( $user_trust ) ){
            $who = 'support';
        }elseif ( !empty( $user_team ) && empty( $user_trust ) ){
            $who = 'master';
        }elseif ( !empty( $course_bought ) ){
            $who = 'student';
        }else{
            $who = 'user';
        }
        return $who;
    }

    public static function isSupporter()
    {
        global $GLOBAL_TICKET_WHO_IS;
        if ( ( $GLOBAL_TICKET_WHO_IS != 'student' && $GLOBAL_TICKET_WHO_IS != 'user') ){
            return true;
        }
        return false;
    }

    public static function isSupporterDashboard( $userID )
    {
        $whois = self::whoIs( $userID );
        if ( $whois != 'student' && $whois != 'user' ){
            return true;
        }
        return false;
    }

    public static function checkTicketPermission( $token ,$userID ,$course ,$destination ,$reply ,$parent = '' )
    {
        self::checkRecaptcha( $token );
        Users::get_instance()->userValidator( $userID );
        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_IS_SUP;
        global $GLOBAL_TICKET_PERMISSION;


        if ( 'admin' == $GLOBAL_TICKET_WHO_IS ){
            return true;

        }elseif ( $GLOBAL_TICKET_IS_SUP ){
            if ( !empty( $course ) && in_array( $course ,$GLOBAL_TICKET_PERMISSION ) ){
                if ( isset( $GLOBAL_TICKET_PERMISSION[$course][$destination] ) ){
                    return true;
                }
            }

        }elseif( $reply ){
            Course::get_instance()::checkCourseReply( $userID ,$parent );
            Destination::get_instance()::checkDestinationReply( $userID ,$parent ,$destination );

        }else{
            Course::get_instance()::checkCourseNew( $userID ,$course );
            Destination::get_instance()::checkDestinationNew( $userID  ,$course  ,$destination );
        }
        return true;
    }



    public static function checkAccessSingleTicket( $userID ,$ticketObject ,$who_is ,$permissions )
    {
        if ( (int) $ticketObject->creator == $userID || (int) $ticketObject->assign_to == $userID || 'admin' == $who_is ){
            return true;
        }
        if ( self::isAccessToSpecificTicket( $ticketObject->main_object ,$ticketObject->destination ,$permissions ) ){
            return true;
        }
        return false;
    }


    public static function isAccessToSpecificTicket( $courseID ,$destination ,$permissions )
    {
        if ( !empty( $permissions ) ){
            foreach ( $permissions as $key => $val ){
                if ( $courseID == $key && isset( $val[$destination] ) && $val[$destination] == 'true' ){
                    return true;
                }
            }
        }
        return false;
    }


    public static function getAssignedPermissionList()
    {
        global $wpdb;
        $table      = $wpdb->usermeta;
        $users = [];

        $permission = $wpdb->get_results(  "SELECT * FROM $table WHERE meta_key='_hamfy_user_permissions';");
        if ( !empty( $permission ) ){
            $i = 0;
            foreach ( $permission as $user ){
                $user_data = Functions::getUser( $user->user_id );
                $users [$i] = [
                    'user_id'     => $user->user_id ,
                    'full_name'   => $user_data->first_name.' '.$user_data->last_name ,
                    'email'       => $user_data->user_email ,
                    'role'        => Permissions::whoIs( $user->user_id ) ,
                    'phone'       => Users::getMobile( $user->user_id ) ,
                    'permissions' => maybe_unserialize( $user->meta_value ) ,
                    'trust'       => Users::getUserTrustMeta( $user->user_id )  ,
                    'without_course' => get_user_meta( $user->user_id ,'_hamfy_user_without_course_permission' ,true )
                ];
                $i++;
            }
        }
        return $users;

    }


    public static function getMasters()
    {
        $master = [];
        $all = self::getAssignedPermissionList();
        foreach ( $all as $item ){
            if ( !empty( $item['permissions']) &&
                 !empty( $item['permissions']['courses'] ) &&
                  empty( $item['permissions']['destinations'] )  ){
                $master[$item['user_id'] ] = $item['permissions']['courses'];
            }
        }
        return $master;
    }



    public static function getMastersByCourseID( $courseID )
    {
        global $wpdb;
        $table      = $wpdb->usermeta;
        $users = [];

        $permission = $wpdb->get_results("SELECT * FROM $table WHERE meta_key='_hamfy_user_permissions' AND meta_value LIKE '%$courseID%'  ;");

        if ( !empty( $permission ) ){
            foreach ( $permission as $user ){
                $trust =  get_user_meta( $user->user_id ,'_hamfy_user_trust_status' ,true );
                if ( !empty( $trust ) )  continue;

                $user_data = get_user_by('id' , $user->user_id );
                $users [$user->user_id] = [
                    'user_id'     => $user->user_id ,
                    'full_name'   => $user_data->first_name.' '.$user_data->last_name ,
                    'phone'       => Users::getMobile( $user->user_id ) ,
                ];
            }
        }
        return $users;

    }


    public static function loginChecker()
    {
        if (!is_user_logged_in()){
            $redirect= '?redirect_to='.urlencode( home_url( $_SERVER['REQUEST_URI'] ) );
            nocache_headers();
            wp_redirect(site_url().'/login/'.$redirect,302,'redirect_by_ticket' );
            exit();
        }
        return true;
    }


    public static function getPermissionsList( $userID )
    {
        $perms = maybe_unserialize( get_user_meta( $userID , '_hamfy_user_permissions' , true ) );
        $empty_course = false;
        if ( !empty( $perms ) && is_array( $perms )){
            $webmasteran_group = array_flip( get_option('_is_webmasteran_course' ) );
            $instagram_group   = array_flip( get_option('_is_instagram_group' ) );
            if ( array_key_exists('empty' ,$perms ) ){
                $empty_course = $perms['empty'];
            }

            if ( array_key_exists('webmasteran', $perms ) ){
                foreach ( $webmasteran_group as $webmaster ){
                    if( !isset( $perms[$webmaster] ) ){
                        $perms[$webmaster] = $perms['webmasteran'];
                    }
                }
            }
            if ( array_key_exists('instagram', $perms ) ){
                foreach ( $instagram_group as $instagram ){
                    if( !isset( $perms[$instagram] ) ){
                        $perms[$instagram] = $perms['instagram'];
                    }
                }
            }
            if ( array_key_exists('like_admin' ,$perms ) ){
                $perms = Functions::getAllProducts( $perms['like_admin'] );
                if ( !empty( $empty_course ) ){
                    $perms['empty'] = $empty_course;
                }
            }
            return $perms;
        }
        return [];
    }

    public static function getAdminPermissionsList( $userID )
    {
        $perm = maybe_unserialize( get_user_meta( $userID , '_hamfy_user_permissions' , true ) );
        if ( !empty( $perm ) ){
            if ( is_array( $perm ) ){
                return $perm;
            }
        }
        return [];
    }


    public static function getSpecificCoursesListByDestination( $permissions ,$destination )
    {
        $courses = [];
        if ( !empty( $permissions ) && is_array( $permissions ) ){
            $destinations = Destination::getDestinationsList();
            if ( array_key_exists( $destination , $destinations ) ){
                foreach ( $permissions as $key => $des_items ){
                    foreach ( $des_items as $des_key => $des_val ){
                        if ( $des_val == 'true' && $des_key == $destination ){
                            $courses[] = (int) $key;
                        }
                    }
                }
            }
        }
        return $courses;
    }


    public static function getUserCoursesList( $permissions ,$course )
    {
        if ( !empty( $permissions ) && is_array( $permissions ) ){
            if ( array_key_exists( $course ,$permissions ) ){
                return true;
            }
        }
        return false;
    }

    public static function getSpecificCourseDestinations( $permissions ,$course )
    {
        $destinations = [];
        if ( !empty( $permissions ) && is_array( $permissions ) ){
            foreach ( $permissions as $key => $values ){
                if ( $key == $course ){
                    foreach ( $values as $val_key => $val_val ){
                        if ( $val_val == 'true' ){
                            $destinations[] = $val_key;
                        }
                    }
                }
            }
        }
        return $destinations;
    }


    public static function ticketOwner( $userID ,$creatorID ){
        return $userID == (int) $creatorID ? 'sent' : 'received';
    }


    public static function userDefaultSort( $who_Is )
    {
        switch ( $who_Is ){
            case 'admin' :
                 return 'created_date|DESC';
            case 'master' :
                return 'order_num|DESC';
            case 'support' :
            case 'student' :
            case 'user'    :
            default        :
                return 'updated_date|DESC';
        }
    }

    public static function validSortList()
    {
        return ['updated_date|DESC' ,'updated_date|ASC' , 'created_date|DESC' ,'created_date|ASC' ,'order_num|DESC' ,'order_num|ASC' ];
    }


    public static function nonceCheckerBackend()
    {
        if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'],'hamfy-admin-nonce-sec') ){
            wp_send_json_error('invalid nonce',403  );
        }
        return true;
    }


    public static function nonceCheckerFrontend()
    {
        if ( !isset( $_POST['nonce'] ) || empty( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'],'hamfy_public_security')  ){
            wp_send_json_error('invalid nonce',403  );
        }
        return true;
    }


    public static function getAccessDestinationsList( $permission )
    {
        $list = [];
        if ( !empty( $permission ) ){
            foreach ( $permission as $course => $dest_items ){
                if ( !empty( $dest_items ) ){
                    foreach ( $dest_items as $key => $val ){
                        if ( $val == 'true' && $val != 'false' ){
                            $list[$key] = $key;
                        }
                    }
                }
            }
        }
        return $list;
    }


    public static function checkRecaptcha( $token )
    {
        return true;
        if (!function_exists('hamyar_feature_is_recaptcha_enabled') || !hamyar_feature_is_recaptcha_enabled()) return true;
        $message = hamyar_feature_recaptcha_validate( $token );
        if ( $message !== true ){
            wp_send_json( $message );
        }
        return true;
    }


    public static function checkGroupCoursesKeys( $courseID ,$destination )
    {
        global $GLOBAL_TICKET_PERMISSION;
        if ( !empty( $GLOBAL_TICKET_PERMISSION ) && is_array( $GLOBAL_TICKET_PERMISSION ) ){
            if ( isset( $GLOBAL_TICKET_PERMISSION[$courseID] ) && $GLOBAL_TICKET_PERMISSION[$courseID][$destination] == 'true' ){
                return true;
            }
        }
        return false;
    }

    public static function checkUserAdminLike( $destination )
    {
        global $GLOBAL_TICKET_PERMISSION;
        if ( !empty( $GLOBAL_TICKET_PERMISSION ) && isset( $GLOBAL_TICKET_PERMISSION['like_admin']) && !empty( $GLOBAL_TICKET_PERMISSION['like_admin']) ){
            if ( isset( $GLOBAL_TICKET_PERMISSION['like_admin'][$destination] ) && $GLOBAL_TICKET_PERMISSION['like_admin'][$destination] == 'true' ){
                return true;
            }
        }
        return false;
    }

}



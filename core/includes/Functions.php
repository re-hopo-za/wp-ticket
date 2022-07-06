<?php

namespace HWP_Ticket\core\includes;




use Hashids\Hashids;
use HWP_Ticket\core\ui\PartialUI;
use WP_User;


class Functions
{


    protected static $_instance = null;
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public static function statusesTranslate( $input )
    {
        switch ( $input ) {
            case 'draft':
                return 'پیش نویس';
            case 'open':
                return 'باز';
            case 'closed':
                return 'بسته';
            case 'in_progress':
                return 'در حال انجام';
            case 'deleted':
                return 'حذف شده';
            case 'is_read':
                return 'خوانده شده';
            case 'waiting':
                return 'منتظر تایید';
            case 'answered':
                return 'پاسخ داده شده';
            case 'finished':
                return 'اتمام یافته';
            case 'first':
                return 'جدید';
        }
    }


    public static function destinationTranslate( $input )
    {
        switch ( $input ) {
            case 'tango_support':
                return 'پشتیبانی';

            case 'tango_license':
                return 'لایسنس دوره';

            case 'tango_other':
                return 'دیگر';

            case 'tango_sale':
                return 'مالی';
        }
        return 'بدون واحد';
    }


    public static function priorityStatus($input)
    {
        if ($input === 3) {
            $priority = 'red';
        } elseif ($input === 2) {
            $priority = 'white';
        } else {
            $priority = 'green';
        }
        return $priority;
    }


    public static function version()
    {
        return '1.2.1';
    }


    //// *  ////
    public static function persian_number($string)
    {
        $persian_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $latin_num = range(0, 9);
        $string = str_replace($latin_num, $persian_num, $string);

        return $string;
    }


    public static function _404(){
        ?>
        <script>
            let body_404 = document.querySelector("body");
            body_404.classList.add("error404");
        </script>
        <?php
        include get_stylesheet_directory().'/404.php';
        exit();
    }



    public static function sanitizeTicketBody( $ticket_content )
    {
        $allowed_html = [
            'a' => [
                'style' => [
                    'text-align'   => true
                ],
                'href' => true
            ],
            'div' => [
                'class' => [
                    'code-toolbar'
                ],
                'style' => [
                    'text-align'   => true,
                ]
            ],
            'p' => [
                'style' => [
                    'text-align'   => true,
                    'margin-right' => true,
                ]
            ],
            'strong' => [
                'style' => [
                    'text-align'   => true,
                ]
            ],
            'span' => [
                'class' => [] ,
                'style' => [
                    'text-align'   => true,
                ]
            ],
            'pre' => [
                'class' => [
                    'language-html' ,
                    'language-css' ,
                    'language-javascript' ,
                    'language-php' ,
                    'language-sql'
                ],
                'tabindex' => [] ,
                'style' => [
                    'text-align' => true
                ]
            ],
            'code' => [
                'class' => [
                    'ck-code_selected',
                    'language-html' ,
                    'language-css' ,
                    'language-javascript' ,
                    'language-php' ,
                    'language-sql'
                ],
                'style' => [
                    'text-align' => true
                ]
            ],
            'h1' => [
                'style' => [
                    'text-align' => true
                ]
            ],
            'h2' => [
                'style' => [
                    'text-align' => true
                ]
            ],
            'h3' => [
                'style' => [
                    'text-align' => true
                ]
            ],
            'li' => [
                'style' => [
                    'text-align' => true
                ]
            ],
            'br' => [
                'data-cke-filler' => [
                    'true',
                    'false'
                ]
            ]
        ];
        return wp_kses( $ticket_content ,$allowed_html , ['https' ,'http'] );
    }


    public static function get_avatar($identification, $size, $url = false)
    {

        if (is_numeric($identification))
            $user = get_user_by('id', (int)$identification);
        elseif (is_email($identification))
            $user = get_user_by('email', $identification);
        elseif ( $identification instanceof WP_User) {
            $user = $identification;
        }

        if (!isset($user->ID)) {
            if ($url) return get_template_directory_uri() . '/assets/public/img/person.png?1';
            return sprintf('<img alt="" src="%1$s/assets/public/img/person.png"  height="%2$d" width="%2$d">', get_template_directory_uri(), $size);
        }

        $img = get_user_meta($user->ID, 'profile_pic', true);

        if (!empty($img)) {
            $img_url = wp_get_attachment_image_src((int)$img, 'thumbnail', false);
            $img_url = (isset ($img_url[0])) ? $img_url[0] : '';
            if ($url) return $img_url;
            return sprintf('<img alt="" src="%1$s"  height="%2$d" width="%2$d">', $img_url, $size);

        } elseif (!isset($user->user_email) || !is_email($user->user_email)) {
            if ($url) return get_template_directory_uri() . '/assets/public/img/person.png?2';
            return sprintf('<img alt="" src="%1$s/assets/public/img/person.png"  height="%2$d" width="%2$d">',
                get_template_directory_uri(), $size);

        } else {
            if (function_exists('get_avatar')) {
                if ($url) return get_avatar_url($user->user_email, $size);
                return get_avatar($user->user_email, $size);
            } else {
                $grav_url = "http://www.gravatar.com/avatar/" .
                    md5(strtolower($user->user_email));
                if ($url) return $grav_url;
                return sprintf('<img alt="" src="%1$s"  height="%2$d" width="%2$d">', $grav_url, $size);
            }
        }
    }

    public static function course_has_license($course_id)
    {
        if (!is_numeric($course_id)) return false;
        $_spotplayer_license = get_post_meta((int)$course_id, '_spotplayer_course', true);
        $_star_force_license = get_post_meta((int)$course_id, '_licensed_product', true);
        return !empty($_spotplayer_license) || !empty($_star_force_license);
    }


    public static function sanitizer($value, $functions)
    {
        $functions = explode(',', $functions);
        foreach ($functions as $function) {
            if (function_exists($function)) {
                $value = $function($value);
            }
        }
        return $value;
    }


    public static function getCourseName( $input )
    {
        if ( $input != 0 &&  $input != 'empty' ){
            $courseName = wc_get_product( $input );
            if ( $courseName !== false ){
                return $courseName->get_title();
            }else{
                return 'دوره یافت نشد';
            }
        }
        return 'بدون دوره';
    }


    public static function encryptID( $id )
    {
        date_default_timezone_set('Asia/Tehran');
        $endOfDay   = strtotime("tomorrow", strtotime("today", time()) )+(10*60*60);
        $key        = Functions::getTicketOptions('hwp_ticket_user_hash_complement' ,'');
        $hashID     = new Hashids( $endOfDay+(int)$key  );
        return $hashID->encode( $id.$key );
    }


    public static function decryptID( $hashedID )
    {
        date_default_timezone_set('Asia/Tehran');
        $endOfDay        = strtotime("tomorrow", strtotime("today", time() ) )+(10*60*60);
        $key             = Functions::getTicketOptions('hwp_ticket_user_hash_complement' ,'' );
        $hashID          = new Hashids( $endOfDay+(int)$key );
        $user_hashed_id  = isset( $hashID->decode( $hashedID )[0] ) ? $hashID->decode( $hashedID )[0] : '';
        $outputUser      = (int) str_replace( $key , '' , $user_hashed_id );
        if ( is_numeric( $outputUser ) and  $outputUser > 0 ){
            return $outputUser;
        }else{
            return false;
        }
    }


    public static function fileExtension( $ex )
    {
        $ex = strtolower( $ex );
        switch ($ex) {
            case 'zip' :
            case 'rar' :
                return PartialUI::fileIcon('zip');
            case 'jpg'  :
            case 'jpeg' :
                return PartialUI::fileIcon('jpeg');
            case 'png' :
                return PartialUI::fileIcon('png');
            case 'pdf' :
                return PartialUI::fileIcon('pdf');
            case 'mp3'  :
            case 'wave' :
                return PartialUI::fileIcon('mp3');
            case 'txt' :
                return PartialUI::fileIcon('txt');
            default :
                return PartialUI::fileIcon('raw');
        }
    }

    public static function isFill( $value ,$key = null ){
        if (!empty( $key ) && (( is_array( $value ) || is_object( $value ) ) )){
            $value = (array) $value;
            return isset( $value[$key]) && !empty( $value[$key] );
        }
        return isset( $value ) && !empty( $value );
    }

    public static function getFillValue( $value ,$key = null ,$empty_value = null ){
        $is_fill = self::isFill( $value, $key );
        if (!empty( $key ) && ((is_array( $value ) || is_object($value) )) ){
            $value = (array) $value;
            return $is_fill ? $value[$key] : $empty_value;
        }
        return $is_fill ? $value : $empty_value;
    }


    public static function indexChecker( $data ,$index ,$default = '' )
    {
        if ( !empty( $data ) ){
            if ( is_array( $data ) ){
                if ( isset( $data[$index] ) ){
                    return $data[$index];
                }
            }elseif ( is_object( $data ) ){
                if ( isset( $data->$index ) ){
                    return $data->$index ;
                }
            }
        }
        return $default;
    }

    public static function tagsSeparator( $tags )
    {
        if ( !empty( $tags ) && is_countable($tags) ){
            return implode( ',' ,  $tags ) ;
        }
        return '';
    }


    public static function roleTransition( $keyword  ){
        $output = '';
        switch ( $keyword ){
            case 'user':
                $output = 'دانشجو';
                break;
            case 'master':
                $output = 'استاد';
                break;
            case 'support':
                $output = 'پشتیبان';
                break;
            case 'admin':
                $output = 'مدیر';
                break;
        }
        return $output;
    }


    public static function adminNonceChecker()
    {
        $nonce = self::indexChecker( $_POST ,'nonce' ,false );
        if( $nonce && !wp_verify_nonce( $nonce, 'hamfy-admin-sec' ) && Users::isAdmin( get_current_user_id() ) ){
            wp_send_json_error('invalid nonce',403  );
        }
        return true;
    }


    public static function getTicketOptions( $optionsName ,$default )
    {
        $option = get_option( $optionsName );
        if ( !empty( $option ) ){
            $option = maybe_unserialize( $option );
            if ( !empty( $option ) ){
                return $option;
            }
        }
        return $default;
    }

    public static function getQueryString()
    {
        $string = $_SERVER['QUERY_STRING'];
        parse_str( $string , $query );

        global $QUERY_STRING_TICKET;

        if( isset( $query['sort'] ) ){
            if ( in_array(  $query['sort'] , Permissions::validSortList() ) || empty( $query['sort'] ) ){
                $QUERY_STRING_TICKET['sort'] = sanitize_text_field( $query['sort'] );
            }
        }else{
            $QUERY_STRING_TICKET['sort'] = '';
        }

        if( isset( $query['limit'] ) ){
            if ( is_numeric( $query['limit'] ) && $query['limit'] <= 500 ){
                $QUERY_STRING_TICKET['limit'] = sanitize_text_field( $query['limit'] );
            }
        }else{
            $QUERY_STRING_TICKET['limit'] = 15;
        }

        if( isset( $query['_page'] ) ){
            if ( is_numeric( $query['_page'] ) ){
                $QUERY_STRING_TICKET['_page'] = sanitize_text_field( $query['_page'] );
            }
        }else{
            $QUERY_STRING_TICKET['_page'] = 0;
        }

        if( isset( $query['search'] ) ){
            if ( strlen( $query['search'] ) < 50 ){
                $QUERY_STRING_TICKET['search'] = sanitize_text_field( $query['search'] );
            }
        }else{
            $QUERY_STRING_TICKET['search'] = '';
        }

        if( isset( $query['status'] ) ){
            $status = unserialize( get_option('_hamfy_ticket_statuses' ,true  ));
            if ( array_key_exists( $query['status'] ,$status )  || empty( $query['status'] ) || $query['status'] == 'first' ){
                $QUERY_STRING_TICKET['status'] = sanitize_text_field( $query['status'] );
            }
        }else{
            $QUERY_STRING_TICKET['status'] = '';
        }

        if( isset( $query['destination'] ) ){
            if ( array_key_exists( $query['destination'] ,Destination::getDestinationsList() ) || !empty( $query['destination'] ) ){
                $QUERY_STRING_TICKET['destination'] = sanitize_text_field( $query['destination'] );
            }
        }else{
            $QUERY_STRING_TICKET['destination'] = '';
        }

        if( isset( $query['course'] ) ){
            if ( is_numeric( $query['course'] ) ) {
                $QUERY_STRING_TICKET['course'] = sanitize_text_field( $query['course'] );
            }
        }else{
            $QUERY_STRING_TICKET['course'] = '';
        }

        if( isset( $query['the_user'] ) ){
            if ( is_numeric( $query['the_user'] ) ) {
                $QUERY_STRING_TICKET['the_user'] = sanitize_text_field( $query['the_user'] );
            }
        }else{
            $QUERY_STRING_TICKET['the_user'] = '';
        }
        $QUERY_STRING_TICKET['username'] = '';
    }


    public static function clearHttpToTicketRequest( $http )
    {
        if ( !empty( $http ) ){
            if ( strpos( $http, '?' ) ){
                $http = substr( $http ,0 ,strpos( $http ,'?' ) );
            }
            if( substr( $http ,-1 ) == '/' ){
                $http = substr( $http, 0, -1 );
            }
            if( strpos( $http ,'/' ) !== false ){
                $http = explode('/' ,$http );
                if ( isset( $http[0] ) && empty( $http[0] ) ){
                    unset( $http[0] );
                }
                return $http;
            }
        }
        return [];
    }


    public static function implodeForQuery( $array )
    {
        if ( !empty( $array ) && is_array( $array) ){
            $array  = implode("','"  , $array );
            return "IN ('$array')";
        }
        return '';
    }

    public static function implodeForDestinationsListQuery( $destinations )
    {
        if ( !empty( $destinations ) && is_array( $destinations) ){
            $dest_list = [];
            foreach ( $destinations as $key => $val ){
                if ( $val == 'true' &&  $val != 'false' ){
                    $dest_list [] = $key;
                }
            }
            $dest_list  = implode("','"  , $dest_list );
            return "('$dest_list')";
        }
        return '';
    }


    public static function getStatusList()
    {
        $statuses = Functions::getTicketOptions('_hamfy_ticket_statuses' ,[] );
        if ( !empty( $statuses ) && is_array( $statuses ) ){
            return $statuses;
        }
        return [];
    }


    public static function returnResult( $statusCode ,$result = null )
    {
        if ( empty( $result ) ){
            $result_text = '';
            switch ( $statusCode ){
                case 200 :
                    $result_text = ' فرایند با موفقعیت انجام شد.  ';
                    break;
                case 201 :
                    $result_text = ' دیتا مورد نظر ذخیره شد.  ';
                    break;
                case 204 :
                    $result_text = ' فرایند با موفقعیت انجام شد ولی داده ای یافت نشد.  ';
                    break;
                case 401 :
                    $result_text = 'برای دریافت اطلاعات بایستی وارد شوید.  ';
                    break;
                case 403 :
                    $result_text = 'شما به این قسمت دسترسی نداری.  ';
                    break;
                case 404 :
                    $result_text = ' دیتای مورد نظر یافت نشد.  ';
                    break;
                case 428 :
                    $result_text = ' خطای دیتای اجباری.  ';
                    break;
                case 500 :
                    $result_text = 'خطای داخلی رخ داده است.  ';
                    break;
            }
            wp_send_json( ['result' => $result_text ] ,$statusCode );
        }
        wp_send_json( $result ,$statusCode );
    }


    public static function returnTime( $time )
    {
        if ( !empty( $time )  ){
            return date_i18n(' d F Y  ساعت: H:i', strtotime( $time ) );
        }
        return 'قالب نادرست';
    }


    public static function getSpotAccount( $userID )
    {
        $spot_account = Users::getMobile( $userID );
        if ( substr( $spot_account, 0, 2 ) == '98') {
            $spot_account = substr( $spot_account , 2 );
        }
        return $spot_account;
    }


    public static function returnAppropriateData( $http_call ,$data ,$extra_data = [] )
    {
        if ( !$http_call ){
            wp_send_json( ['result' => $data ,'extra_data' => $extra_data  ] ,200 );
        }
        echo $data;
    }


    public static function logHandler( $userID ,$logs ,$key ,$val ,$task ='add' )
    {
        $logs = maybe_unserialize( $logs );
        $logs = !is_array( $logs ) ? [] : $logs;
        if ( !empty( $logs ) ){
            if ( $task == 'add' && $key  ){
                $logs[$key][strtotime('now')][$userID] = $val;
            }
            if ( $task == 'delete' ){
                unset( $logs[$key] );
            }
        }else{
            $logs[$key][ $userID ][strtotime('now')] = $val;
        }
        return serialize( $logs );
    }

    public static function ratingChecked( $checked ,$current ,$return ,$else )
    {
        if ( is_numeric( $checked ) && $checked >= $current  ){
            return $return;
        }
        return $else;
    }

    public static function  preparationUsersList( $IDs )
    {
        $row_list   = Database::getUsersByIDs( $IDs );
        $final_list = [];
        if( !empty( $row_list ) ){
            foreach ( $row_list as $user ){
                $final_list[$user->ID] = [
                    'nicename'     => $user->user_nicename ,
                    'email'        => $user->user_email ,
                    'display_name' => $user->display_name,
                    'mobile'       => $user->mobile,
                ];
            }
        }
        return $final_list;
    }

    public static function getBulkUsersSeenList( $ticket )
    {
        $items = [];
        if ( !empty( $ticket ) ){
            if ( isset( $ticket->tags ) ){
                $tags = maybe_unserialize( self::indexChecker( $ticket ,'tags' ) );
                if ( is_array( $tags ) && isset( $tags['supporter_seen_list'] ) ){
                    $seen_list = $tags['supporter_seen_list'];
                    foreach ( $seen_list as $key => $val ){
                        $items[ key($val) ] = $key;
                    }
                }
            }
        }
        return $items;
    }

    public static function getUserCustomField( $userID ,$field )
    {
         if ( is_numeric( $userID ) && !empty( $field ) ){
            $user = get_user_by('id' ,$userID );
            if ( !empty( $user ) && isset( $user->ID ) ){
                return $user->$field;
            }
         }
        return '';
    }



    public static function getUserFromBulk($UesrsIDs , $userID )
    {
        if( !empty( $UesrsIDs ) && isset( $UesrsIDs[$userID] ) ){
            return $UesrsIDs[$userID];
        }
        return [
            'nicename'     => '' ,
            'email'        => '' ,
            'display_name' => ''
        ];
    }


    public static function contentExcerpt( $content ,$count )
    {
        return esc_attr( wp_trim_words( $content, $count ) );
    }


    public static function getAllProducts( $destinations )
    {
        $final_result = [];
        $products = new \WP_Query( [
            'post_type'      => 'product',
            'posts_per_page' => -1 ,
            'post_status'    => ['publish', 'pending', 'draft', 'auto-draft']
        ] );
        if( !empty( $products ) ){
            foreach ( $products->get_posts() as $product ){
                $final_result[ $product->ID ] = $destinations;
            }
        }
        return $final_result;
    }


    public static function getUser( $userID )
    {
        $user_holder = new \stdClass();
        $user_holder->display_name  = 'display_name';
        $user_holder->user_nicename = 'user_nicename';
        $user_holder->first_name    = 'first_name';
        $user_holder->last_name     = 'last_name';
        $user_holder->user_email    = 'user_email';
        $user_holder->ID            = 0;
        $user_holder->user_status   = false;
        $user_object = get_user_by( 'id' ,$userID );
        if ( !empty( $user_object ) ){
            $user_holder->display_name  = $user_object->display_name;
            $user_holder->user_nicename = $user_object->user_nicename;
            $user_holder->first_name    = $user_object->first_name;
            $user_holder->last_name     = $user_object->last_name;
            $user_holder->user_email    = $user_object->user_email;
            $user_holder->ID            = $user_object->ID;
            $user_holder->user_status   = true;
        }
        return $user_holder;
    }



    public static function getUserFromBulkList( $usersList ,$userID ,$key )
    {
        if( is_array( $usersList ) && isset( $usersList[$userID] ) && isset( $usersList[$userID][$key] ) ){
            return $usersList[$userID][$key];
        }
        return '';
    }


    public static function calculateStatus( $userID ,$params ,$parentObject  )
    {
        global $GLOBAL_TICKET_IS_SUP;
        if ( $parentObject->status == 'first' && $parentObject->creator == $userID ){
            return 'first';
        }
        elseif (  $params->act == 'new-reply-pwa' && $parentObject->creator !== $userID && $GLOBAL_TICKET_IS_SUP  ){
            return 'answered';
        }
        return $params->status;
    }


    public static function createTicketDirectly( $data ,$userID ,$currentUserID )
    {
        global $wpdb;
        date_default_timezone_set('Asia/Tehran');
        $data = [
            'title'         => self::indexChecker( $data ,'title' ) ,
            'content'       => self::indexChecker( $data ,'content' ) ,
            'send_method'   => self::indexChecker( $data ,'send_method' ) ,
            'creator'       => self::indexChecker( $data ,'creator' ,get_current_user_id() ) ,
            'destination'   => self::indexChecker( $data ,'destination' ) ,
            'parent_ticket' => self::indexChecker( $data ,'parent_ticket' ,null ) ,
            'main_object'   => self::indexChecker( $data ,'main_object' ) ,
            'reply_to'      => self::indexChecker( $data ,'reply_to' ) ,
            'assign_to'     => self::indexChecker( $data ,'assign_to' ),
            'do_action'     => self::indexChecker( $data ,'do_action' ),
            'order_num'     => self::indexChecker( $data ,'order_num' ,0 ),
            'tags'          => self::logAssignedOnDirectly( $data ,$currentUserID ,$userID ) ,
            'rate'          => self::indexChecker( $data ,'rate' ,null ) ,
            'rate_comment'  => self::indexChecker( $data ,'rate_comment' ) ,
            'status'        => self::indexChecker( $data ,'status' ,'first'),
            'is_public'     => self::indexChecker( $data ,'is_public' ,0)
        ];
        $format = [ '%s','%s','%s','%d','%s','%s','%d','%d','%d','%s','%d','%s','%s','%s','%s','%d' ];
        $new_id = Database::get_instance()::saveTicket( $data ,$format );
        if( is_numeric( $new_id ) ){
            return true;
        }
        return false;
    }

    public static function logAssignedOnDirectly( $data ,$currentUserID ,$userID )
    {
        if ( !empty( $data ) && isset( $data['assign_to'] ) && is_numeric( $data['assign_to'] ) ){
            return self::logHandler( (int) $currentUserID ,[] ,'assign_list' ,(int) $userID );
        }
        return '';
    }


    public static function replaceInputNewTicket( $input )
    {
        if ( !empty( $_GET ) && isset( $_GET[$input] ) && !empty( $_GET[$input] ) ){
            return sanitize_text_field( $_GET[$input] );
        }
        return '';
    }



    public static function getAverageRate( $items )
    {
        $rates  = array_column( $items ,'rate' );
        $output = [];
        if ( !empty( $rates ) ){
            foreach ( $rates as $rate ){
                if ( is_numeric( $rate ) && $rate > 0 ){
                    $output[ $rate ] = $rate;
                }
            }
        }
        return $output;
    }

}


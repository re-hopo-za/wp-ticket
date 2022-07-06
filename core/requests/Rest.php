<?php

namespace HWP_Ticket\core\requests;



use HWP_Ticket\core\includes\Database;
use HWP_Ticket\core\includes\Destination;
use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\includes\Notification;
use HWP_Ticket\core\includes\Permissions;
use HWP_Ticket\core\includes\Uploader;
use HWP_Ticket\core\includes\Users;
use HWP_Ticket\core\ui\TicketUI;
use WP_REST_Request;
use WP_REST_Server;

class Rest{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }




    public static $namespace;
    public static $version;
    public static $endpoint;
    public static $api;
    private static $params;
    private static $userID;



    public function __construct(){
        self::$namespace = 'hamfy';
        self::$version   = 'v1.1';
        self::$endpoint  = 'tickets';
        self::$api       = self::$namespace.'/'.self::$version.'/'.self::$endpoint;

        add_action('rest_api_init' ,[$this ,'routes' ]);
    }


    public function routes()
    {
        date_default_timezone_set('Asia/Tehran');
        register_rest_route( self::$namespace , self::$version.'/'.self::$endpoint , [
            'methods'  => WP_REST_Server::READABLE          ,
            'callback' => [ $this ,'read' ]            ,
            'args'     => self::argsValidator('READABLE')   ,
            'permission_callback' => [ $this , 'authentication' ]
        ]);

        register_rest_route( self::$namespace , self::$version.'/'.self::$endpoint , [
            'methods'  => WP_REST_Server::CREATABLE         ,
            'callback' => [ $this ,'create' ]         ,
            'args'     => self::argsValidator( 'CREATABLE' )  ,
            'permission_callback' => [ $this ,'authentication' ]
        ]);

        register_rest_route( self::$namespace , self::$version.'/'.self::$endpoint , [
            'methods'  => WP_REST_Server::EDITABLE           ,
            'callback' => [ $this ,'update' ]          ,
            'args'     => self::argsValidator('EDITABLE')   ,
            'permission_callback' => [ $this  ,'authentication' ]
        ]);

        register_rest_route( self::$namespace , self::$version.'/'.self::$endpoint  ,[
            'methods'  => WP_REST_Server::DELETABLE          ,
            'callback' => [ $this ,'delete' ]          ,
            'args'     => self::argsValidator('DELETABLE')  ,
            'permission_callback' => [ $this ,'authentication' ]
        ]);
    }


    public function authentication( WP_REST_Request $request )
    {
        self::$params  = (object) $request->get_params();
        self::$userID  = Functions::decryptID( $request->get_headers()['usertoken'][0] );
        $GLOBALS['GLOBAL_TICKET_WHO_IS'] = Permissions::whoIs( self::$userID );
        $GLOBALS['GLOBAL_TICKET_IS_SUP'] = Permissions::isSupporter();
        $GLOBALS['GLOBAL_TICKET_PERMISSION'] = Permissions::getPermissionsList( self::$userID );
        $GLOBALS['GLOBAL_TICKET_USER_TRUST'] = Users::getUserTrustMeta( self::$userID );
        return self::$userID;
    }


    public function read()
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_PERMISSION;
        $params = self::$params;
        $userID = self::$userID;
        $call   = Functions::indexChecker( $params,'call' ,'web' );

        ///// single ticket
        if ( !empty( Functions::indexChecker( $params,'id' ) ) && Functions::indexChecker( $params,'id' ) <> 'new' ){
            $single = Database::get_instance()::single( $params->id );
            if ( !empty( $single ) && Permissions::checkAccessSingleTicket( $userID ,$single ,$GLOBAL_TICKET_WHO_IS ,$GLOBAL_TICKET_PERMISSION ) ){
                Database::updateUsersSeenTickets( $userID ,$single );
                if ( $call == 'web' ){
                    TicketUI::get_instance()::single( $userID ,$single ,false );
                }
                elseif( $call == 'web-preview' && 'admin' === $GLOBAL_TICKET_WHO_IS ){
                    TicketUI::summarySection( $userID ,$params->id );
                }
                elseif ( $call == 'app'  ){
                     Pwa::get_instance()::single( $userID ,$single );
                }
            }

        ///// new tickets ui
        }elseif ( !empty( Functions::indexChecker( $params,'id' ) ) && Functions::indexChecker( $params,'id' ) == 'new' ){
            TicketUI::get_instance()::newTicket( $userID ,false );

        ///// all tickets pwa
        }elseif ( empty( Functions::indexChecker( $params,'id' ) ) && $call == 'app' ){
            Pwa::get_instance()::all( $userID ,$params );

        ///// all tickets website
        }elseif ( empty( Functions::indexChecker( $params,'id' )  ) && $call == 'web' ){
            TicketUI::get_instance()::ticketRoot( $userID ,$params ,false );
        }
        Functions::returnResult( 403 );
    }



    public static function create()
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_IS_SUP;
        $params      = self::$params;
        $act         = Functions::getFillValue( $params ,'act');
        $course      = Functions::getFillValue( $params ,'course') ;
        $destination = Functions::getFillValue( $params ,'destination' );
        $token       = Functions::getFillValue( $params ,'token' );
        $parent_id   = Functions::getFillValue( $params ,'parent_id' );

        if ( in_array( $act ,['reply','new-reply-pwa'] ) ){
            if ( !$GLOBAL_TICKET_IS_SUP && ( empty( $params->content ) || strlen( $params->content ) < 2 ) ){
                Functions::returnResult( 400 ,
                    [
                        "code" => "rest_missing_callback_param" ,
                        "message" => "پارامتر(های) گمشده: content",
                        "data" => [  "status" => 400, "params" => [ "content" ] ]
                    ]
                );
            }
            $parent = Database::get_instance()::single( (int) $parent_id );
            if ( empty( $parent ) ){
                Functions::returnResult( 403 );
            }elseif ( isset( $parent->status ) &&  $parent->status == 'finished' ){
                Functions::returnResult(403 , [ 'result' => 'این تیکت پایان یافته و دیگر قادر به ارسال پاسخ روی آن نیستید. لطفا تیکت جدید ایجاد نمایید.'] );
            }
            Permissions::get_instance()::checkTicketPermission( $token ,self::$userID ,$parent->main_object ,$parent->destination ,true ,(object) $parent   );
            self::save( self::$userID ,$parent );
            Database::updateUsersSeenTickets( self::$userID ,$parent ,false );
            if ( 'new-reply-pwa' == $act ){
                Pwa::single( self::$userID ,$parent ); 
            }else{
                TicketUI::childTickets( self::$userID ,$parent ,true ,false );
            }
        }

        if( 'new-ticket' == $act || 'new-ticket-pwa' == $act ){
            if ( empty( $params->content ) || strlen( $params->content ) < 2 ){
                Functions::returnResult( 400 ,
                    [
                        "code" => "rest_missing_callback_param" ,
                        "message" => "پارامتر(های) گمشده: content",
                        "data" => [  "status" => 400, "params" => [ "content" ] ]
                    ]
                );
            }
            Permissions::get_instance()::checkTicketPermission( $token ,self::$userID ,$course ,$destination ,false ,null );
            $new_ticket_id = self::save( self::$userID );
            if( is_integer( $new_ticket_id ) ){
                $new_ticket_ob = Database::get_instance()::single( $new_ticket_id );
                Notification::send_notification('new_ticket' ,(object) $new_ticket_ob  ,$GLOBAL_TICKET_WHO_IS ,self::$userID );
                if ( 'new-ticket' == $act ){
                    TicketUI::get_instance()::single( self::$userID ,(object)$new_ticket_ob ,false );
                }else{
                    wp_send_json( ['Result'=>$new_ticket_id] );
                }
            }
        }
        if ( 'comment' == $act && 'admin' == Permissions::whoIs( self::$userID ) ){
            Permissions::checkRecaptcha( $token );
            self::save( self::$userID );
            $parent = Database::get_instance()::single( (int) $parent_id );
            TicketUI::childTickets( self::$userID ,$parent ,true ,false );
        }
        Functions::returnResult( 400 ,'درخواست نامرتبت');
    }


    public static function save( $userID ,$parentObject = false )
    {
        global $GLOBAL_TICKET_WHO_IS;
        $params = self::$params;
        $inserted_id = '';

        if ( in_array( $params->act ,['reply','new-reply-pwa'] ) ) {
            $status = Functions::calculateStatus( $userID ,$params ,$parentObject );
            $inserted_id = Database::get_instance()::saveTicket(
                [
                    'content'        => $params->content     ,
                    'creator'        => $userID              ,
                    'parent_ticket'  => $params->parent_id   ,
                    'status'         => $status
                ] ,
                ['%s','%d','%d','%s']
            );
            if( is_numeric( $inserted_id ) ){
                $parent = Database::updateParent( $params->parent_id ,$status  );
                Notification::send_notification('response_ticket' ,(object) $parent ,$GLOBAL_TICKET_WHO_IS ,self::$userID );
            }
        }
        elseif( 'new-ticket' === $params->act || 'new-ticket-pwa' === $params->act  ){
            $inserted_id = Database::get_instance()::saveTicket(
                [
                    'title'       => $params->title       ,
                    'content'     => $params->content     ,
                    'creator'     => $userID              ,
                    'destination' => $params->destination ,
                    'main_object' => $params->course      ,
                    'status'      => 'first'              ,
                    'order_num'   => 2
                ] ,
                ['%s','%s','%d','%s','%s','%s','%d']
            );

        }
        elseif ( 'comment' === $params->act ){
            $inserted_id = Database::get_instance()::saveTicket(
                [
                    'content'       => $params->content   ,
                    'parent_ticket' => $params->parent_id ,
                    'creator'       => $userID            ,
                    'main_object'   => 'comment'
                ],
                [ '%s','%s','%d','%s'  ]
            );

        }
        elseif ( 'master-new' === $params->act ){
            $inserted_id = Database::get_instance()::saveTicket(
                [
                    'title'       => $params->title   ,
                    'content'     => $params->content ,
                    'creator'     => $userID         ,
                    'destination' => 'tango_support' ,
                    'main_object' => $params->course ,
                    'status'      => 'first'         ,
                    'order_num'   => 2               ,
                    'assign_to'   => $params->assign_to
                ],
                ['%s','%s','%d','%s','%s','%s','%d','%d']
            );
        }

        if ( !is_integer( $inserted_id ) ){
            Functions::returnResult( 500 ,'خطا هنگام ذخیره' );
        }
        elseif ( !empty( $params->files ) && is_array( $params->files ) && 'comment' !== $params->act ) {
            Uploader::get_instance()::saveFiles( $params->files ,$inserted_id ,$userID );
        }
        return $inserted_id;
    }



    public function update()
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_IS_SUP;
        global $GLOBAL_TICKET_PERMISSION;
        $userID    = self::$userID;
        $params    = self::$params;
        $parentOb  = Database::get_instance()::single( $params->ticket_id );
        $result    = '';
        if ( !empty( $parentOb ) ){
            if( $params->action == 'update_destination' && ( $GLOBAL_TICKET_WHO_IS == 'admin' || 'support' == $GLOBAL_TICKET_WHO_IS  ) ){
                $result = Database::changeDestination( $parentOb ,$params->n_destination ,$userID ,$GLOBAL_TICKET_PERMISSION );
            }
            elseif( $params->action == 'assign_to_user' && ('admin' == $GLOBAL_TICKET_WHO_IS || 'support' == $GLOBAL_TICKET_WHO_IS  ) ){
                $result = Database::assignToAnotherSupporter( $parentOb ,$params->assign_to ,$userID ,$GLOBAL_TICKET_PERMISSION );
                if ( $params->assign_to > 0 ){
                    Notification::send_notification('assign_ticket' ,(object) $parentOb  ,$GLOBAL_TICKET_WHO_IS ,$params->assign_to );
                }
            }
            elseif( $params->action == 'remove_read' && 'admin' == $GLOBAL_TICKET_WHO_IS   ){
                $result = Database::clearSeenList( $userID ,$parentOb );

            }
            elseif( $params->action == 'update_status' && $GLOBAL_TICKET_IS_SUP ){
                $result = Database::updateStatusWithoutContent( $userID ,$parentOb ,$GLOBAL_TICKET_PERMISSION );

            }
            elseif( $params->action == 'add_rating' && $userID == $parentOb->creator ){
                $ticketOb = Database::get_instance()::single( $params->parent_id ,true );
                $result   = Database::addRate( $userID ,$ticketOb ,$parentOb ,$params );
            }
        }
        if ( empty( $result ) ){
            Functions::returnResult(200 );
        }else{
            Functions::returnResult(500 );
        }
    }


    public function delete()
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_IS_SUP;
        $params     = self::$params;
        $ticketOb   = Database::get_instance()::single( $params->ticket_id ,true );
        if( !empty( $ticketOb ) ){
            $logs = unserialize( $ticketOb->tags );
            if ( ( $ticketOb->creator == self::$userID && $GLOBAL_TICKET_IS_SUP ) || 'admin' == $GLOBAL_TICKET_WHO_IS  ){
                if ( ( !isset( $logs['owner_seen_reply']) || empty( $logs['owner_seen_reply'] ) ) || 'admin' == $GLOBAL_TICKET_WHO_IS ) {
                    $result = Database::ticketSoftDelete( self::$userID ,$ticketOb );
                    if( empty( $result ) ){
                        Functions::returnResult(200 , [ 'Status' => 'Deleted'] );
                    }else{
                        Functions::returnResult(500  );
                    }

                }else{
                    Functions::returnResult(403 );
                }
            }
        }
    }



    public function argsValidator( $which )
    {
        $args=[];
        if ( $which == 'READABLE' ){
            $args['call']=[
                'required'           => true           ,
                'description'        => 'تعیین نوع داده بازگشتی'  ,
                'type'               => 'string'       ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return  $value == 'web' || $value == 'app' || $value == 'web-preview' ;
                },
            ];
            $args['sort']=[
                'required'           => false           ,
                'description'        => 'مرتب سازی'     ,
                'type'               => 'string'        ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    $value = $value == 'default' ? '' : $value ;
                    return ( in_array( $value , Permissions::validSortList() ) || empty( $value ) ) ;
                },
            ];
            $args['id']=[
                'required'           => false        ,
                'description'        => 'شناسه تیکت' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return $value;
                },
                'validate_callback'  => function( $value ){
                    return ( $value > 0 || $value == 'new' );
                },
            ];
            $args['course']=[
                'required'           => false          ,
                'description'        => 'انتخاب دوره'  ,
                'type'               => 'string'       ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function(){
                    return true;
                },
            ];
            $args['destination']=[
                'required'           => false       ,
                'description'        => 'مقصد تیکت' ,
                'type'               => 'string'    ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value ,'sanitize_text_field,trim' );
                },
                'validate_callback'  => function( $value ){
                    return array_key_exists( $value , Destination::get_instance()::getDestinationsList() ) || $value == null;
                },
            ];
            $args['status']=[
                'required'           => false        ,
                'description'        => 'وضعیت تیکت' ,
                'type'               => 'string'     ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value  ,'sanitize_text_field,trim' );
                },
                'validate_callback'  => function( $value ){
                    return array_key_exists( $value ,Functions::getTicketOptions('_hamfy_ticket_statuses' ,[] ) ) ||  $value == null || $value == 'first' ;
                },
            ];
            $args['the_user']=[
                'required'           => false        ,
                'description'        => 'تیکت های مخصوص یک کاربر خاص' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return $value;
                },
                'validate_callback'  => function( $value ){
                    return $value > 0 || $value == null;
                },
            ];
            $args['last_response']=[
                'required'           => false        ,
                'description'        => 'آخرین پاسخ های ارسال شده' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return $value;
                },
                'validate_callback'  => function( $value ){
                    return $value ==  0 || $value ==  1 ;
                },
            ];
            $args['unseen_tickets']=[
                'required'           => false        ,
                'description'        => 'تیکت های رویت نشده' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return $value;
                },
                'validate_callback'  => function( $value ){
                    return $value ==  0 || $value ==  1 ;
                },
            ];
            $args['n_reply_tickets']=[
                'required'           => false        ,
                'description'        => 'تیکت های پاسخ داده نشده' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return $value;
                },
                'validate_callback'  => function( $value ){
                    return $value ==  0 || $value ==  1 ;
                },
            ];
            $args['limit']=[
                'required'           => false                   ,
                'description'        => 'تعداد تیکت قابل نمایش' ,
                'type'               => 'int'                   ,
                'default'            => 10                      ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return is_numeric( $value );
                },
            ];
            $args['_page']=[
                'required'           => false                ,
                'description'        => 'صفحه در حال نمایش ' ,
                'type'               => 'int'                ,
                'default'            => null                 ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return is_numeric( $value );
                },
            ];
            $args['search']=[
                'required'           => false      ,
                'description'        => 'جستجو'    ,
                'type'               => 'string'   ,
                'default'            => null       ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value  ,'sanitize_text_field,trim' );
                },
                'validate_callback'  => function( $value ){
                    return true;
                },
            ];
        }
        elseif ( $which == 'CREATABLE' )
        {
            $args['act'] = [
                'required'           => false        ,
                'description'        => 'عملیات'     ,
                'type'               => 'string'     ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer($value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return  $value == 'new-ticket' || $value == 'reply' || $value == 'comment' || $value == 'master-new' || $value == 'new-ticket-pwa' || $value == 'new-reply-pwa';
                },
            ];
            $args['title'] = [
                'required'           => false        ,
                'description'        => 'موضوع تیکت' ,
                'type'               => 'string'     ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer($value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return strlen($value) <= 250  ;
                },
            ];
            $args['content']=[
                'required'           => true        ,
                'description'        => 'متن تیکت'  ,
                'type'               => 'string'    ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizeTicketBody( $value );
                },
                'validate_callback'  => function( $value ){
                    return strlen($value)<=10000 ;
                },

            ];
            $args['send_method']=[
                'required'           => true             ,
                'description'        => 'متد ارسال تیکت' ,
                'type'               => 'string'         ,
                'default'            => 'ticket'         ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value  ,'sanitize_text_field,trim' );
                },
                'validate_callback'  => function($value){
                    return $value === 'ticket';
                },
            ];
            $args['destination']=[
                'required'           => false       ,
                'description'        => 'مقصد تیکت' ,
                'type'               => 'string'    ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value  ,'sanitize_text_field,trim' );
                },
                'validate_callback'  => function( $value ){
                    return array_key_exists( $value , Destination::get_instance()::getDestinationsList() );
                },
            ];
            $args['course']=[
                'required'           => false         ,
                'description'        => 'انتخاب دوره' ,
                'type'               => 'string'      ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return  true;
                },
            ];
            $args['status']=[
                'required'           => false        ,
                'description'        => 'وضعیت تیکت' ,
                'type'               => 'int'        ,
                'default'            => 'open'       ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value  ,'sanitize_text_field,trim' );
                },
                'validate_callback'  => function( $value ){
                    return array_key_exists( $value ,Functions::getTicketOptions('_hamfy_ticket_statuses' ,[] ) ) ||  $value == null;
                },
            ];
            $args['priority']=[
                'required'           => false     ,
                'description'        => 'اولویت'  ,
                'type'               => 'int'     ,
                'default'            => 0         ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function($value){
                    return $value <= 3  ;
                },
            ];
            $args['files']=[
                'required'           => false           ,
                'description'        => 'فایل های تیکت' ,
                'type'               => 'array'         ,
                'default'            => []              ,
                'sanitize_callback'  => function( $value ){
                    return array_map('intval',$value );
                },
            ];
            $args['reply_id']=[
                'required'           => false           ,
                'description'        => 'شناسه پاسخ داده شده' ,
                'type'               => 'int'         ,
                'default'            =>  0            ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function($value){
                    return is_numeric( $value );
                },
            ];
            $args['assign_to']=[
                'required'           => false        ,
                'description'        => 'ارجاع به دانشجو' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return $value >= 0;
                },
            ];



        }
        elseif ( $which == 'EDITABLE' ){

            $args['action']=[
                'required'           => true          ,
                'description'        => 'نوع به روز رسانی' ,
                'type'               => 'string'      ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function(){
                    return true;
                },
            ];
            $args['ticket_id']=[
                'required'           => false        ,
                'description'        => 'شناسه تیکت' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return $value > 0;
                },
            ];
            $args['parent_id']=[
                'required'           => false        ,
                'description'        => 'شناسه تیکت والد' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return $value > 0;
                },
            ];
            $args['cUser']=[
                'required'           => false        ,
                'description'        => 'شناسه کاربر' ,
                'type'               => 'int'         ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return $value > 0;
                },
            ];
            $args['n_destination']=[
                'required'           => false        ,
                'description'        => ' واحد جدید یا واحد فعلی'  ,
                'type'               => 'string'        ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return array_key_exists( $value , Destination::get_instance()::getDestinationsList() );
                },
            ];
            $args['o_destination']=[
                'required'           => false        ,
                'description'        => 'واحد قدیم' ,
                'type'               => 'string'        ,
                'sanitize_callback'  => function( $value ){
                    return Functions::sanitizer( $value ,'sanitize_text_field,trim');
                },
                'validate_callback'  => function( $value ){
                    return array_key_exists( $value , Destination::get_instance()::getDestinationsList() );
                },
            ];
            $args['assign_to']=[
                'required'           => false        ,
                'description'        => 'ارجاع به کاربر' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return $value >= 0;
                },
            ];
            $args['rate']=[
                'required'           => false        ,
                'description'        => 'امتیاز' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return intval( $value );
                },
                'validate_callback'  => function( $value ){
                    return $value >= 0 && $value <= 5;
                },
            ];
        }

        elseif ( $which == 'DELETABLE' ){
            $args['ticket_id']=[
                'required'           => false        ,
                'description'        => 'شناسه تیکت' ,
                'type'               => 'int'        ,
                'sanitize_callback'  => function( $value ){
                    return (int) $value ;
                },
                'validate_callback'  => function( $value ){
                    return is_numeric($value);
                },
            ];
        }

        return $args;
    }




}





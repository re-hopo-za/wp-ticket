<?php


namespace HWP_Ticket\core\requests;


use HWP_Ticket\core\includes\Database;
use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\ui\PartialUI;

class Pwa
{
    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public static function single( $userID ,$tickets ){

        global $wpdb;
        $items = [];
        $table = Database::get_instance()::$tickets;

        $parentUser = Functions::getUser( $tickets->creator );
        $items['parent'] = [
            'id'            => $tickets->id ,
            'title'         => $tickets->title ,
            'content'       => $tickets->content ,
            'status'        => Functions::statusesTranslate($tickets->status) ,
            'destination'   => Functions::destinationTranslate($tickets->destination) ,
            'main_object'   => $tickets->main_object==='empty'?'بدون دوره': get_the_title($tickets->main_object) ,
            'owner'         => $tickets->creator == $userID ? 'sent' : 'received' ,
            'creator'       => $parentUser->first_name. ' ' .$parentUser->last_name ,
            'created_date'  => date_i18n( "Y/m/d - H:i" , strtotime( $tickets->created_date) ),
            'profile_image' => Functions::get_avatar( $tickets->creator , 50 , true ) ,
            'files'         => PartialUI::filesLoop( $tickets->id ) ,
        ];

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE parent_ticket=%d 
                      AND ( status IS NULL OR status <> 'deleted') 
                      AND is_public =0;" ,$tickets->id
                )
        );

        if ( !empty( $results ) ){
            foreach ( $results as $item ) {
                $userData = Functions::getUser( $item->creator );
                $items['children'][] = [
                    'id'            => $item->id ,
                    'content'       => $item->content ,
                    'owner'         => $item->creator == $userID ? 'sent' : 'received' ,
                    'creator'       => $userData->first_name. ' ' .$userData->last_name ,
                    'created_date'   => date_i18n( "Y/m/d - H:i" , strtotime( $tickets->created_date) ),
                    'profile_image' => Functions::get_avatar( $item->creator , 50 , true ) ,
                    'files'         => PartialUI::filesLoop( $item->id )
                ];
            }
        }
        if ( !empty( $items ) ){
            wp_send_json( [ 'Result' => $items ] , 200  );
        }else{
            Functions::returnResult(404 );
        }

    }



    public static function all( $userID ,$params ){
        $items   = [];
        $results = Database::get_instance()::getAllTickets( $userID ,$params );

        if( !empty( $results ) ){
            foreach ( $results as $item ) {
                $userData = Functions::getUser( $item->creator );

                $items [] = [
                    'id'            => $item->id ,
                    'title'         => $item->title ,
                    'content'       => wp_trim_words($item->content,50) ,
                    'creator'       => $item->creator ,
                    'parent_ticket' => $item->parent_ticket ,
                    'reply_to'      => $item->reply_to ,
                    'order_num'     => $item->order_num ,
                    'priority'      => Functions::priorityStatus( $item->order_num ) ,
                    'rate_comment'  => $item->rate_comment ,
                    'first_name'    => $userData->first_name ,
                    'last_name'     => $userData->last_name ,
                    'destination'   => Functions::destinationTranslate( $item->destination ) ,
                    'profile_image' => Functions::get_avatar( $item->creator , 50 , true ) ,
                    'status'        => $item->status,
                    'create_date'   => '<b dir="rtl"><time>'.date_i18n( " d F Y  ساعت: H:i" , strtotime( $item->created_date) ) .'</time></b>' ,
                    'update_date'   => human_time_diff( time() , strtotime( $item->updated_date ) ).' قبل ' ,
                    'phone'         => get_user_meta('force_verified_mobile' , $userData->ID ) ,
                    'main_object'   => Functions::getCourseName( $item->main_object ),

                ];
            }
        } 
        wp_send_json( [
            'result'             => "set" ,
            'loop'               => $items ,
        ] , 200  );

    }

}
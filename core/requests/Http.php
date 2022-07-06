<?php


namespace HWP_Ticket\core\requests;


use HWP_Ticket\core\Enqueues;
use HWP_Ticket\core\includes\Database;
use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\includes\Permissions;
use HWP_Ticket\core\ui\AnalyticsUI;
use HWP_Ticket\core\ui\TextTemplateUI;
use HWP_Ticket\core\ui\TicketUI;


class Http
{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public static function httpRequest( $params )
    {
        date_default_timezone_set('Asia/Tehran');

        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_PERMISSION;
        global $QUERY_STRING_TICKET;
        $main_param   = Functions::indexChecker( $params , 1 , false );
        $second_param = Functions::indexChecker( $params , 2 , false );
        $third_param  = Functions::indexChecker( $params , 3 , false );
        $userID       = get_current_user_id();
        $_404_status  = true;



        if ( $userID > 0 && $main_param == 'ticket' ){
            if ( empty( $second_param ) && empty( $third_param ) ){
                TicketUI::get_instance()::ticketRoot( $userID ,$QUERY_STRING_TICKET );
                $_404_status = false;

            }elseif ( !empty( $second_param ) && !empty( $third_param ) && is_numeric( $third_param ) ){
                if( $second_param == 'user'){
                    $QUERY_STRING_TICKET['the_user'] = $third_param;
                    $QUERY_STRING_TICKET['username'] = Functions::getUserCustomField( $third_param ,'nicename' );
                    TicketUI::get_instance()::ticketRoot( $userID ,$QUERY_STRING_TICKET );
                    $_404_status = false;

                }elseif( $second_param == 'single' ){
                    $ticketObject = Database::get_instance()::single( $third_param );
                    if ( !empty( $ticketObject ) && Permissions::get_instance()::checkAccessSingleTicket( $userID ,$ticketObject ,$GLOBAL_TICKET_WHO_IS ,$GLOBAL_TICKET_PERMISSION ) ){
                         Database::updateUsersSeenTickets( $userID ,$ticketObject );
                         TicketUI::get_instance()::single( $userID ,$ticketObject  );
                         $_404_status = false;
                    }
                }
                
            }elseif ( !empty( $second_param ) && empty( $third_param ) ){
                if( $second_param == 'new' ){
                    TicketUI::get_instance()::newTicket( $userID );
                     $_404_status = false;

                }elseif( $second_param == 'template') {
                    add_action( 'wp_enqueue_scripts' ,Enqueues::templateScripts() ,99 );
                    if ( Permissions::isSupporter() ) {
                        TextTemplateUI::get_instance()::templateList();
                        $_404_status = false;
                    }

                }elseif( $second_param == 'dashboard' && (user_can( $userID ,'administrator') || 88716 == $userID ) ) {
                    add_action( 'wp_enqueue_scripts' ,Enqueues::analyticsScripts() ,99 );
                    AnalyticsUI::dashboard();
                    $_404_status = false;

                }elseif( $second_param == 'master-new' ) {
                    add_action( 'wp_enqueue_scripts' ,Enqueues::masterScripts() ,99 );
                    TicketUI::get_instance()::masterForm( $userID );
                    $_404_status = false;
                }
            }
            if ( $_404_status ){
                Functions::_404();
            }

        }
    }



    public static function ticketUrl( $single = null ,$user = null ){
        $url = home_url().'/ticket';
        if ( !empty( $single ) ){
            $url .= '/single/'.$single;
        }elseif( !empty( $user ) ){
            $url .= '/user/'.$user;
        }
        return $url;
    }



}
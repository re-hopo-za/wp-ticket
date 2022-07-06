<?php


namespace HWP_Ticket\core\cron;




use HWP_Ticket\core\includes\Database;
use HWP_Ticket\core\includes\Functions;

class ChangeStatus{

    protected static $_instance = null;
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public static function runCron(){

        $close_day   = Functions::getTicketOptions('hwp_ticket_change_ticket_status_close' ,2 );
        $finish_day  = Functions::getTicketOptions('hwp_ticket_change_ticket_status_finish' ,10 );
        $text_close  = Functions::getTicketOptions('hwp_ticket_change_ticket_status_text_close' ,'' );
        $text_finish = Functions::getTicketOptions('hwp_ticket_change_ticket_status_text_finish' ,'' );

        $run[] = [
            'day'        => $close_day ,
            'old_status' => 'answered',
            'new_status' => 'closed',
            'text'       => $text_close
        ];
        $run[] = [
            'day'        => $finish_day,
            'old_status' => 'closed',
            'new_status' => 'finished',
            'text'       => $text_finish
        ];

        foreach ( $run as $item ){
            self::cronQuery( $item );
        }
    }

    public static function cronQuery( $item ){
        global $wpdb;
        $table = Database::get_instance()::$tickets;

        $query = sprintf("select id from {$table}  where status ='%s' and parent_ticket IS NULL and updated_date < '%s'"
                      ,$item['old_status'] ,date('Y-m-d H:i:s' ,strtotime('-'.$item['day'].' days')  )  );

        $tickets_id = $wpdb->get_results( $query ,ARRAY_A );
        $tickets_id = array_column( $tickets_id  ,'id' );

        if (!is_wp_error( $tickets_id ) && !empty( $tickets_id )){
            foreach ( $tickets_id as $ticket_id ){
                self::changeTicketStatus( $ticket_id , $item['text'] ,$item['new_status'] );
            }
        }
    }


    public static function changeTicketStatus( $ticket_id , $message , $status ){
        global $wpdb;
        $table = Database::get_instance()::$tickets;
        $wpdb->insert( $table, [
            'content'        => $message        ,
            'title'          => 'System Crons'  ,
            'send_method'    => 'system'        ,
            'parent_ticket'  => (int)$ticket_id ,
            'creator'        => 0               ,
            'main_object'    => 'ticket'        ,
            'status'         => $status
        ], ['%s' ,'%s' ,'%d' ,'%d' ,'%s','%s'] );

        $id  = $wpdb->insert_id;
        if ( is_integer( $id ) ) {
            $wpdb->update(
                $table  ,
                ['status' => $status,
                'updated_date' => date('Y-m-d H:i:s')],
                [ 'id' => (int) $ticket_id ]
            );
        }
    }

}


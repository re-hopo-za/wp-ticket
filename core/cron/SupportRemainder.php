<?php


namespace HWP_Ticket\core\cron;





use HWP_Ticket\core\includes\Database;
use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\includes\Permissions;

class SupportRemainder
{

    protected static $_instance = null;
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function runCron()
    {
         self::cronQuery( Functions::getTicketOptions('hwp_ticket_send_reminder_to_masters' ,24 )  );
    }

    public static function cronQuery( $time )
    {
        global $wpdb;
        $table   = Database::get_instance()::$tickets;

        $opens   = [];
        $query   = sprintf("SELECT * FROM  {$table}  WHERE (status ='first' OR status ='open') AND destination='tango_support' AND parent_ticket IS NULL AND updated_date < '%s'"
          , date('Y-m-d H:i:s', strtotime('-' . $time . ' hours')));
        $tickets = $wpdb->get_results( $query );

        if ( !is_wp_error( $tickets ) && !empty( $tickets )) {
            foreach ( $tickets as $ticket ) {
                $opens[$ticket->main_object][]= $ticket->id;
            }
        }
        self::sendSMS( $opens );
    }


    public static function sendSMS( $opens )
    {
          $lists = self::getRelatedMaster( $opens );
          foreach ( $lists as $key => $val ){
              $mobile = get_user_meta( (int) $key ,'force_verified_mobile' ,true );
              $name   = Functions::getUser( $key )->first_name;
              $count = count( explode(',' , $val ) );
              if ( isset( $mobile ) ){
                  if (function_exists('cron_send_sms'))
                      cron_send_sms( $mobile , 'teacher-ticket-reminder' , 99 ,$name ,$count ,$val );
              }
          }
    }


    public static function getRelatedMaster( $opens )
    {
        $masters = Permissions::getMasters();
        $final_master = [];
        $masters_row  = [];
        foreach ( $opens as $key => $val ){
            foreach ( $masters as $ke => $va ){
                if ( in_array( $key , $va ) ){
                    $masters_row[$ke][] = $val;
                }
            }
        }
        foreach ( $masters_row as $m_k => $m_v ){
            foreach ( $m_v as $item ){
                $final_master[$m_k] = $final_master[$m_k].','.implode( ',' ,$item );
            }
        }
        return $final_master;
    }


}
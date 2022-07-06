<?php

namespace HWP_Ticket\core\cron;


class CronTask{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }



    public function __construct() {
         $this->schedule();
//        add_filter( 'cron_schedules', [$this,'isa_add_cron_recurrence_interval'] );

    }


    protected function schedule() {
        if ( !wp_next_scheduled('hamfy_daily_task') ) {
            wp_schedule_event( time(), 'daily', 'hamfy_daily_task' );
        }
    }

}
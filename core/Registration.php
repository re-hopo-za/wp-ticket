<?php
//is checked

namespace HWP_Ticket\core;




class Registration{


    protected static $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct()
    {
        register_activation_hook(HWP_TICKET__FILE__   ,[$this , 'activation' ] );
        register_deactivation_hook(HWP_TICKET__FILE__ ,[$this , 'deactivation'] );
    }


    public function activation()
    {
//        Database::get_instance()->create_need_database();
        self::insertNeedOptions();
        add_action('plugins_loaded', [$this, 'textDomainLoader']);
    }

    public function textDomainLoader()
    {
        load_plugin_textdomain(
            'hamyarNotify' ,
            false ,
            HWP_TICKET_LANGUAGE_URL
        );
    }


    public function deactivation()
    {
        wp_clear_scheduled_hook('hamfy_daily_task');
    }


    public static function insertNeedOptions()
    {
        update_option('_hamfy_ticket_statuses' , serialize( [
            'open'        => 'باز' ,
            'closed'      => 'بسته' ,
            'in_progress' => 'در حال انجام' ,
            'answered'    => 'پاسخ داده شده' ,
            'finished'    => 'اتمام یافته'
        ]  )   );


        update_option('_hamfy_ticket_destinations' , serialize( [
            'tango_license'   => 'لایسنس دوره' ,
            'tango_support'   => 'پشتیبانی' ,
            'tango_other'     => 'دیگر' ,
            'tango_sale'      => 'مالی'
        ]   )   );

    }













}


<?php
//is checked

namespace HWP_Ticket\core;


class Endpoint
{


    protected static $_instance = null;
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct()
    {
        add_action('init' ,[$this , 'add_rewrite'] ,99 );
        add_filter('theme_page_templates' ,[$this, 'add_template'] ,10 ,4 );
        add_filter('template_include' ,[$this, 'load_template'] );
    }


    public function add_template( $post_templates ,$wp_theme ,$post ,$post_type )
    {
        $post_templates['ticket-template.php'] = 'Tickets';
        return $post_templates;
    }


    public function load_template( $template ){

        if ( get_page_template_slug() === 'ticket-template.php') {
            if ( $theme_file = locate_template( ['ticket-template.php'] ) ) {
                $template = $theme_file;
            } else {
                $template = HWP_TICKET_ROOT . 'ticket-template.php';
            }
        }
        return $template;
    }


    public function add_rewrite(){
        add_rewrite_rule('ticket/([0-9]*)', 'index.php/ticket?ticket=$1');
    }


}



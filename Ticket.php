<?php
/**
 * Plugin Name:       تیکت جدید همیار
 * Version:           2.0.0
 * Author:            reza hossein pour
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hamyarNotify
 * Domain Path:       /core/languages/
 */


namespace HWP_Ticket;
use HWP_Ticket\core\Loader;
use HWP_Ticket\core\Registration;


if (!defined('WPINC')) {
    die;
}


class Ticket
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
        self::define();
        self::registeration();
        self::run();
    }


    public static function define()
    {
        define( 'HWP_TICKET_DEVELOPER_MODE', true );
        define( 'HWP_TICKET_VERSION'        , '2.3.7');
        define( 'HWP_TICKET_ROOT'           , plugin_dir_path(__FILE__) );
        define( 'HWP_TICKET_ASSETS'         , plugin_dir_url(__FILE__) . 'assets/');
        define( 'HWP_TICKET_ADMIN_ASSETS'   , HWP_TICKET_ASSETS . 'admin/'); 
        define( 'HWP_TICKET_PUBLIC_ASSETS'  , HWP_TICKET_ASSETS . 'public/');
        define( 'HWP_TICKET_CORE'           , HWP_TICKET_ROOT   . '/core/');
        define( 'HWP_TICKET_LANGUAGE_URL'   , dirname(plugin_basename(__FILE__)) . '/languages/');
        define( 'HWP_TICKET__FILE__'        , __FILE__ );
        define( 'HWP_TICKET_SCRIPTS_VERSION' ,
            HWP_TICKET_DEVELOPER_MODE ? time() : HWP_TICKET_VERSION
        );
        require_once HWP_TICKET_ROOT .'vendor/autoload.php';
    }


    public static function run()
    {
         Loader::get_instance();
    }

    public static function registeration()
    {
        Registration::get_instance();
    }

}

Ticket::get_instance();











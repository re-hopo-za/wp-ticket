<?php


namespace HWP_Ticket\core;




use HWP_Ticket\core\includes\AnalyticsData;
use HWP_Ticket\core\includes\Course;
use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\includes\Permissions;
use HWP_Ticket\core\ui\PartialUI;


class Enqueues
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
        if ( is_admin() ){
            add_action( 'admin_enqueue_scripts', [$this , 'dashboardScripts'] );
        }
        add_action('wp_enqueue_scripts' , [$this ,'frontScripts']  );
    }


    public function dashboardScripts()
    {
        wp_enqueue_script(
            'hamfy_admin_main_js' ,
            HWP_TICKET_ADMIN_ASSETS.'hamfy.js' ,
             ['jquery' , 'select2' ] ,
            HWP_TICKET_SCRIPTS_VERSION
        );
        wp_localize_script(
            'hamfy_admin_main_js' ,
            'hamfy_admin_object',
            [
                'admin_url' => admin_url( 'admin-ajax.php' ) ,
                'hamfy'     => wp_create_nonce('hamfy-admin-nonce-sec')
            ]
        );
        wp_enqueue_style('hamfy_admin_main_css' ,
            HWP_TICKET_ADMIN_ASSETS.'hamfy.css' ,
            ['select2'] ,
            HWP_TICKET_SCRIPTS_VERSION
        );
    }


    public static function frontScripts()
    {
        if ( get_page_template_slug( ) == 'ticket-template.php' ){
            global $GLOBAL_TICKET_IS_SUP;
            global $QUERY_STRING_TICKET;

            $current_userID = get_current_user_id();
            $require_js     = ['jquery'];
            $require_css    = ['dashicons' ,'hwp_prism_css' ,'hwp_prism_css'];

            if ( $GLOBAL_TICKET_IS_SUP ){
                wp_enqueue_script(
                    'hwp_ticket_select2_js' ,
                    HWP_TICKET_PUBLIC_ASSETS.'js/select2.full.min.js' ,
                    ['jquery'] ,
                    '4.0.13'
                );
                wp_enqueue_style(
                    'hwp_ticket_select2_css' ,
                    HWP_TICKET_PUBLIC_ASSETS.'css/select2.min.css' ,
                    false ,
                    '4.0.13'
                );
                $require_js [] = 'hwp_ticket_select2_js';
                $require_css[] = 'hwp_ticket_select2_css';
            }


            wp_enqueue_script(
                'hwp_ticket_js' ,
                HWP_TICKET_PUBLIC_ASSETS.'final.min.js' ,
                $require_js  ,
                HWP_TICKET_SCRIPTS_VERSION,
                true
            );

            wp_localize_script(
                'hwp_ticket_js' ,
                'hamfy_object' ,
                [
                    'admin_url'         => admin_url( 'admin-ajax.php' ) ,
                    'home_url'          => home_url(),
                    'params'            => $QUERY_STRING_TICKET ,
                    'captcha_pub'       => (function_exists('hamyar_feature_recaptcha_site_key') ) ? hamyar_feature_recaptcha_site_key() : '' ,
                    'hamfy'             => wp_create_nonce('hamfy_public_security') ,
                    'ticket_create'     => Course::get_instance()::getFormListCourse( $current_userID ) ,
                    'root'              => esc_url_raw( rest_url() ) ,
                    'user_token'        => Functions::encryptID( $current_userID ) ,
                    'load_sup_filter'   => $GLOBAL_TICKET_IS_SUP ? 'load' : 'do-not-load' ,
                    'user_default_sort' => Permissions::userDefaultSort( $GLOBAL_TICKET_IS_SUP ),
                    'template_btn'      => PartialUI::templateIcon()
                ]
            );

            wp_enqueue_style(
                'hwp_izi_toast_css' ,
                HWP_TICKET_PUBLIC_ASSETS.'css/izi-toast.min.css' ,
                false,
                '1.24.1'
            );

            wp_enqueue_style(
                'hwp_prism_css' ,
                HWP_TICKET_PUBLIC_ASSETS.'css/prism.css' ,
                false,
                '1.24.1'
            );

            wp_enqueue_style(
                'hwp_ticket_css' ,
                HWP_TICKET_PUBLIC_ASSETS.'style.min.css' ,
                $require_css,
                HWP_TICKET_SCRIPTS_VERSION
            );
        }
    }



    public static function templateScripts()
    {
        wp_enqueue_style(
            'hamfy_style_template' ,
            HWP_TICKET_PUBLIC_ASSETS.'template/template.css' ,
            null ,
            HWP_TICKET_SCRIPTS_VERSION
        );
        wp_enqueue_script(
            'jquery_sortable_lists' ,
            HWP_TICKET_PUBLIC_ASSETS.'template/jquery-sortable-lists.js'
        );
        wp_enqueue_script(
            'hamfy_script_template' ,
            HWP_TICKET_PUBLIC_ASSETS.'template/template.js' ,
            ['jquery'   ,'jquery_sortable_lists' ] ,
            HWP_TICKET_SCRIPTS_VERSION
        );
        wp_localize_script(
            'hamfy_script_template' ,
            'hamfy_object' ,
            [
                'admin_url'      => admin_url( 'admin-ajax.php' ) ,
                'template_nonce' => wp_create_nonce('hamfy-admin-nonce-sec')
            ]
        );
    }


    public static function analyticsScripts(){
        wp_enqueue_style(
            'bootstrap'
        );
        wp_enqueue_style(
            'hamfy_style_dashboard' ,
            HWP_TICKET_PUBLIC_ASSETS.'dashboard/dashboard.css' ,
            false,
            HWP_TICKET_SCRIPTS_VERSION
        );

        wp_enqueue_script(
            'hamyar_highcharts' ,
            HWP_TICKET_PUBLIC_ASSETS.'dashboard/highcharts.js'
        );

        wp_enqueue_script(
            'hamfy_exporting' ,
            HWP_TICKET_PUBLIC_ASSETS.'dashboard/exporting.js'
        );

        wp_enqueue_script(
            'hamfy_export_data' ,
            HWP_TICKET_PUBLIC_ASSETS.'dashboard/export-data.js'
        );

        wp_enqueue_script(
            'hamfy_accessibility' ,
            HWP_TICKET_PUBLIC_ASSETS.'dashboard/accessibility.js'
        );

        wp_enqueue_script(
            'hamfy_variable_pie' ,
            HWP_TICKET_PUBLIC_ASSETS.'dashboard/variable-pie.js'
        );

        wp_enqueue_script(
            'hamfy_script_dashboard' ,
            HWP_TICKET_PUBLIC_ASSETS.'dashboard/dashboard.js' ,
            [ 'jquery' , 'hamyar_highcharts' ,'hamfy_accessibility' ,'hamfy_export_data' ,'hamfy_exporting' ,'hamfy_variable_pie' ] ,
            HWP_TICKET_SCRIPTS_VERSION ,
            true
        );
        wp_localize_script(
            'hamfy_script_dashboard' ,
            'hamfy_object' ,
            [
                'admin_url'   => admin_url( 'admin-ajax.php' ) ,
                'hamfy'       => wp_create_nonce('hamfy_public_security') ,
                'destination_monthly' => AnalyticsData::get_instance()::destinationMonthly(),
                'response_average_m'  => AnalyticsData::get_instance()::responseAverageMonthly(),
                'reply_count_monthly' => AnalyticsData::get_instance()::replyMonthlySum(),
                'destination_percent' => AnalyticsData::get_instance()::destinationPercent(),
                'courses_statistics'  => AnalyticsData::get_instance()::courseStatistic()
            ]
        );
    }


    public static function masterScripts(){
        wp_enqueue_script(
            'hamfy_script_master_new' ,
            HWP_TICKET_PUBLIC_ASSETS.'master-new/master-new.js' ,
            ['jquery'  ,'hamfy_dropzone' ,'hamfy_recaptcha' ],
            HWP_TICKET_SCRIPTS_VERSION,
            true
        );

        wp_localize_script(
            'hamfy_script_master_new' ,
            'hamfy_object' ,
            [
                'admin_url'   => admin_url( 'admin-ajax.php' ) ,
                'hamfy'       => wp_create_nonce('hamfy_public_security') ,
                'root'        => esc_url_raw( rest_url() ) ,
                'user_token'  => Functions::encryptID( get_current_user_id() ) ,
                'captcha_pub' => (function_exists('hamyar_feature_recaptcha_site_key'))? hamyar_feature_recaptcha_site_key():''  ,

            ]
        );
    }






}
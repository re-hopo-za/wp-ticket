<?php


namespace HWP_Ticket\core;




use HWP_Ticket\core\cron\ChangeStatus;
use HWP_Ticket\core\cron\CronTask;
use HWP_Ticket\core\cron\SupportRemainder;
use HWP_Ticket\core\includes\Course;
use HWP_Ticket\core\includes\Permissions;
use HWP_Ticket\core\includes\Uploader;
use HWP_Ticket\core\includes\Users;
use HWP_Ticket\core\requests\Ajax;
use HWP_Ticket\core\requests\Rest;
use HWP_Ticket\core\ui\DashboardUI;

class Loader
{

    protected static $_instance = null;
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct(){
        self::loader();
    }

    public static function loader()
    {

        add_action('hamfy_daily_task' ,[ChangeStatus::get_instance(),'runCron'] );
        add_action('hamfy_daily_task' ,[SupportRemainder::get_instance(),'runCron'] );

        Rest::get_instance();
        CronTask::get_instance();
        Uploader::get_instance();
        DashboardUI::get_instance();
        Registration::get_instance();
        Users::get_instance();


        add_action('init' ,function (){
            $user_id = get_current_user_id();
            $GLOBALS['GLOBAL_TICKET_WHO_IS']     = Permissions::whoIs( $user_id );
            $GLOBALS['GLOBAL_TICKET_IS_SUP']     = Permissions::isSupporter();
            $GLOBALS['GLOBAL_TICKET_PERMISSION'] = Permissions::getPermissionsList( $user_id );
            $GLOBALS['GLOBAL_TICKET_USER_TRUST'] = Users::getUserTrustMeta( $user_id );

            Endpoint::get_instance();
            Enqueues::get_instance();
            Ajax::get_instance();
            Permissions::get_instance();
            Course::get_instance();
        });
    }











}

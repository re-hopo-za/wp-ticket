<?php


namespace HWP_Ticket\core\requests;


use HWP_Ticket\core\includes\Course;
use HWP_Ticket\core\includes\Permissions;
use HWP_Ticket\core\includes\Users;
use HWP_Ticket\core\ui\DashboardUI;
use HWP_Ticket\core\ui\TextTemplateUI;

class Ajax
{

    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct()
    {

        //// Text templates
        add_action('wp_ajax_hamfy_add_text_template'    , [ $this , 'templateAdd' ]);
        add_action('wp_ajax_hamfy_remove_text_template' , [ $this , 'templateDelete' ]);
        add_action('wp_ajax_hamfy_sort_text_template'   , [ $this , 'templateSortPrivate' ]);


        /// Permission
        add_action('wp_ajax_hamfy_remove_all_permission'   , [$this , 'removeAllPermission']);
        add_action('wp_ajax_hamfy_update_user_permission'  , [$this , 'updateUserPermission']);



        add_action('wp_ajax_hamfy_search_products'   , [ $this , 'searchProductsList' ]);
        add_action('wp_ajax_hamfy_user_search_admin' , [$this, 'searchUserAdmin']);
        add_action('wp_ajax_hamfy_user_search_public', [$this, 'searchUserPublic']);

    }

    //// front page ajax action
    public function searchUserAdmin()
    {
        if ( Permissions::isSupporter() ){
            Permissions::nonceCheckerBackend();
            Users::searchUserAdmin();
        }
    }


    public function searchUserPublic()
    {
        if ( Permissions::isSupporter() ){
            Permissions::nonceCheckerFrontend();
            Users::searchUserPublic();
        }
    }

    public function searchProductsList()
    {
        if ( Permissions::isSupporter() ){
            if ( isset( $_POST['call'] ) && $_POST['call'] == 'admin' ){
                Permissions::nonceCheckerBackend();
            }else{
                Permissions::nonceCheckerFrontend();
            }
            Course::searchProductsList();
        }
    }



    ///text Template action
    public function templateAdd()
    {
        Permissions::nonceCheckerBackend();
        TextTemplateUI::get_instance()::add();
    }

    public function templateDelete()
    {
        Permissions::nonceCheckerBackend();
        TextTemplateUI::get_instance()::delete();
    }

    public function templateSortPrivate()
    {
        Permissions::nonceCheckerBackend();
        TextTemplateUI::get_instance()::sortPrivate();
    }




    ///Permission action
    public function removeAllPermission()
    {
        Permissions::nonceCheckerBackend();
        Permissions::removeAllPermission();
    }

    public function updateUserPermission()
    {
        Permissions::nonceCheckerBackend();
        Permissions::updateUserPermission();
    }






}
<?php
/*
 * Template Name: Ticket Template
 * Description: A Page Template For ticket Plugin.
 */


use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\includes\Permissions;
use HWP_Ticket\core\requests\Http;

Permissions::loginChecker();
Functions::getQueryString();

get_header();
     Http::get_instance()::httpRequest(
        Functions::clearHttpToTicketRequest( $_SERVER['REQUEST_URI'] )
    );
get_footer();



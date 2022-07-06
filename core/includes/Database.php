<?php

namespace HWP_Ticket\core\includes;



class Database
{

    public static $charset;

    public static $notification_job;
    public static $tickets;
    public static $prefix;
    public static $userID;

    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct() {
        global $wpdb;
        self::$prefix = $wpdb->prefix.'tango_';
        self::$notification_job = self::$prefix.'notification_job';
        self::$tickets          = self::$prefix.'tickets';
        self::$charset          = $wpdb->get_charset_collate();
    }


    public function create_need_database(){

        $create_query   = [];
        $create_query[] = $this->createTableTickets();
        $create_query[] = $this->createTableLogs();
        $create_query[] = $this->createTableFileUpload();

        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        foreach ( $create_query as $query ) {
                 dbDelta( $query );
        }
    }



    protected function createTableTickets(){

        $charset_collate = self::$charset;
        $table = self::$tickets;
        return "
		CREATE TABLE $table (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `title` varchar(500) default NULL,
		  `content` text NOT NULL,
		  `send_method` varchar(100) default 'ticket',
		  `creator` bigint(20) unsigned NOT NULL,
		  `destination` varchar(100),
		  `parent_ticket` bigint(20) unsigned default NULL,
		  `main_object` varchar(250) default NULL,
		  `reply_to` bigint(20) unsigned default NULL,
		  `assign_to` bigint(20) unsigned default NULL,
		  `do_action` varchar(500) default NULL,
		  `order_num` int(2) unsigned default 0,
		  `tags` LONGTEXT default NULL,
		  `rate` smallint(1) default NULL,
		  `rate_comment` varchar(500) default NULL,
		  `status` varchar(500) default NULL,
		  `is_public` smallint(1) default 0,
		  `created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
		  `updated_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
		  PRIMARY KEY  (id),
		  KEY  `tickets_key` (`creator`,`reply_to`,`main_object`,`parent_ticket`)
		  ) {$charset_collate};";
    }


    protected function createTableLogs(){

        $charset_collate = self::$charset;
        $table = self::$table_logs;
        return " CREATE TABLE $table (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `ticket_id` bigint(20) unsigned,
		  `log` text NOT NULL,
		  `created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
		  PRIMARY KEY  (id),
		  KEY  `tickets_key` (`ticket_id`)
		  ) {$charset_collate};";
    }


    protected function createTableFileUpload(){

        $charset_collate = self::$charset;
        $table = self::$file_upload;
        return " CREATE TABLE $table (
		  `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `ticket_id` bigint(20) unsigned,
          `online_path` varchar(1000) COMMENT 'minio upload file path',
          `etag` varchar(200) COMMENT 'minio upload file etag',
          `user_id` bigint(20) unsigned,
          `type` varchar(200) NOT NULL,
          `created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
		  PRIMARY KEY  (id),
		  KEY  `file_upload` (`ticket_id`,`etag`)
		  ) {$charset_collate};";
    }


    public static function ticketCount( $userID ,$params )
    {
        global $wpdb;
        return count( $wpdb->get_results( self::loopQuery( $userID ,$params ,true ) ) );
    }


    public static function loopQuery( $userID ,$params ,$COUNT = false )
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_IS_SUP;
        global $GLOBAL_TICKET_PERMISSION;
        $table   = self::$tickets;
        $query   = "SELECT * FROM {$table} WHERE";

        $page           = self::getFilterArgs( $params ,'_page' );
        $sort           = self::getFilterArgs( $params ,'sort' );
        $limit          = self::getFilterArgs( $params ,'limit' );
        $search         = self::getFilterArgs( $params ,'search' );
        $status         = self::getFilterArgs( $params ,'status' );
        $destination    = self::getFilterArgs( $params ,'destination' );
        $course         = self::getFilterArgs( $params ,'course' );
        $the_user       = self::getFilterArgs( $params ,'the_user' );
        $last_response  = self::getFilterArgs( $params ,'last_response' );
        $n_reply_ticket = self::getFilterArgs( $params ,'n_reply_tickets' );
        $unseen_ticket  = self::getFilterArgs( $params ,'unseen_tickets' );
        $query         .= self::extraFilter( $userID ,$last_response ,$n_reply_ticket ,$unseen_ticket );


        if ( 'admin' == $GLOBAL_TICKET_WHO_IS  ){
            if ( !empty( $destination ) ){
                $query .= " AND destination = '{$destination}'";
            }
            if ( !empty( $course ) ){
                $query .= " AND main_object = '{$course}'";
            }
        }


        if ( $GLOBAL_TICKET_IS_SUP && 'admin' != $GLOBAL_TICKET_WHO_IS ){
            if ( !empty( $destination ) && !empty( $course ) ){
                if ( Permissions::get_instance()::getUserCoursesList( $GLOBAL_TICKET_PERMISSION ,$course ) ){
                    $has_dest  = Permissions::getSpecificCourseDestinations( $GLOBAL_TICKET_PERMISSION ,$course );
                    if ( in_array( $has_dest ,$destination ) ){
                        $query .= " main_object = {$course} AND destination ='{$destination}' ";
                    }
                }

            }elseif ( empty( $destination ) && empty( $course ) ) {
                $query .= " AND (";
                $i      = 0;
                foreach ( $GLOBAL_TICKET_PERMISSION as $key => $val  ){
                    $query .= $i >= 1 ? ' OR' : '';
                    $_destination = Functions::implodeForDestinationsListQuery( $val );
                    $_destination = empty( $_destination ) ? 'per_err' : $_destination;
                    $query .= " ( main_object = '{$key}' AND destination IN {$_destination} )";
                    $i++;
                }
                $query .= " )";

            }else{
                if ( !empty( $destination ) ){
                    $courses = Functions::implodeForQuery( Permissions::getSpecificCoursesListByDestination( $GLOBAL_TICKET_PERMISSION ,$destination ) );
                    $query .= " AND main_object {$courses} AND destination = '{$destination}' ";
                }
                if ( !empty( $course ) ){
                    if ( Permissions::getUserCoursesList( $GLOBAL_TICKET_PERMISSION ,$course ) ){
                        $dest   = Functions::implodeForQuery( Permissions::getSpecificCourseDestinations( $GLOBAL_TICKET_PERMISSION ,$course ) );
                        $query .= " AND main_object = '{$course}' AND destination {$dest} ";
                    }

                }
            }
        }

        if ( !$GLOBAL_TICKET_IS_SUP ){
            if ( !empty( $destination ) ){
                $query .= " AND destination = '{$destination}'";
            }
        }

        if ( !empty( $status ) ){
            $query .= " AND status ='{$status}' ";
        }

        if ( !empty( $the_user ) && $GLOBAL_TICKET_IS_SUP ){
            $query .= " AND creator = {$the_user} ";
        }else if( !$GLOBAL_TICKET_IS_SUP ){
            $query .= " AND ( creator = {$userID} OR assign_to={$userID} )";
        }else{
            $query .= " OR ( parent_ticket IS NULL AND ( creator = {$userID} OR assign_to={$userID} ) )";
        }

        if ( !empty( $search ) ){
            $query .= " AND ( title LIKE '%{$search}%' OR content LIKE '%{$search}%' ) ";
        }

        if ( !empty( $sort ) ){
            $query .= " ORDER BY ".str_replace('|' , ' ' , $sort );
        }else{
            $query .= self::getUserOrderDefault( $GLOBAL_TICKET_WHO_IS );
        }

        if ( false === $COUNT ){
            if( !empty( $page )){
                $query .= " LIMIT {$limit} OFFSET ". $page * $limit ;
            }else{
                if( !empty( $limit )){
                    $query .= " LIMIT {$limit} ;";
                }else{
                    $query .= " LIMIT 15 ;";
                }
            }
        }
        return $query;
    }


    public static function getChildesTicket( $parentID )
    {
        if ( !empty( $parentID ) && is_numeric( $parentID ) ){
            global $wpdb;
            $table   = self::$tickets;
            $results = $wpdb->get_results(
                $wpdb->prepare( "SELECT * FROM {$table} WHERE parent_ticket = %d AND ( status IS NULL OR status <> 'deleted');" , $parentID )
            );
            if ( !is_wp_error( $results ) && !empty( $results ) ){
                return $results;
            }
        }
        return [];
    }


    public static function single( $id ,$child = false )
    {
        if ( !empty( $id ) && is_numeric( $id ) ){
            global $wpdb;
            $table   = self::$tickets;
            $ch_whe  = $child ? 'IS NOT' : 'IS';
            $results = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM {$table} WHERE id= %d AND parent_ticket {$ch_whe} NULL LIMIT 1 ;" , $id )
            );
            if ( !is_wp_error( $results ) && !empty( $results ) ){
                return $results[0];
            }
        }
        return [];
    }


    public static function getAllTickets( $userID ,$params )
    {
        global $wpdb;
        $items = $wpdb->get_results(
             self::loopQuery( $userID ,$params )
        );
        if ( !is_wp_error( $items ) && !empty( $items ) ){
            return $items;
        }
        return [];
    }


    public static function getFilterArgs( $params ,$index )
    {
        if ( !empty( $params ) ){
            if ( is_object( $params ) ){
                if ( isset( $params->$index ) ){
                    return $params->$index;
                }
            }
            if ( is_array( $params ) ){
                if ( isset( $params[$index] ) ){
                    return $params[$index];
                }
            }
        }
        return '';
    }


    public static function getUserOrderDefault( $who_is )
    {
        if ( 'master' == $who_is ) {
            return ' ORDER BY FIELD(order_num,3,2,1,0), FIELD(status,"open","first","in_progress","answered","closed","finished") , updated_date ';
        } else {
            return ' ORDER BY FIELD(status,"open","first","in_progress","answered","closed","finished")  , updated_date ';
        }
    }


    public static function extraFilter( $userID , $last ,$n_reply ,$unseen )
    {
        $query = '';
        if ( $last == 1 ) {
            $last_response = self::lastResponse( $userID );
            $last_re_query = Functions::implodeForQuery( $last_response );
            $query .= " id {$last_re_query}";
        }elseif ( $n_reply == 1 ) {
            $without_response = self::withoutResponse();
            $without_re_query = Functions::implodeForQuery( $without_response );
            $query .= " id {$without_re_query}";

        }elseif ( $unseen == 1  ) {
            $unseen_tickets = self::unseenTickets();
            $unseen_t_query = Functions::implodeForQuery( $unseen_tickets );
            $query .= " id {$unseen_t_query}";
        }else {
            $query .= " parent_ticket IS NULL";
        }
        return $query;
    }


    public static function lastResponse( $userID ){
        global $wpdb;
        $table   = self::$tickets;
        $id_list = [];
        $IDs = $wpdb->get_results(
            $wpdb->prepare("SELECT parent_ticket FROM {$table} WHERE creator = %d AND parent_ticket IS NOT NULL ",$userID )
        );
        $IDs['fixed'] = 'fixed';
        foreach ( $IDs as $id ){
            if ( isset( $id->parent_ticket ) ){
                $id_list[] = $id->parent_ticket;
            }
        }
        return $id_list;
    }


    public static function withoutResponse(){
        global $wpdb;
        $without_response = [];
        $table   = self::$tickets;
        $IDs     = $wpdb->get_results("SELECT id FROM {$table} WHERE parent_ticket IS NULL AND tags IS NOT NULL AND status='first' ;");
        $IDs['fixed'] = 'fixed';
        foreach ( $IDs as $id ){
            if ( isset( $id->id ) ){
                $without_response[]= $id->id;
            }
        }
        return $without_response;
    }


    public static function unseenTickets(){
        global $wpdb;
        $unseen_tickets = [];
        $table   = self::$tickets;
        $IDs = $wpdb->get_results("SELECT id FROM {$table} WHERE parent_ticket IS NULL AND status='first' AND ( tags NOT LIKE '%supporter_seen_list%' OR tags IS NULL );");
        $IDs['fixed'] = 'fixed';
        foreach ( $IDs as $id ){
            if ( isset( $id->id ) ){
                $unseen_tickets[] = $id->id;
            }
        }
        return $unseen_tickets;
    }


    public static function getTicketChilds( $IDs )
    {
        global $wpdb;
        $table   = self::$tickets;
        $IDs = '"'. implode("','" , $IDs ).'"';
        $result = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table WHERE parent_ticket IN (%s); " ,$IDs )
        );
        if ( !is_wp_error( $result ) && !empty( $result ) ){
            return $result;
        }
        return [];
    }


    public static function updateUsersSeenTickets( $userID ,$ticketOb ,$read = true )
    {
        $creatorID = Functions::indexChecker( $ticketOb , 'creator' ,0 );
        if ( $creatorID != 0 ){
            global $wpdb;
            $table = self::$tickets;
            $logs  = [];
            $tags  = unserialize( $ticketOb->tags );
            $tags  = !empty( $tags ) ? $tags : [];
            if ( $creatorID == $userID ){
                if ( $ticketOb->status != 'first' &&( !isset( $tags['owner_seen_reply'] ) || empty( $tags['owner_seen_reply'] )) ){
                    $logs = Functions::logHandler( $userID ,Functions::indexChecker( $ticketOb , 'tags' ,[] ) ,'owner_seen_reply' ,strtotime('now') );
                }
            }elseif( $read ){
                $logs = Functions::logHandler( $userID ,Functions::indexChecker( $ticketOb , 'tags' ,[] ) ,'supporter_seen_list' ,strtotime('now') );
            }else{
                $logs = Functions::logHandler( $userID ,Functions::indexChecker( $ticketOb , 'tags' ,[] ) ,'owner_seen_reply' ,'' ,'delete' );
            }
            if ( !empty( $logs ) ){
                $wpdb->update(
                    $table ,
                    [ 'tags' => $logs ] ,
                    [ 'id' => $ticketOb->id ] ,
                    [ '%s' ] ,[ '%d' ]
                );
                return $wpdb->last_error;
            }
        }
        return 'Not Effected';
    }



    public static function updateParent( int $parentID ,$status )
    {
        global $wpdb;
        $table  = self::$tickets;
        $parent = self::single( $parentID );
        if ( !empty( $parent ) ){
            $data   = [ 'status' => $status ,'updated_date' => date('Y-m-d H:i:s') ,'order_num' => 2 ];
            $format = [ '%s' ,'%s' ,'%d'  ];
            $where  = ['id' => $parentID ];
            $where_format =['%d'];
            $wpdb->update( $table ,$data  ,$where  ,$format ,$where_format );
            return $parent;
        }
        return [];
    }


    public static function changeDestination( $ticketOb ,$newDest ,$userID ,$permissions )
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $wpdb;
        $table  = self::$tickets;
        $pesrs  = Permissions::isAccessToSpecificTicket( $ticketOb->main_object ,$ticketOb->destination ,$permissions );
        if( ( $pesrs || 'admin' == $GLOBAL_TICKET_WHO_IS ) && !empty( $newDest ) ){
            if ( array_key_exists( $newDest , Destination::getDestinationsList() ) ){
                $logs = Functions::logHandler( $userID ,$ticketOb->tags ,'change_destination_list' ,[$ticketOb->destination => $newDest] );
                $wpdb->update(
                    $table,
                    [ 'destination' => $newDest ,'tags' => $logs ] ,
                    [ 'id' => $ticketOb->id ] ,
                    [ '%s' ,'%s' ] ,
                    [ '%d']
                );
                return $wpdb->last_error;
            }
        }
        return 'Not Effected';
    }


    public static function assignToAnotherSupporter( $ticketOb ,$assignTO ,$userID ,$permissions )
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $wpdb;
        $table = self::$tickets;
        $pesrs = Permissions::isAccessToSpecificTicket( $ticketOb->main_object ,$ticketOb->destination ,$permissions );
        if( ( $pesrs || 'admin' == $GLOBAL_TICKET_WHO_IS ) ){
            $logs = Functions::logHandler( $userID ,$ticketOb->tags ,'assign_list' ,$assignTO );
            $wpdb->update(
                $table,
                [ 'tags' => $logs ,'assign_to' => $assignTO ,'order_num' => 3 ] ,
                [ 'id' => $ticketOb->id ] ,
                [ '%s' ,'%d' ,'%d' ] ,
                [ '%d']
            );
            return $wpdb->last_error;
        }
        return 'Not Effected';
    }


    public static function clearSeenList( $userID ,$ticketOb )
    {
        global $wpdb;
        $table = self::$tickets;
        $logs  = Functions::logHandler( $userID ,$ticketOb->tags ,'supporter_seen_list' ,null ,'delete' );
        $wpdb->update(
            $table,
            [ 'tags' => $logs  ] ,
            [ 'id' => $ticketOb->id ] ,
            [ '%s' ] ,
            [ '%d']
        );
        return $wpdb->last_error;
    }


    public static function updateStatusWithoutContent( $userID ,$ticketOb ,$permissions )
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $wpdb;
        $table = self::$tickets;
        $pesrs = Permissions::isAccessToSpecificTicket( $ticketOb->main_object ,$ticketOb->destination ,$permissions );
        if( ( $pesrs || 'admin' == $GLOBAL_TICKET_WHO_IS ) ){
            $logs  = Functions::logHandler( $userID ,$ticketOb->tags ,'update_status_list' ,[ $ticketOb->status => 'closed'] );
            $wpdb->update(
                $table,
                [ 'tags' => $logs ,'status' => 'closed' ,'updated_date' => date('Y-m-d H:i:s') ] ,
                [ 'id' => $ticketOb->id ] ,
                [ '%s' ,'%s' ,'%s' ] ,
                [ '%d']
            );

            return $wpdb->last_error;
        }
        return 'Not Effected';
    }


    public static function ticketSoftDelete( $userID ,$ticketOb )
    {
        global $wpdb;
        $table  = self::$tickets;
        if ( !empty( $ticketOb ) ){
            $logs = unserialize( $ticketOb->tags );
            $logs = Functions::logHandler( $userID, $logs, 'update_status_list', [$ticketOb->status => 'deleted']);
            $wpdb->update(
                $table,
                ['status' => 'deleted', 'tags' => $logs ],
                ['id' => $ticketOb->id],
                ['%s', '%s'],
                ['%d']
            );
            return $wpdb->last_error;
        }
        return 'Not Effected';
    }


    public static function getUsersByIDs( $IDs )
    {
        global $wpdb;
        $IDs = Functions::implodeForQuery( array_unique( $IDs ) );
        $mobile_key = function_exists('hf_user_mobile_meta_key' ) ? \hf_user_mobile_meta_key() : '';
        $result = $wpdb->get_results( "
                    SELECT users.*, user_meta.meta_value AS mobile FROM {$wpdb->users} AS users
                    LEFT JOIN {$wpdb->usermeta} AS user_meta ON users.ID = user_meta.user_id AND user_meta.meta_key = '{$mobile_key}'  
                    WHERE users.ID {$IDs};"
        );
        if ( empty( $wpdb->last_error ) && !empty( $result ) ){
            return $result;
        }
        return [];
    }


    public static function addRate( $userID ,$ticketObject ,$parentObject ,$params )
    {
        if ( empty( $ticketObject->rate ) && $parentObject->creator == $userID && $ticketObject->main_object != 'comment' ){
            $rate = Functions::indexChecker( $params ,'rate' ,5 );
            global $wpdb;
            $table  = self::$tickets;
            if ( is_numeric( $rate ) ){
                $wpdb->update(
                    $table,
                    ['rate' => $rate ],
                    ['id' => $ticketObject->id ],
                    ['%d'],
                    ['%d']
                );
                if ( !is_wp_error( $wpdb )  ){
                    self::updateParentRate( $parentObject );
                    return '';
                }
            }
        } 
        return 'Not Effected';
    }


    public static function updateParentRate( $parentObject )
    {
        $children = self::getSpecificTicketChildren( $parentObject->id );
        $items    = Functions::getAverageRate( $children );
        $average  = array_sum( $items ) / count( $items );
        if ( is_numeric( $average ) ){
            global $wpdb;
            $wpdb->update(
                self::$tickets ,
                ['rate' => $average ],
                ['id' => $parentObject->id ],
                ['%d'],
                ['%d']
            );
            if ( !is_wp_error( $wpdb )  ){
                return '';
            }
        }
        return false;
    }


    public static function getSpecificTicketChildren( $parentID )
    {
        global $wpdb;
        $table  = self::$tickets;
        $result = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table WHERE parent_ticket = %d ; " ,$parentID )
        );
        if ( !is_wp_error( $result ) && !empty( $result ) ){
            return $result;
        }
        return [];
    }


    public static function saveTicket( $data ,$format )
    {
        global $wpdb;
        $table = self::$tickets;
        $wpdb->insert( $table ,$data ,$format );
        if( !is_wp_error( $wpdb ) && is_numeric( $wpdb->insert_id ) ){
            return $wpdb->insert_id;
        }
        return false;
    }






}

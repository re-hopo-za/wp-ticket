<?php

namespace HWP_Ticket\core\ui;



use HWP_Ticket\core\includes\Course;
use HWP_Ticket\core\includes\Database;
use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\includes\Permissions;
use HWP_Ticket\core\requests\Http;



class TicketUI{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public static function ticketRoot( $userID ,$params ,$http_call = true )
    {
        $items  = Database::get_instance()::getAllTickets( $userID ,$params );
        $icons  = str_replace( '[icons-list]' ,PartialUI::get_instance()::iconsList() ,PartialUI::iconsSection() );
        $icons  = str_replace( '[new-ticket-url]' ,home_url()  ,$icons );
        $ticket = str_replace( '[icons-list]' ,$icons ,PartialUI::ticketRoot() );
        $filter = str_replace( '[destination-options]' ,PartialUI::get_instance()::destinationOptions() ,PartialUI::filterSection() );
        $filter = str_replace( '[status-options]' ,PartialUI::get_instance()::statusOptions() ,$filter );
        $filter = str_replace( '[admin-filter]' ,PartialUI::get_instance()::adminFilter() ,$filter );
        $filter = str_replace( '[sort-filter]' ,PartialUI::get_instance()::sortFilter() ,$filter );
        $ticket = str_replace( '[filter-section]' ,$filter ,$ticket );
        $ticket = str_replace( '[pagination]' ,self::pagination( $userID ,$params ) ,$ticket );

        if( !empty( $items ) ){
            $ticket = str_replace( '[loop]' ,self::allLoop( $items ) ,$ticket );
        }else{
            $ticket = str_replace( '[loop]' ,PartialUI::emptyList()  ,$ticket );
        }
        Functions::returnAppropriateData( $http_call ,$ticket );
    }


    public static function allLoop( $items )
    {
        $loop    = '';
        if ( !empty( $items ) ){
            $creator_list   = array_column( (array) $items ,'creator' );
            $users_list     = Functions::preparationUsersList( $creator_list );
            $rating_enable  = Functions::getTicketOptions( 'hwp_ticket_rating_enable_status' ,0 );
            foreach ( $items as $item ){
                $ui    = str_replace( '[ticket-id]'     , $item->id ,PartialUI::ticketLoop() );
                $ui    = str_replace( '[ticket-url]'    , Http::ticketUrl( $item->id ) ,$ui );
                $ui    = str_replace( '[nicename]'      , Functions::getUserFromBulk( $users_list ,$item->creator )['display_name'] ,$ui );
                $ui    = str_replace( '[create-date]'   , Functions::returnTime( $item->created_date )  ,$ui );
                $ui    = str_replace( '[destination]'   , Functions::destinationTranslate( $item->destination ) ,$ui );
                $ui    = str_replace( '[status-class]'  , $item->status ,$ui );
                $ui    = str_replace( '[main-object]'   , Functions::getCourseName( $item->main_object ) ,$ui );
                $ui    = str_replace( '[ticket-title]'  , $item->title ,$ui );
                $ui    = str_replace( '[status]'        , Functions::statusesTranslate( $item->status ) ,$ui );
                $ui    = str_replace( '[preview]'       , self::preview( $item->id ) ,$ui );
                $ui    = str_replace( '[seen-list]'     , self::ticketSeenList( $item ) ,$ui );
                $ui    = str_replace( '[creator-seen]'  , self::creatorSeenReply( $item ) ,$ui );
                $ui    = str_replace( '[forwards]'      , self::forwardLog( $item ,$users_list ) ,$ui );
                $ui    = str_replace( '[assigns]'       , self::assignedList( $item ) ,$ui );
                $ui    = str_replace( '[elapsed-date]'  , human_time_diff( time() , strtotime( $item->updated_date ) ) ,$ui );
                $ui    = str_replace( '[profile-image]' , Functions::get_avatar( $item->creator , 50 , true ) ,$ui );
                $ui    = str_replace( '[excerpt]'       , Functions::contentExcerpt( $item->content ,50 ) ,$ui );
                $loop .= str_replace( '[rating]'        , PartialUI::ratingReadOnly( $rating_enable ,$item->rate )  ,$ui);
            }
        }else{
            $loop = PartialUI::emptyList();
        }
        return $loop;
    }


    public static function single( $userID ,object $_db ,$http_call = true )
    {
        global $GLOBAL_TICKET_IS_SUP;
        $user = Functions::getUser( $_db->creator );
        $ui  = str_replace( '[ticket-id]'       , $_db->id ,PartialUI::ticketSingle() );
        $ui  = str_replace( '[avatar]'          , Functions::get_avatar( $_db->creator ,80 ,true ) ,$ui );
        $ui  = str_replace( '[is-student]'      , ( $GLOBAL_TICKET_IS_SUP ? 'not' : 'is') ,$ui );
        $ui  = str_replace( '[ticket-home]'     , Http::ticketUrl() ,$ui );
        $ui  = str_replace( '[first-name]'      , $user->first_name ,$ui );
        $ui  = str_replace( '[last-name]'       , $user->last_name  ,$ui );
        $ui  = str_replace( '[nicename]'        , $user->display_name ,$ui );
        $ui  = str_replace( '[phone]'           , PartialUI::phoneReplicer( $user->ID ) ,$ui );
        $ui  = str_replace( '[destination]'     , Functions::destinationTranslate( $_db->destination ) ,$ui );
        $ui  = str_replace( '[status]'          , Functions::statusesTranslate( $_db->status ) ,$ui );
        $ui  = str_replace( '[time]'            , Functions::returnTime( $_db->created_date ) ,$ui );
        $ui  = str_replace( '[all-user-ticket]' , PartialUI::allUserTickets( $user ) ,$ui );
        $ui  = str_replace( '[all-user-orders]' , self::allUserOrders( $_db->creator ) ,$ui );
        $ui  = str_replace( '[all-user-info]'   , PartialUI::allUserInfo( $_db->creator ) ,$ui );
        $ui  = str_replace( '[user-license]'    , PartialUI::userLicensePage( $_db->creator ) ,$ui );
        $ui  = str_replace( '[title]'           , $_db->title ,$ui );
        $ui  = str_replace( '[main-object]'     , Functions::getCourseName( $_db->main_object ) ,$ui );
        $ui  = str_replace( '[content]'         , $_db->content ,$ui );
        $ui  = str_replace( '[remain-support]'  , PartialUI::remainSupportDays( $_db->creator ,$_db->main_object ) ,$ui );
        $ui  = str_replace( '[ticket-owner]'    , Permissions::ticketOwner( $userID ,$_db->creator ) ,$ui );
        $ui  = str_replace( '[files-loop]'      , PartialUI::filesLoop( $_db->id )  ,$ui );
        $ui  = str_replace( '[reply-list]'      , self::childTickets( $userID ,$_db ,false,false ) ,$ui );
        $ui  = str_replace( '[text-template]'   , PartialUI::loadTextTemplate( $_db->status ,$_db->main_object ,$userID ) ,$ui );
        $ui  = str_replace( '[single-actions]'  , PartialUI::newReplyForm( $_db ,$userID ,$GLOBAL_TICKET_IS_SUP ) ,$ui );
        Functions::returnAppropriateData( $http_call ,$ui ,['ticket_id' => $_db->id ] );
    }


    public static function childTickets( $userID ,$parent ,$direct_call ,$http_call = true )
    {
        $children = Database::getChildesTicket( $parent->id );
        $child_ui = '';
        if ( !empty( $children ) ){
            global $GLOBAL_TICKET_IS_SUP;
            $rating_status = Functions::getTicketOptions( 'hwp_ticket_rating_enable_status' ,0 );
            foreach ( $children as $child ){
                if ( $child->main_object != 'comment' || $GLOBAL_TICKET_IS_SUP ){
                    $class  = $child->main_object == 'comment' ? 'comment' : Permissions::ticketOwner( $userID ,$child->creator );
                    $ui  = str_replace( '[class-name]'    ,$class ,PartialUI::ticketChildren() );
                    $ui  = str_replace( '[ticket-id]'     ,$child->id ,$ui );
                    $ui  = str_replace( '[time]'          ,Functions::returnTime( $child->created_date ) ,$ui );
                    $ui  = str_replace( '[creator-name]'  ,PartialUI::get_instance()::replyCreatorName( $child->creator ) ,$ui );
                    $ui  = str_replace( '[remove-reply]'  ,self::removeReply( $userID ,$child ,$parent ) ,$ui );
                    $ui  = str_replace( '[files-loop]'    ,PartialUI::get_instance()::filesLoop( $child->id ) ,$ui );
                    $ui  = str_replace( '[image-url]'     ,Functions::get_avatar( $child->creator , 50 ,true )  ,$ui );
                    $ui  = str_replace( '[rating]'        ,self::ratingStars( $rating_status ,$class ,$child ,$parent ,$userID ) ,$ui );
                    $child_ui .= str_replace( '[content]' ,PartialUI::prepareReplyContent( $child->content )  ,$ui );
                }
            }
        }
        if ( $direct_call ){
            Functions::returnAppropriateData( $http_call ,$child_ui );
        }
        return $child_ui;
    }


    public static function summarySection( $userID ,$ticketID )
    {
        $summary_db  = Database::get_instance()::single( $ticketID );
        $summary_ui  = PartialUI::summaryMainSection();
        $summary_ui  = str_replace( '[content]' ,$summary_db->content ,$summary_ui );
        $summary_ch_db = Database::getChildesTicket( $ticketID );
        $summary_ch    = '';
        if ( !empty( $summary_ch_db ) ){
            foreach ( $summary_ch_db as $children ){
                if (  $children->main_object == 'comment' ){ continue;  }
                $child  = str_replace( '[owner]' ,Permissions::ticketOwner( $userID ,$children->creator ) ,PartialUI::summaryItem() );
                $child  = str_replace( '[ticket-id]' ,$children->id ,$child );
                $child  = str_replace( '[time]' , Functions::returnTime( $children->created_date ) ,$child );
                $child  = str_replace( '[creator]' ,PartialUI::get_instance()::replyCreatorName( $children->creator ) ,$child );
                $child  = str_replace( '[avatar]' , Functions::get_avatar( $children->creator , 50 ,true )  ,$child );
                $child  = str_replace( '[content]' , nl2br(preg_replace('/[\n\r]{2,}/',"\n", $children->content ) )  ,$child );
                $summary_ch .= $child;
            }
        }else{
            $summary_ch = PartialUI::summaryNoItem();
        }
        Functions::returnAppropriateData( false ,$summary_ui.$summary_ch );
    }


    public static function newTicket( $userID ,$http_call = true )
    {
        $new_ticket = PartialUI::get_instance()::newTicket();
        $new_ticket = str_replace( '[title]' ,Functions::replaceInputNewTicket('title') ,$new_ticket );
        $new_ticket = str_replace( '[content]' ,Functions::replaceInputNewTicket('content') ,$new_ticket );
        $new_ticket = str_replace( '[ticket-home]' ,Http::ticketUrl() ,$new_ticket );
        $new_ticket = str_replace( '[course-list]' ,PartialUI::newTicketCourseList( $userID ) ,$new_ticket );
        Functions::returnAppropriateData( $http_call ,$new_ticket );
    }


    public static function pagination( $userID ,$params )
    {
        $total     = Database::ticketCount( $userID ,$params );
        $limit     = Functions::indexChecker( $params ,'limit' ,15 );
        $page      = Functions::indexChecker( $params , '_page' , 0 );
        $page_loop = '';
        $page_num  = 1;
        if ( $total > $limit ){
            $items = (int) ceil( $total / $limit );
            $prev  = range(intval( $page - 3 ) < 3 ? 0 : intval( $page - 3 ) ,$page );
            $aft   = range( $page ,$page + 3 );
            for( $i = 0; $i <= $items; $i++ ){
                if( $i == 0 && $page != 0 ){
                    $page_loop .= '<a href="'. home_url() .'/ticket/paged=0" onclick="return false;" class="pagination__number" id="'. $i .'"> <<< </a>';
                }
                elseif( in_array( $i ,$prev ) && $i != $page && $i != 0 ){
                    $page_loop .= '<a href="'. home_url() .'/ticket/paged='.$page_num.'" onclick="return false;" class="pagination__number" id="'. $i .'"> '.$page_num.' </a>';
                }
                if ( $i == $page ){
                    $page_loop .= '<span class="pagination__number pagination__number--current">'.$page_num.'</span>';
                }
                if ( in_array( $i ,$aft ) && !in_array( $i ,$prev ) ){
                    $page_loop .= '<a href="'. home_url() .'/ticket/paged='.$page_num.'" onclick="return false;" class="pagination__number" id="'. $i .'"> '.$page_num.' </a>';
                }
                elseif ( $i == $items && $i != $page ){
                    $page_loop .= '<a href="'. home_url() .'/ticket/paged='.$page_num.'" onclick="return false;" class="pagination__number" id="'.$items .'"> >>> </a>';
                }
                $page_num++;
            }
            return
                '<div class="main-block__footer">
                    <nav class="pagination">
                        '. $page_loop .'
                    </nav>
                </div>
            ';
        }
        return '';
    }


    public static function masterForm( $userID )
    {
        global $GLOBAL_TICKET_PERMISSION;

        if ( !isset( $_POST['course-id'] ) || empty( $_POST['course-id'] ) || !is_numeric( $_POST['course-id'] ) &&
             !isset( $_POST['user-id'] )   || empty( $_POST['user-id'] )   || !is_numeric( $_POST['user-id'] ) ){
             Functions::_404();
        }
        $courseID  = $_POST['course-id'];
        $studentID = $_POST['user-id'];
        $title     = $_POST['title'];

        if ( Permissions::whoIs( $userID ) == 'admin' || Permissions::isAccessToSpecificTicket( $GLOBAL_TICKET_PERMISSION ,$courseID ,'tango_support' ) ){
            ?>
                <div id="tango-panel" class="article-content" >
                    <form id="master-new"
                    data-course_id="<?php echo $courseID; ?>"
                    data-user_id="<?php echo $studentID; ?>" >
                        <span id="back" class="pointer" > بازگشت </span>
                        <br>
                        <label for="course-id">دوره آموزشی:  </label>
                        <br>
                        <input type="text" value="<?php echo wc_get_product( $courseID )->get_title(); ?>" name="course-id" id="course-id" disabled >
                        <br>
                        <label for="user-id">دانشجو:  </label>
                        <br>
                        <input type="text" value="<?php echo get_user_by('id' ,$studentID )->last_name; ?>" name="user-id" id="user-id" disabled >
                        <br>
                        <label for="title">موضوع: </label>
                        <br>
                        <input type="text" name="title" id="title" value="<?php echo $title; ?>" required="required" disabled>
                        <br>
                        <label for="content">متن تیکت: </label>
                        <div id="hwp-master-ticket" >
                        </div>
                        <div id="file_holder"></div>
                        <br>
                        <div class="dropzone dz-clickable" id="file" data-direct_load="true" >
                            <div class="dz-default dz-message">
                                <button class="dz-button" type="button">فایل را اینجا رها کن یا کلیک کن</button>
                            </div>
                        </div>
                        <br>
                        <input type="submit" name="submit" value="ارسال" class="pull-left" >
                    </form>
                <style>
                    #footer.stiky-bottom{
                        position: initial;
                    }
                </style>
            <?php
        }
    }


    public static function test(){
        ?>
            <form action="https://reza.test/ticket/master-new" method="post" target="_blank">
                <input type="hidden" id="user-id" name="user-id" value="7" />
                <input type="hidden" id="course-id" name="course-id" value="206007" />
                <input type="hidden" id="title" name="title"  value="تست شماره 24 آموزش  html" />
                <button type="submit" class="btn">Create Ticket</button>
            </form>
        <?php
    }


    public static function ticketSeenList( object $ticketObject )
    {
        global $GLOBAL_TICKET_IS_SUP;
        $output     = '';
        $items      = '';
        if ( $GLOBAL_TICKET_IS_SUP ){
            $seen_list  = Functions::getBulkUsersSeenList( $ticketObject );
            if ( is_array( $seen_list ) && !empty( $seen_list ) ){
                $seen_users = Functions::preparationUsersList( array_keys( $seen_list ) );
                if ( !empty( $seen_users ) && is_array( $seen_users ) ){
                    foreach ( $seen_list as $user_id => $time ) {
                        $user = Functions::indexChecker( $seen_users ,$user_id );
                        if( is_array( $user ) ){
                            $item   = str_replace( '[name]'     ,Functions::indexChecker( $user ,'display_name' ,'بدون نام' ) ,PartialUI::ticketSeenItem() );
                            $item   = str_replace( '[mobile]'   ,Functions::indexChecker( $user ,'mobile' ,'بدون موبایل' ) ,$item );
                            $items .= str_replace( '[datetime]' ,date_i18n('Y/m/d H:i' ,$time ) ,$item );
                        }
                    }
                }
                $output = str_replace( '[ticket-id]' ,$ticketObject->id ,PartialUI::ticketSeenList() );
                $output = str_replace( '[items]' ,$items ,$output  );
            }
        }
        return $output;
    }


    public static function creatorSeenReply( object $ticketObject )
    {
        global $GLOBAL_TICKET_IS_SUP;
        if ( $GLOBAL_TICKET_IS_SUP ){
            $logs = maybe_unserialize( Functions::indexChecker( $ticketObject ,'tags' ,[] ) );
            if ( isset( $logs['owner_seen_reply'] ) && !empty( $logs['owner_seen_reply'] ) ){
                $seen = str_replace( '[creator-seen-status]' ,'dashicons-saved' ,PartialUI::creatorSeen() );
                return  str_replace( '[creator-seen-color]'  ,'green',$seen );
            }
            $seen = str_replace( '[creator-seen-status]' ,'dashicons-minus' ,PartialUI::creatorSeen() );
            return  str_replace( '[creator-seen-color]'  ,'orange',$seen );
        }
        return '';
    }


    public static function forwardLog( object $ticketObject ,$usersList )
    {
        global $GLOBAL_TICKET_IS_SUP;
        if ( $GLOBAL_TICKET_IS_SUP ){
            $items = '';
            $forward_list = maybe_unserialize( $ticketObject->tags );
            $forward_list = Functions::getFillValue( $forward_list,'change_destination_list');
            if ( !empty( $forward_list ) && is_array( $forward_list ) ){
                foreach ( $forward_list as $time => $values ){
                    if( is_array( $values ) ){
                        foreach ( $values as $user => $destinations ){
                            $item   = str_replace( '[name]' ,Functions::getUserFromBulkList( $usersList ,$user ,'nicename' ) ,PartialUI::forwardLogItem() );
                            $item   = str_replace( '[destination-from]' ,Functions::destinationTranslate( key( $destinations ) ) ,$item );
                            $item   = str_replace( '[destination-to]' ,Functions::destinationTranslate( reset( $destinations ) )  ,$item );
                            $items .= str_replace( '[time]' ,human_time_diff( time(),  $time ) .' قبل ' ,$item );
                        }
                    }
                }
                $output = str_replace( '[ticket-id]' ,$ticketObject->id ,PartialUI::forwardLog() );
                return str_replace( '[items]' ,$items ,$output );
            }
        }
        return '';
    }


    public static function assignedList( object $ticketObject )
    {
        global $GLOBAL_TICKET_IS_SUP;
        if ( $GLOBAL_TICKET_IS_SUP ){
            $items = '';
            $assigned_list = maybe_unserialize( $ticketObject->tags );
            $assigned_list = Functions::indexChecker( $assigned_list,'assign_list' );
            if ( !empty( $assigned_list )) {
                foreach ( $assigned_list as $assigner => $details ) {
                    $assigner    = get_user_by('id' ,$assigner );
                    $assigned_to = get_user_by('id' ,reset( $details ) );
                    if ( !empty( $assigner ) && !empty( $assigned_to ) ){
                        $item   = str_replace( '[from-user]' ,$assigner->user_nicename ,PartialUI::assignedListItem() );
                        $item   = str_replace( '[to-user]' ,$assigned_to->user_nicename ,$item );
                        $items .= str_replace( '[time]' ,human_time_diff( time() ,key( $details ) ) . ' قبل ' ,$item );
                    }
                }
                $output = str_replace( '[ticket-id]' ,$ticketObject->id ,PartialUI::assignedList() );
                return    str_replace( '[items]' ,$items ,$output );
            }
        }
        return '';
    }


    public static function preview( $ticketID )
    {
        global $GLOBAL_TICKET_WHO_IS;
        if( 'admin' == $GLOBAL_TICKET_WHO_IS ){
            return str_replace( '[ticket-id]' ,$ticketID ,PartialUI::preview() );
        }
        return '';
    }


    public static function removeReply( $userID ,$child ,$parent ){
        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_IS_SUP;
        $creator   = Functions::indexChecker( $child ,'creator' ,0 );
        $remove_ui = str_replace( '[ticket-id]' ,Functions::indexChecker( $child ,'id','error' ) ,PartialUI::removeReply() );
        if ( $creator != $parent->creator ){
            if ( 'admin' == $GLOBAL_TICKET_WHO_IS && $creator  ){
                return $remove_ui;
            }
            elseif ( $userID == $creator  && $GLOBAL_TICKET_IS_SUP ){
                $last_reply_seen = maybe_unserialize( $parent->tags );
                $last_reply_seen = Functions::indexChecker( $last_reply_seen ,'questioner_seen_ticket' ,[] );
                if ( empty( $last_reply_seen ) && $child->main_object != 'comment' && $child->creator != 0 ){
                    return $remove_ui;
                }
            }
        }
        return '';
    }



    public static function coursesLists( $userID )
    {
        $items   = '';
        $orders = Course::get_instance()->getAllPurchase( $userID );
        if ( is_array( $orders ) && !empty( $orders ) && count( $orders ) > 0 ) {
            foreach ( Course::get_instance()->getAllPurchase( $userID ) as $list ) {
                $item   = str_replace( '[name]'     ,Functions::indexChecker( $list ,'name' ) ,PartialUI::coursesListItem() );
                $item   = str_replace( '[order-id]' ,Functions::indexChecker( $list ,'order_id' ) ,$item );
                $item   = str_replace( '[support]'  ,Functions::indexChecker( $list ,'support' )  ,$item );
                $items .= str_replace( '[home-url]' ,home_url()  ,$item );
            }
            return str_replace( '[list]' ,$items ,PartialUI::coursesList() );
        }
        return str_replace( '[list]' ,'موردی یافت نشد' ,PartialUI::coursesList() );
    }


    public static function allUserOrders( $creatorID )
    {
        global $GLOBAL_TICKET_IS_SUP;
        global $GLOBAL_TICKET_WHO_IS;
        if ( $GLOBAL_TICKET_WHO_IS != 'master' && $GLOBAL_TICKET_IS_SUP ){
            return str_replace( '[list]' ,self::coursesLists( $creatorID ) ,PartialUI::coursesListContainer() );
        }
        return '';
    }


    public static function ratingStars( $ratingStatus ,$replyType ,$ticketObject ,$parentObject ,$userID )
    {
        if ( !empty( $ratingStatus ) ){
            if ( empty( $ticketObject->rate ) ){
                if ( $replyType == 'received' && $ticketObject->creator != $parentObject->creator && $parentObject->creator == $userID ){
                    $output = str_replace( '[ticket-id]' ,$ticketObject->id ,PartialUI::ratingStars() );
                    return    str_replace( '[parent-id]' ,$parentObject->id ,$output );
                }
            }else{
                return PartialUI::ratingReadOnly( $ratingStatus ,$ticketObject->rate );
            }
        }
        return '';
    }




}



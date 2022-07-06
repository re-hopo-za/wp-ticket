<?php


namespace HWP_Ticket\core\ui;



use HWP_Ticket\core\includes\Course;
use HWP_Ticket\core\includes\Database;
use HWP_Ticket\core\includes\Destination;
use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\includes\Permissions;
use HWP_Ticket\core\includes\Uploader;
use HWP_Ticket\core\includes\Users;
use HWP_Ticket\core\requests\Http;

class PartialUI
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
    }


    public static function ticketRoot()
    {
        ob_start();
        ?>
        <div class="article-content" id="hwp-root-element"
             style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;" >
            <div class="theiaStickySidebar"
                 style="padding-top: 0px; padding-bottom: 1px; position: static; transform: none;">
                <div id="tango-panel">
                    [icons-list]
                    [filter-section]
                    <ul class="ticket-list">
                        [loop]
                    </ul>
                </div>
            </div>
            <div class="main-block__footer">
                <nav class="pagination">
                    [pagination]
                </nav>
            </div>
        </div>
        <?php
        echo self::resolveScreenWidth();
        return ob_get_clean();
    }


    public static function ticketLoop()
    {
        ob_start();
        ?>
        <li class="ticket-list__item [status-class]">
            <a class="ticket-list__item-wrap ticket-item" title="[excerpt]" href="[ticket-url]" data-id="[ticket-id]" onclick="return false" target="_blank">
                <div class="ticket-list__item-header right">
                    <div class="ticket-list__avatar-wrapper">
                        <img data-lazyloaded="1"
                             src="[profile-image]"
                             class="ticket-list__item-thumb litespeed-loaded"
                             data-src="[profile-image]"
                             data-was-processed="true" >
                        <noscript>
                            <img class="ticket-list__item-thumb" src="[profile-image]">
                        </noscript>
                    </div>
                    <div class="ticket-list__item-title">
                        <b style="font-size: 15px"> [nicename] </b>
                        <time class="fa-num" dir="rtl">
                            <b> [create-date] </b>
                        </time>
                        <p>
                            [destination] ( [main-object] )
                        </p>
                    </div>
                </div>
                <div class="ticket-list__item-name">
                    <p>
                        [ticket-title]
                    </p>
                    <strong> وضعیت تیکت:
                        [status]
                    </strong>
                    <div class="ticket-actions">
                        [preview]
                        [seen-list]
                        [forwards]
                        [assigns]
                        [creator-seen]
                    </div>
                </div>
                <div class="ticket-list__item-time">
                    <div class="time-root">
                        <time> [elapsed-date] قبل </time>
                        <div class="loop">
                            [rating]
                        </div>
                    </div>
                </div>
            </a>
<!--            <div class="popup-container [ticket-id]" style="display: none" data-ticket-id="[ticket-id]"></div>-->
        </li>
        <?php
        return ob_get_clean();
    }



    public static function filterSection()
    {
        ob_start();
        ?>
        <div id="ticket-filter">
            <label for="search" class="search">جستجو
                <input name="search" type="text" id="search"  placeholder="جستجو" autocomplete="off" >
                <svg height="20" viewBox="0 0 74 74" width="20"  >
                    <g id="line_icons">
                        <path d="m27.971 53.928a25.95 25.95 0 1 1 18.363-7.594 25.887 25.887 0 0 1 -18.363 7.594zm0-49.917a23.952 23.952 0 1 0 16.95 7.01 23.9 23.9 0 0 0 -16.951-7.01z"/>
                        <path d="m64.121 72c-.1 0-.207 0-.311-.006a7.93 7.93 0 0 1 -5.7-2.768l-13.2-15.541a1 1 0 0 1 .207-1.479 1.59 1.59 0 0 0 .216-.184l6.69-6.69a1.435 1.435 0 0 0 .175-.2 1 1 0 0 1 1.487-.219l15.541 13.2a7.9 7.9 0 0 1 .464 11.587 7.892 7.892 0 0 1 -5.569 2.3zm-17.062-18.875 12.573 14.805a5.9 5.9 0 0 0 8.644.353 5.9 5.9 0 0 0 -.347-8.653l-14.8-12.572z"/>
                        <path d="m44.073 54.551a3.788 3.788 0 0 1 -2.684-1.116l-2.315-2.315a1 1 0 1 1 1.414-1.414l2.312 2.315a1.781 1.781 0 0 0 2.291.2 1.875 1.875 0 0 0 .235-.2l6.693-6.692a1.692 1.692 0 0 0 .194-.224 1.782 1.782 0 0 0 -.194-2.3l-2.315-2.315a1 1 0 0 1 1.414-1.414l2.315 2.315a3.774 3.774 0 0 1 .421 4.859 3.631 3.631 0 0 1 -.421.5l-6.693 6.692a3.739 3.739 0 0 1 -2.67 1.116z"/>
                        <path d="m23.748 35.46a.993.993 0 0 1 -.707-.293l-4.136-4.135a1 1 0 0 1 1.414-1.414l3.429 3.428 11.883-11.883a1 1 0 0 1 1.414 1.414l-12.59 12.59a1 1 0 0 1 -.707.293z"/>
                        <path d="m27.97 47.746a19.776 19.776 0 1 1 13.99-5.786 19.725 19.725 0 0 1 -13.99 5.786zm0-37.552a17.777 17.777 0 1 0 12.576 5.2 17.725 17.725 0 0 0 -12.576-5.2z"/>
                    </g>
                </svg>
            </label>

            <label for="status">انتخاب وضعیت
                <select name="status" id="status" autocomplete="off" >
                    <option value="" class="select_placeholder">همه وضعیت ها</option>
                    <option value="first">جدید</option>
                    [status-options]
                </select>
            </label>

            <label for="destination">انتخاب واحد
                <select name="destination" id="destination" class="" autocomplete="off" >
                    <option value="" class="select_placeholder">همه واحدها</option>
                    [destination-options]
                </select>
            </label>
            [admin-filter]

            <label for="sort">مرتب سازی
                <select name="sort" id="sort" autocomplete="off" >
                    <option value=""  selected="selected" >پیشفرض</option>
                    [sort-filter]
                </select>
            </label>

            <label for="limit">تعداد
                <select name="limit" id="limit" autocomplete="off" >
                    <option value="15" selected="selected">15</option>
                    <option value="40">40</option>
                    <option value="50">50</option>
                </select>
            </label>
        </div>
        <?php
        return ob_get_clean();
    }



    public static function iconsSection()
    {
        ob_start();
        ?>
        <div id="request">
            <div>
                <a id="new" class="pointer" onclick="return false" href="[new-ticket-url]/ticket/new/" target="_blank"> تیکت جدید</a>
            </div>
            <div class="extra-option">
                [icons-list]
                <span class="space" ></span>
                <span title="دریافت مجدد"  id="reload-tickets" class="refresh dashicons dashicons-image-rotate"></span>
                <span title="بازنشانی فیلتر ها"  id="reset-tickets" class="refresh dashicons dashicons-update-alt"></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


    public static function ticketChildren()
    {
        ob_start();
        ?>
        <div class="message message--[class-name]" id="reply-[ticket-id]">
            <div class="message__wrap">
                <div class="message__text">
                    [rating]
                    [content]
                </div>
                <div class="message__details">
                    <div class="message__avatar" style="background-image: url('[image-url]');"></div>
                    <div class="message__info">
                        [creator-name]
                        <b dir="rtl"> <time>[time] </time> </b>
                        [remove-reply]
                    </div>
                </div>
                <div class="message__img reply-file-container">
                    [files-loop]
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


    public static function emptyList()
    {
        return '<li class="ticket-list__item">
                 <a class="ticket-list__item-wrap" href="#"  onclick="return false" >
                     <div class="ticket-list__empty"><p> هیچ تیکتی برای شما ثبت نشده</p></div>
                 </a>
           </li>
       ';
    }

    public static function prepareReplyContent( $content )
    {
        if( !empty( $content ) ){
            return
                '<div class="ck-content"> 
                    '.$content.'
                </div> 
            ';
        }
        return '';
    }


    public static function ticketSingle(){
        ob_start();
        ?>
        <div class="article-content"
             style="position: relative; overflow: visible; box-sizing: border-box; min-height: 1px;">
            <div class="theiaStickySidebar"
                 style="padding-top: 0; padding-bottom: 1px; position: static; transform: none;">
                <div id="tango-panel"
                     data-ticket_id="[ticket-id]"
                     data-is_student="[is-student]" >
                    <div class="ticket-view">
                        <div class="ticket__header">
                            <a id="back" class="pointer" onclick="return false" href="[ticket-home]">
                                بازگشت
                            </a>
                            <div class="clearfix"></div>
                            <div class="ticket__title">
                                <div class="ticket__title-thumb">
                                    <img src="[avatar]" alt="[first-name] [last-name]">
                                </div>
                                <div class="ticket__title-info">
                                    <span class="ticket__title-right">
                                        <svg id="svg_username" viewBox="0 0 14.8 17.1">
                                            <path d="M10.9 7.3c.6-.8.9-1.7.9-2.8C11.8 2 9.8 0 7.3 0S2.8 2 2.8 4.5c0 1.1.4 2 1
                                             2.8C1.5 8.6 0 11.1 0 13.7c0 2.3 3.7 3.4 7.4 3.4s7.4-1 7.4-3.3c0-2.7-1.5-5.2-3.9-6.5zM7.3
                                             1c2 0 3.5 1.6 3.5 3.5 0 2-1.6 3.5-3.5 3.5-2 0-3.5-1.6-3.5-3.5S5.4 1 7.3 1zm.1 15.1c-3.1
                                             0-6.4-.8-6.4-2.4 0-2.3 1.3-4.5 3.3-5.6.9.6 2 1 3.1 1s2.2-.3 3-1c2 1.1 3.3 3.3 3.3 5.6.1
                                             1.6-3.2 2.4-6.3 2.4z">  </path>
                                        </svg>
                                         [nicename]
                                    </span>
                                    [phone]
                                    <br>
                                    <span>
                                            <svg id="svg_products" viewBox="0 0 28 26">
                                                <path fill="none" d="M18 0h8c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2h-8c-1.1 0-2-.9-2-2V2c0-1.1.9-2 2-2z"></path>
                                                <path d="M26 12h-8c-1.1 0-2-.9-2-2V2c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2zM18 2v8h8V2h-8z"> </path>
                                                <path fill="none" d="M2 0h8c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2H2c-1.1 0-2-.9-2-2V2C0 .9.9 0 2 0z"> </path>
                                                <path d="M10 12H2c-1.1 0-2-.9-2-2V2C0 .9.9 0 2 0h8c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2zM2 2v8h8V2H2z"> </path>
                                                <path fill="none" d="M18 14h8c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2h-8c-1.1 0-2-.9-2-2v-8c0-1.1.9-2 2-2z"></path>
                                                <path d="M26 26h-8c-1.1 0-2-.9-2-2v-8c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2zm-8-10v8h8v-8h-8z"> </path>
                                                <path fill="none" d="M2 14h8c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2H2c-1.1 0-2-.9-2-2v-8c0-1.1.9-2 2-2z"> </path>
                                                <path d="M10 26H2c-1.1 0-2-.9-2-2v-8c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2zM2 16v8h8v-8H2z"> </path>
                                            </svg>
                                            <strong>واحد مربوطه:</strong> [destination]
                                        </span>
                                    <span class="right-line">
                                        <strong>وضعیت تیکت:</strong>
                                        <span> [status] </span>
                                    </span>
                                    <br>
                                    <span>
                                         <svg id="svg_eye" viewBox="0 0 38.2 23.3">
                                              <path fill="#B3B3B3" d="M37.2 12.7c-.4 0-.7-.2-.9-.5-1.8-3.2-4.5-5.9-7.7-7.7C19.1-.8 7.2 2.7 2
                                               12.1c-.3.5-.9.7-1.4.4-.5-.3-.7-.9-.4-1.4C6 .7 19.1-3.1 29.5 2.7c3.6 2 6.5 4.9 8.5 8.5.3.5.1 1.1-.4 1.4-.1 0-.3.1-.4.1z"></path>
                                              <path fill="#B3B3B3" d="M19 23.3c-3.6 0-7.2-.9-10.4-2.7-3.6-2-6.5-4.9-8.5-8.5-.2-.4-.1-1.1.4-1.3.5-.3 1.1-.1 1.4.4 1.8 3.2 4.5 5.9
                                               7.7 7.7 4.6 2.5 9.9 3.1 14.9 1.7 5-1.5 9.2-4.8 11.7-9.4.3-.5.9-.7 1.4-.4.5.3.7.9.4 1.4-2.8 5.1-7.4 8.7-12.9 10.3-2 .5-4.1.8-6.1.8z"> </path>
                                              <path fill="#B3B3B3" d="M19.1 23.3c-6.4 0-11.6-5.2-11.6-11.6C7.4 5.2 12.6 0 19.1 0s11.7 5.2 11.7 11.6c-.1 6.5-5.3 11.7-11.7
                                               11.7zm0-21.3c-5.3 0-9.6 4.3-9.6 9.6s4.3 9.6 9.6 9.6 9.7-4.3 9.7-9.6S24.4 2 19.1 2z">  </path>
                                              <circle fill="#B3B3B3" cx="19.1" cy="11.7" r="4.1"> </circle>
                                        </svg>
                                    <strong>شماره تیکت: [ticket-id]</strong>
                                    </span>
                                    <span>
                                            <b>
                                                <time >[time]</time>
                                            </b>
                                        </span>
                                    <br>
                                    [all-user-ticket]
                                    [all-user-orders]
                                    [user-license]
                                    [all-user-info]
                                    <p>
                                        <strong>عنوان:</strong>
                                        [title]
                                    </p>
                                </div>
                            </div>
                            <div class="ticket__product">
                                <p class="ticket__product-name">
                                    <strong>محصول مرتبط: </strong>
                                    [main-object]
                                    [remain-support]
                                </p>
                            </div>
                        </div>
                        <div>
                            <h1 class="MessagesList"></h1>
                            <div class="message message--[ticket-owner]">
                                <div class="message__wrap">
                                    <div class="message__text">
                                        <div class="ck-content">
                                            <p>[content]</p>
                                        </div>
                                    </div>
                                    <div class="message__img">
                                        <ul>
                                            [files-loop]
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <h1 class="MessagesList"></h1>
                            <div id="loop-container">
                                [reply-list]
                            </div>
                        </div>
                        [text-template]
                        [single-actions]
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo self::resolveScreenWidth();
        return ob_get_clean();
    }


    public static function resolveScreenWidth()
    {
        return
            '<style>
            #footer.stiky-bottom{
                position: initial;
            }
        </style>';
    }


    public static function loadTextTemplate( $status ,$courseID ,$userID )
    {
        global $GLOBAL_TICKET_IS_SUP;
        if ( $GLOBAL_TICKET_IS_SUP && $status != 'finished' ){
            return TextTemplateUI::get_instance()::loadTemplate( $courseID ,$userID );
        }
        return '';
    }


    public static function replyFormDestinationOptions( $dest ,$ticketID )
    {
        global $GLOBAL_TICKET_IS_SUP;
        $options = '<select name="destination" data-old_destination="'.$dest.'" id="destination" data-ticket_id ="'.$ticketID.'" >';
        if ( $GLOBAL_TICKET_IS_SUP ){
            foreach ( Destination::get_instance()::getDestinationsList() as $key =>  $value ){
                $selected = $dest  == $key ? 'selected' : '';
                $options .= '<option value="'.$key.'" '.$selected.' >'.$value.'</option>';
            }
        }
        $options .= '</select>';
        return $options;
    }


    public static function listOfUserSupport( $userID ,$ticketObject )
    {
        global $GLOBAL_TICKET_IS_SUP;
        if ( $GLOBAL_TICKET_IS_SUP ){
            $users = Users::getSupportUsers();
            $selected_all  =  empty( $ticketObject->assign_to ) ? 'selected' : '' ;
            $support_list  = '<select name="assign" id="assign" data-ticket_id ="'.$ticketObject->id.'" >';
            $support_list .= '<option value="all" '.$selected_all.' > 
                                    تمامی کاربران 
                             </option>';
            foreach ( $users as $user ){
                if ( $user['id'] == $ticketObject->assign_to  ){
                    $selected_text = 'selected';
                }else{
                    $selected_text = '';
                }
                if (  $user['id']!=  $userID ){
                    $support_list .='<option value="'.$user['id'].'"  '.$selected_text.'> '.$user['name'].' </option>';
                }
            }
            $support_list .= '</select>';
            return $support_list;
        }
        return '';
    }


    public static function replyFormCommentTick()
    {
        global $GLOBAL_TICKET_WHO_IS;
        $output = '';
        if( 'admin' == $GLOBAL_TICKET_WHO_IS ){
            $output = '
            <div>
                <label>
                    کامنت
                    <input type="radio" name="is-comment" class="is-comment" value="comment" >
                </label>
                <label>
                    پاسخ
                    <input type="radio" name="is-comment" class="is-comment" value="reply" checked="checked" >
                </label>
            </div>';
        }
        return $output;
    }


    public static function newReplyForm( $object ,$userID ,$permission )
    {
        $output = '';
        if ( $object->status != 'finished' ){
            $output =
                '<div class="voice-container">
                    '. self::checkUserCanSendVoice( $permission ) .'
                    <label for="content">متن تیکت</label>  
                </div> 
                <form action="/" id="reply_form" autocomplete="off" class="" novalidate=""  data-parent_id="'.$object->id.'"> 
                    <div id="hwp-ticket-new-reply"></div> 
                    <div id="file_holder"></div>
                    <div class="dropzone dz-clickable" id="file" data-direct_load="true">
                        <div class="dz-default dz-message">
                            <button class="dz-button" type="button">فایل را اینجا رها کن یا کلیک کن</button>
                        </div>
                    </div> 
                    <div class="select-container">
                    </div>
                ';
            $output .= self::getUsersSelectList( $userID ,$object );
            $output .= '<br>
                    <div class="submit-btn">
                        <div>
                            '.self::replyFormCommentTick().'
                            <input type="submit" name="submit" value="ارسال" class="sub pull-left">
                        </div> 
                    </div>
                </form>';
        }
        return $output;
    }


    public static function getUsersSelectList( $userID ,$object )
    {
        global $GLOBAL_TICKET_USER_TRUST;
        $output = '<div class="select-container">';
        if ( $GLOBAL_TICKET_USER_TRUST ){
            $output .= self::replyFormSelectStatus( (object) $object );
            $output .= self::replyFormChangeDestination( (object) $object );
            $output .= self::replyFormAssignUsersList( $userID ,(object) $object );
        }
        return $output.'</div>';
    }

    public static function checkUserCanSendVoice( $permission )
    {
        if( $permission ){
            return
                '<button class="recorder-voice">
                    <svg id="recorder-voice" viewBox="0 0 512.000000 512.000000" id="microphone">
                        <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)" stroke="none">
                            <path d="M2417 4944 c-161 -29 -302 -93 -432 -198 -163 -130 -273 -301 -332
                                -518 -17 -58 -18 -132 -18 -868 0 -736 1 -810 18 -868 59 -217 169 -388 332
                                -518 423 -339 1040 -263 1365 167 110 147 172 307 190 495 12 129 12 1319 0
                                1448 -19 190 -79 347 -190 495 -217 287 -579 429 -933 365z"/>
                                 <path d="M895 2706 c-42 -18 -83 -69 -91 -113 -4 -20 -4 -84 0 -142 24 -407
                                187 -791 462 -1089 282 -306 664 -502 1086 -557 l47 -6 3 -269 c3 -253 4 -270
                                24 -296 39 -53 71 -69 134 -69 63 0 95 16 134 69 20 26 21 43 24 296 l3 268
                                82 12 c244 35 512 134 707 260 488 316 799 869 804 1427 1 132 -5 151 -68 197
                                -39 29 -133 29 -172 0 -59 -43 -67 -64 -76 -196 -26 -388 -162 -693 -423 -953
                                -207 -208 -437 -332 -733 -397 -140 -31 -424 -31 -564 0 -295 65 -527 190
                                -733 397 -262 261 -398 567 -423 953 -9 131 -17 153 -74 195 -33 25 -113 32
                                -153 13z"/>
                        </g>
                    </svg>
                    <svg viewBox="0 0 512.000000 512.000000" id="save-recorded-voice"  > 
                        <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)"  stroke="none">
                            <path d="M1310 5109 c-231 -30 -447 -114 -639 -250 -107 -75 -301 -267 -392
                            -389 -127 -169 -212 -359 -256 -576 -17 -83 -18 -173 -18 -1334 0 -1161 1
                            -1251 18 -1334 64 -313 201 -552 460 -802 185 -180 353 -284 564 -353 213 -69
                            134 -66 1513 -66 1161 0 1251 1 1334 18 295 60 516 179 745 402 259 251 394
                            489 458 801 17 83 18 173 18 1334 0 1161 -1 1251 -18 1334 -64 312 -199 550
                            -458 801 -224 217 -429 330 -721 398 -92 21 -106 21 -1318 23 -674 1 -1254 -2
                            -1290 -7z m2491 -404 c154 -27 307 -91 425 -178 82 -60 228 -206 293 -292 70
                            -93 145 -249 173 -360 l23 -90 0 -1225 0 -1225 -23 -89 c-27 -108 -95 -254
                            -159 -345 -57 -79 -223 -246 -307 -308 -118 -87 -271 -151 -425 -178 -125 -22
                            -2357 -22 -2482 0 -155 27 -310 92 -425 178 -84 64 -230 209 -293 292 -70 93
                            -145 249 -173 360 l-23 90 0 1225 0 1225 23 89 c27 108 95 254 159 345 56 78
                            223 246 308 309 113 83 271 150 418 177 113 21 2372 21 2488 0z"/>
                            <path d="M2320 3541 c-60 -19 -106 -61 -502 -458 -435 -437 -438 -440 -438
                            -523 0 -83 3 -86 438 -523 230 -230 433 -427 452 -437 86 -44 169 -32 235 35
                            65 65 79 147 38 230 -8 17 -119 135 -246 263 l-232 232 758 0 c828 0 797 -2
                            860 58 43 41 60 81 60 142 0 61 -17 101 -60 142 -63 60 -32 58 -860 58 l-758
                            0 232 233 c227 228 254 260 267 323 19 88 -55 199 -146 222 -52 13 -62 14 -98
                            3z"/>
                        </g>
                    </svg> 
                </button>
                <div id="recorded-list"> </div>
            ';
        }
        return '';
    }


    public static function replyFormSelectStatus( object $object )
    {
        return
            '<div class="select-item">
                <label for="status">وضعیت تیکت </label>
                <select name="status" id="status" data-ticket_id ="'.$object->id.'"  >
                    '.self::statusOptions().'
                </select>
            </div>';
    }


    public static function replyFormChangeDestination( object $object )
    {
        global $GLOBAL_TICKET_WHO_IS;
        if ( 'admin' == $GLOBAL_TICKET_WHO_IS || 'support' == $GLOBAL_TICKET_WHO_IS ){
            return
                '<div class="select-item">
                <label for="destination">واحد مربوطه</label>
                '.self::replyFormDestinationOptions( $object->destination ,$object->id ).' 
            </div>';
        }
        return '';
    }

    public static function replyFormAssignUsersList( $userID ,object $object )
    {
        global $GLOBAL_TICKET_WHO_IS;
        if ( 'admin' == $GLOBAL_TICKET_WHO_IS || 'support' == $GLOBAL_TICKET_WHO_IS ){
            return
                '<div class="select-item">
                <label for="assign">ارجاع</label>
                ' . self::listOfUserSupport( $userID, $object ) . '
            </div>';
        }
        return '';
    }





    public static function replyCreatorName( $creatorID )
    {
        if ( $creatorID == 0 ){
            return '<b style="padding: 0 10px;"> سیستم </b>';
        }else{
            $userName = Functions::getUser( $creatorID );
            if ( $userName->user_status ) {
                return '<b style="padding: 0 10px;">'.$userName->first_name .' '.$userName->last_name .'</b>';
            }else{
                return '<b style="font-size: 15px"> کاربر یافت نشد </b>';
            }
        }
    }


    public static function filesLoop( $ticketID )
    {
        $files = Uploader::get_instance()::getFiles( $ticketID );
        $list_aud = '';
        $list_pic = '';
        if ( !empty( $files )){
            foreach ( $files as $file ){
                $link  = Uploader::getLink( Functions::indexChecker( $file ,'id' ) );
                $path  = Functions::indexChecker( $link ,'file_path' ,home_url()  );
                $exten = Functions::fileExtension( Functions::indexChecker( $link ,'extension' ) );
                if ( $file->type == 'wav' || $file->type == 'mp3'){
                    $list_aud .=
                        '<div class="recorded-voice-container">
                            <audio src="'.$path.'" controls="controls" ></audio> 
                        </div>';
                }else{
                    $list_pic .=
                        '<div>
                            <a href="'.$path.'" target="_blank">
                                '.$exten.'
                            </a>
                        </div>';
                }
            }
        }
        return '<div class="file-pic-con">'.$list_aud.'</div> <div class="file-wav-con">'.$list_pic.'</div>';
    }




    public static function remainSupportDays( $creatorID ,$main_object )
    {
        global $GLOBAL_TICKET_IS_SUP;
        if ( $GLOBAL_TICKET_IS_SUP  ){
            return "( ".Users::getRemainSupport ( $creatorID ,$main_object )." )";
        }
        return '';
    }


    public static function userLicensePage( $creatorID )
    {
        global $GLOBAL_TICKET_IS_SUP;
        global $GLOBAL_TICKET_WHO_IS;
        if ( $GLOBAL_TICKET_WHO_IS != 'master' && $GLOBAL_TICKET_IS_SUP ){
            return
                '<span class="right-line">  
                    <a href="https://panel.spotplayer.ir/license/?search='.Functions::getSpotAccount( $creatorID ).'" target="_blank">
                    صفحه لایسنس این کاربر
                    </a>
                </span>';
        }
        return '';
    }




    public static function allUserTickets( $userData ){
        global $GLOBAL_TICKET_IS_SUP;
        global $GLOBAL_TICKET_WHO_IS;
        if ( $GLOBAL_TICKET_WHO_IS != 'master' && $GLOBAL_TICKET_IS_SUP ){
            return
                '<span>
                    <a data-theid="'.$userData->ID.'"
                          data-username="'.$userData->first_name.' '.$userData->last_name.'" 
                          href="'.Http::ticketUrl( null , $userData->ID ).'" 
                          target="_blank">
                          همه تیکت های این کاربر
                    </a>
                </span>';
        }
        return '';
    }


    public static function allUserInfo( $userID )
    {
        global $GLOBAL_TICKET_IS_SUP;
        global $GLOBAL_TICKET_WHO_IS;
        if ( $GLOBAL_TICKET_WHO_IS != 'master' && $GLOBAL_TICKET_IS_SUP ){
            return
                '<span class="right-line">
                    <a href="'.home_url().'/wp-admin/admin.php?page=hwp_user_report&s='.$userID.'&type=id" 
                       target="_blank">اطلاعات کاربر</a> 
                </span>';
        }
        return '';
    }


    public static function phoneReplicer( $userID ){
        global $GLOBAL_TICKET_IS_SUP;
        global $GLOBAL_TICKET_WHO_IS;
        if ( $GLOBAL_TICKET_WHO_IS != 'master' && $GLOBAL_TICKET_IS_SUP ){
            return '<a href="tel:00'.get_user_meta( $userID ,'force_verified_mobile' ,true ).'">
                    ('.get_user_meta( $userID ,'force_verified_mobile' ,true ).')</a>';
        }
        return '';
    }



    public static function iconsList()
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_IS_SUP;
        $icons_ist = '';
        $home_url  = home_url();
        if ( 'admin' === $GLOBAL_TICKET_WHO_IS  ){
            $icons_ist .=
                '<a title="پیشخوان" target="_blank" href="'.$home_url.'/ticket/dashboard/" class="links dashicons dashicons-analytics" id="dashboard" ></a>';
        }
        if ( $GLOBAL_TICKET_IS_SUP ){
            $icons_ist .=
                '<a title="قالب نوشتاری" target="_blank" href="'.$home_url.'/ticket/template/" class="links dashicons dashicons-editor-paste-text" id="template-page"></a>
                 <span class="space" ></span>
                 <span title="تیکت های رویت شده بدون پاسخ" id="n-reply-tickets" class="group dashicons dashicons-warning" ></span>
                 <span title="تیکت های رویت نشده " id="unseen-tickets" class="group dashicons dashicons-hidden" ></span> 
                 <span title=" پاسخ های من " id="last-response" class="group dashicons dashicons-format-chat" ></span> ';
        }
        return $icons_ist;
    }


    public static function statusOptions()
    {
        global $GLOBAL_TICKET_IS_SUP;
        $statuses = Functions::getTicketOptions( '_hamfy_ticket_statuses' ,[] );
        $options  = '';
        if ( !empty( $statuses ) ){
            if ( !$GLOBAL_TICKET_IS_SUP ){
                unset( $statuses[ 'answered'] , $statuses[ 'in_progress'] , $statuses[ 'finished']  );
            }
            foreach ( $statuses as $key => $value ){
                $selected = '';
                if( (!$GLOBAL_TICKET_IS_SUP && 'open' === $key) || ( $GLOBAL_TICKET_IS_SUP  && 'answered' === $key )  ) {
                    $selected = 'selected="selected"';
                }
                $options .= '<option value="'.$key.'" '.$selected.' >'.$value.'</option>';
            }
        }

        return $options;
    }


    public static function destinationOptions()
    {
        global $GLOBAL_TICKET_WHO_IS;
        global $GLOBAL_TICKET_PERMISSION;

        $user_des_list = Permissions::get_instance()::getAccessDestinationsList( $GLOBAL_TICKET_PERMISSION );
        $all_des_list  = Destination::get_instance()::getDestinationsList();
        $options       = '';

        if ( 'admin' === $GLOBAL_TICKET_WHO_IS || 'student' === $GLOBAL_TICKET_WHO_IS ){
            foreach (  $all_des_list as  $key => $val  ) {
                $options .= '<option value="'.$key.'" >'. $val .'</option>';
            }
        }elseif ( !empty( $GLOBAL_TICKET_PERMISSION ) ){
            foreach ( $user_des_list as $item  ) {
                $options .= '<option value="'.$item.'" >'. Functions::getFillValue( $all_des_list ,$item ) .'</option>';
            }
        }
        return $options;
    }


    public static function adminFilter()
    {
        global $GLOBAL_TICKET_IS_SUP;
        $options = '';
        if ( $GLOBAL_TICKET_IS_SUP ){
            $options .= '
                <label for="theid">کاربر
                    <select name="theid" id="user" autocomplete="off" class="select-2-filter" data-text="username">
                    </select>
                </label>
                <label for="course">دوره
                    <select name="course" id="course-root" autocomplete="off" class="select-2-filter" data-text="course-name">
                    </select>
                </label>';
        }
        return $options;
    }


    public static function sortFilter()
    {
        global $GLOBAL_TICKET_WHO_IS;
        $options = '';
        if ( 'admin' === $GLOBAL_TICKET_WHO_IS ){
            $options .=' 
                <option value="updated_date|DESC">جدیدترین </option>
                <option value="updated_date|ASC" >قدیمی ترین </option>
                <option value="order_num|DESC" >اولویت زیاد</option>
                <option value="order_num|ASC" >اولویت کم</option>';

        }elseif ( 'support' === $GLOBAL_TICKET_WHO_IS ){
            $options .='
                <option value="updated_date|DESC">جدیدترین </option>
                <option value="updated_date|ASC" >قدیمی ترین </option>
                <option value="order_num|DESC">اولویت زیاد</option>
                <option value="order_num|ASC" >اولویت کم</option>';

        }elseif ( 'master' === $GLOBAL_TICKET_WHO_IS ){
            $options .='
                <option value="order_num|DESC">اولویت زیاد</option>
                <option value="order_num|ASC" >اولویت کم</option>
                <option value="updated_date|DESC">جدیدترین </option>
                <option value="updated_date|ASC" >قدیمی ترین</option>';

        }else{
            $options .='
                <option value="updated_date|DESC">جدیدترین</option>
                <option value="updated_date|ASC" >قدیمی ترین</option>';
        }
        return $options;
    }


    public static function newTicket()
    {
        ob_start();
        ?>
        <div id="tango-panel" class="article-content" >
            <form  id="tango_form" >
                <a id="back" class="pointer" onclick="return false;" href="[home-ticket]" > بازگشت </a>
                <div class="course-con">
                    <div>
                        <label for="course">دوره آموزشی:  </label>
                        <select name="course" id="course" class="" autocomplete="off" placeholder="لطفا یک مورد را انتخاب نمایید..." data-parsley-required="true" required="required">
                            <option value="empty" selected>بدون دوره </option>
                            [course-list]
                        </select>
                    </div>
                    <div class="support-remained"> </div>
                </div>
                <div id="destination" >
                    <input type="radio" name="destination" id="support" value="tango_support" disabled="disabled">
                    <label class="support-con disabled"  for="support" id="support-c" >
                        <span></span>
                        به تیم پشتیبانی دوره میخوام پیام بدم
                    </label>
                    <input type="radio" name="destination" id="license" value="tango_license" disabled="disabled">
                    <label class="license-con disabled" for="license" id="license-c"  style="display: none">
                        <span></span>
                        به تیم لایسنس میخوام پیام بدم
                    </label>
                    <input type="radio" name="destination" id="other" value="tango_other" checked="checked">
                    <label for="other" class="other">
                        <span></span>
                        درخواست دیگری دارم
                    </label>
                </div>
                <label for="title">موضوع: </label>
                <input type="text" name="title" id="title" data-parsley-required="true" data-parsley-minlength="4" data-parsley-maxlength="250" required="required" value="[title]">
                <label for="content">متن تیکت: </label>
                <div id="hwp-ticket-new" style="min-height: 400px;">
                    [content]
                </div>
                <div id="file_holder"></div>

                <div class="dropzone dz-clickable" id="file" data-direct_load="true" >
                    <div class="dz-default dz-message">
                        <button class="dz-button" type="button">فایل را اینجا رها کن یا کلیک کن</button>
                    </div>
                </div>
                <input type="submit" name="submit" value="ارسال" class="pull-left" >
            </form>
        </div>
        <style>
            #footer.stiky-bottom{
                position: initial;
            }
        </style>
        <?php
        return ob_get_clean();
    }


    public static function newTicketCourseList( $userID )
    {
        $course_ui = '';
        $courses = Course::get_instance()::getFormListCourse( $userID );

        if( !empty( $courses ) && is_array( $courses ) ) {
            foreach ( $courses as $course ) {
                foreach ( $course as $key => $val ){
                    $course_ui .= '<option value="'.$key.'" > '.$val['p_name'].' </option>';
                }
            }
        }
        return $course_ui;
    }


    public static function summaryMainSection()
    {
        ob_start();
        ?>
        <div class="message message--main">
            <div class="message__wrap">
                <div class="message__text">
                    <div class="ck-content">
                        <p>
                            [content]
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function summaryItem()
    {
        ob_start();
        ?>
        <div id="[ticket-id]" class="message message--object [owner]">
            <div class="message__wrap">
                <div class="message__text">
                    <div class="ck-content">
                        [content]
                    </div>
                </div>
                <div class="message__details">
                    <div class="message__avatar" style="[avatar]"></div>
                    <div class="message__info">
                        <span> [creator] </span>
                        <span class="fa-num"> [time] </span>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


    public static function summaryNoItem()
    {
        ob_start();
        ?>
        <div class="message message--main summary-no-item">
            <div class="message__wrap">
                <div class="message__text">
                    <div class="ck-content">
                        <p>
                            بدون پاسخ
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


    public static function preview()
    {
        return
            '<div class="summary" title="پیش مشاهده">
                 <span class="summary-opener dashicons dashicons-video-alt3" data-ticket-id="[ticket-id]" ></span>
                 <div class="summary-con list-con" style="display: none">
                    <div class="root"></div>
                 </div>
            </div>
        ';
    }


    public static function ticketSeenList(){
        return
            '<div class="read-container" title="لیست مشاهده کنندکان">  
                <span class="users-seen-opener dashicons dashicons-visibility" data-ticket-id="[ticket-id]"></span>  
                <div class="users-seen-list">
                   <div class="root"> 
                       [items] 
                   </div>
                </div>  
            </div>
        ';
    }


    public static function ticketSeenItem()
    {
        return
            '<div class="item"> 
                <p class="name"> [name] </p>
                <p class="mobile"> [mobile] </p> 
                <time> [datetime] </time>
            </div>
        ';
    }


    public static function creatorSeen()
    {
        return
            '<div class="seen-container" title="وضعیت رویت پاسخ">
                 <span class="seen-icon dashicons [creator-seen-status]" style="color:[creator-seen-color]" > 
                </span>     
            </div>
        ';
    }


    public static function coursesListContainer()
    {
        return
            '<span class="right-line courses-container">
                <a href="" class="courses-icon" onclick="return false;">
                    لیست سفارشات این کاربر
                </a>
               [list]
            </span>
        ';
    }


    public static function coursesList()
    {
        return
            '<div class="ticket-single-courses">  
                <div class="courses-list list-con">
                    <div class="list"> [list] </div>
                </div> 
            </div>
        ';
    }


    public static function coursesListItem()
    {
        return
            '<li>  
                <div class="name">
                     <p>[name]</p>
                </div>
                <div class="others">    
                    <a href="[home-url]/wp-admin/post.php?post=[order-id]&action=edit" target="_blank">
                        <svg viewBox="0 0 512 512" height="45"   width="45" >
                            <path d="m504.399 185.065c-6.761-8.482-16.904-13.348-27.83-13.348h-98.604l-53.469-122.433c-3.315-7.591-12.157-11.06-19.749-7.743-7.592
                             3.315-11.059 12.158-7.743 19.75l48.225 110.427h-178.458l48.225-110.427c3.315-7.592-.151-16.434-7.743-19.75-7.591-3.317-16.434.15-19.749 
                             7.743l-53.469 122.434h-98.604c-10.926 0-21.069 4.865-27.83 13.348-6.637 8.328-9.086 19.034-6.719 29.376l52.657 230c3.677 16.06 17.884 27.276 34.549
                              27.276h335.824c16.665 0 30.872-11.216 34.549-27.276l52.657-230.001c2.367-10.342-.082-21.048-6.719-29.376zm-80.487 256.652h-335.824c-2.547 
                              0-4.778-1.67-5.305-3.972l-52.657-229.998c-.413-1.805.28-3.163.936-3.984.608-.764 1.985-2.045 4.369-2.045h85.503l-3.929 8.997c-3.315 7.592.151 16.434 7.743 19.75 1.954.854 
                              3.99 1.258 5.995 1.258 5.782 0 11.292-3.363 13.754-9l9.173-21.003h204.662l9.173 21.003c2.462 5.638 7.972 9 13.754 9 2.004 0 4.041-.404 5.995-1.258 7.592-3.315 11.059-12.158 
                              7.743-19.75l-3.929-8.997h85.503c2.384 0 3.761 1.281 4.369 2.045.655.822 1.349 2.18.936 3.983l-52.657 230c-.528 2.301-2.76 3.971-5.307 3.971z"/>
                            <path d="m166 266.717c-8.284 0-15 6.716-15 15v110c0 8.284 6.716 15 15 15s15-6.716 15-15v-110c0-8.284-6.715-15-15-15z"/>
                            <path d="m256 266.717c-8.284 0-15 6.716-15 15v110c0 8.284 6.716 15 15 15s15-6.716 15-15v-110c0-8.284-6.716-15-15-15z"/>
                            <path d="m346 266.717c-8.284 0-15 6.716-15 15v110c0 8.284 6.716 15 15 15s15-6.716 15-15v-110c-.001-8.284-6.716-15-15-15z"/>
                         </svg>
                    </a>     
                     <p class="support">[support]</p>  
                 </div> 
            </li>
        ';
    }

    public static function removeReply()
    {
        return
            '<span class="remove" id="remove-reply" data-ticket_id="[ticket-id]" >
                حذف
            </span>
        ';
    }

    public static function forwardLog()
    {
        return
            '<div class="forwards" title="تغییرات واحد"> 
                <span class="forwards-opener dashicons dashicons-bell" data-ticket-id="[ticket-id]"></span>
                <div class="forwards-list"  style="display: none">
                    <div class="root">
                        [items]
                    </div>
                </div>
            </div>
        ';
    }


    public static function forwardLogItem()
    {
        return
            '<div class="item">  
                <div class="user">[name]</div>
                <div class="change">
                   <span class="old-dest">[destination-from]</span> 
                   <span> => </span>
                   <span class="new-dest">[destination-to]</span>
                 </div> 
                <time>[time]</time>
            </div>
        ';
    }


    public static function assignedList()
    {
        return
            '<div class="assign" title="لیست اختصاص داده ها"> 
                <span class="assigned-opener dashicons dashicons-groups" data-ticket-id="[ticket-id]"></span>
                <div class="assign-list list-con" style="display: none">
                    <div class="root">
                        [items]
                    </div>
                </div>
            </div> 
        ';
    }


    public static function assignedListItem()
    {
        return
            '<div class="item"> 
                <div class="change">
                   <span class="user">[from-user]</span> 
                   <span> => </span>
                   <span class="new-assign">[to-user]</span>
                </div> 
                <time>[time]</time>
            </div>
        ';
    }


    public static function templateIcon()
    {
        return '<button class="ck ck-button " id="ck-load-template" type="button" tabindex="-1" aria-pressed="false"> <svg width="20px"  x="0px" y="0px" viewBox="0 0 415.998 415.998" > <g> <g> <circle cx="208.239" cy="48" r="12"/> </g> </g> <g> <g> <path d="M367.998,95.999c0-17.673-14.326-32-31.999-32h-44.424c-5.926-6.583-13.538-11.62-22.284-14.136 c-7.367-2.118-13.037-7.788-15.156-15.155C248.37,14.663,229.897,0,207.998,0c-21.898,0-40.37,14.663-46.134,34.706 c-2.122,7.376-7.806,13.039-15.182,15.164c-8.736,2.518-16.341,7.55-22.262,14.129H79.999c-17.674,0-32,14.327-32,32v287.999 c0,17.673,14.326,32,32,32c73.466,0,163.758,0,256,0c17.674,0,32-14.327,32-32C367.999,293.119,367.998,206.096,367.998,95.999z M128,95.742c0.11-14.066,9.614-26.606,23.112-30.496c12.71-3.662,22.477-13.426,26.127-26.116 C181.157,25.51,193.805,16,207.998,16c14.194,0,26.842,9.51,30.758,23.13c3.652,12.698,13.413,22.459,26.111,26.11 c13.618,3.917,23.13,16.566,23.13,30.758v16H128V95.742z M335.999,399.998c-85.455,0-170.77,0-256,0c-8.823,0-16-7.178-16-16 V95.999c0-8.822,7.177-16,16-16h34.742c-1.73,4.892-2.698,10.143-2.74,15.617v32.383h191.998v-32c0-5.615-0.992-10.991-2.764-16 h34.764c8.822,0,15.999,7.178,15.999,16c0,45.743-0.001,260.254,0.002,287.999C351.999,392.82,344.822,399.998,335.999,399.998z"/> </g> </g> <g> <g> <polygon points="274.51,194.508 178.343,290.674 135.955,248.286 124.642,259.6 178.343,313.302 285.823,205.822"/> </g> </g> </svg> <span class="ck ck-tooltip ck-tooltip_s"> <span class="ck ck-tooltip__text"> متن های آماده </span> </span> <span class="ck ck-button__label" id="ck-editor__aria-label_e7b9c2468e70c9c6027aa6c1bbefcd824"> متن های آماده </span> </button> <span class="ck ck-toolbar__separator"></span>';

    }


    public static function fileIcon( $which )
    {
        $icons = [];
        $icons['zip'] =
            '<svg  x="0px" y="0px" viewBox="0 0 512 512"  >
                <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
                <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
                <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
                <path style="fill:#84BD5A;" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16
                    V416z"/>
                <g>
                    <path style="fill:#FFFFFF;" d="M132.64,384c-8.064,0-11.264-7.792-6.656-13.296l45.552-60.512h-37.76
                        c-11.12,0-10.224-15.712,0-15.712h51.568c9.712,0,12.528,9.184,5.632,16.624l-43.632,56.656h41.584
                        c10.24,0,11.52,16.256-1.008,16.256h-55.28V384z"/>
                    <path style="fill:#FFFFFF;" d="M212.048,303.152c0-10.496,16.896-10.88,16.896,0v73.04c0,10.608-16.896,10.88-16.896,0V303.152z"/>
                    <path style="fill:#FFFFFF;" d="M251.616,303.152c0-4.224,3.328-8.832,8.704-8.832h29.552c16.64,0,31.616,11.136,31.616,32.48
                        c0,20.224-14.976,31.488-31.616,31.488h-21.36v16.896c0,5.632-3.584,8.816-8.192,8.816c-4.224,0-8.704-3.184-8.704-8.816
                        L251.616,303.152L251.616,303.152z M268.496,310.432v31.872h21.36c8.576,0,15.36-7.568,15.36-15.504
                        c0-8.944-6.784-16.368-15.36-16.368H268.496z"/>
                </g>
                <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            </svg>';

        $icons['jpeg'] =
            '<svg    x="0px" y="0px" viewBox="0 0 512 512"  >
                <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
                <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
                <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
                <path style="fill:#50BEE8;" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16
                    V416z"/>
                <g>
                    <path style="fill:#FFFFFF;" d="M141.968,303.152c0-10.752,16.896-10.752,16.896,0v50.528c0,20.096-9.6,32.256-31.728,32.256
                        c-10.88,0-19.952-2.96-27.888-13.184c-6.528-7.808,5.76-19.056,12.416-10.88c5.376,6.656,11.136,8.192,16.752,7.936
                        c7.152-0.256,13.44-3.472,13.568-16.128v-50.528H141.968z"/>
                    <path style="fill:#FFFFFF;" d="M181.344,303.152c0-4.224,3.328-8.832,8.704-8.832H219.6c16.64,0,31.616,11.136,31.616,32.48
                        c0,20.224-14.976,31.488-31.616,31.488h-21.36v16.896c0,5.632-3.584,8.816-8.192,8.816c-4.224,0-8.704-3.184-8.704-8.816
                        L181.344,303.152L181.344,303.152z M198.24,310.432v31.872h21.36c8.576,0,15.36-7.568,15.36-15.504
                        c0-8.944-6.784-16.368-15.36-16.368H198.24z"/>
                    <path style="fill:#FFFFFF;" d="M342.576,374.16c-9.088,7.552-20.224,10.752-31.472,10.752c-26.88,0-45.936-15.344-45.936-45.808
                        c0-25.824,20.096-45.904,47.072-45.904c10.112,0,21.232,3.44,29.168,11.248c7.792,7.664-3.456,19.056-11.12,12.288
                        c-4.736-4.608-11.392-8.064-18.048-8.064c-15.472,0-30.432,12.4-30.432,30.432c0,18.944,12.528,30.464,29.296,30.464
                        c7.792,0,14.448-2.32,19.184-5.76V348.08h-19.184c-11.392,0-10.24-15.616,0-15.616h25.584c4.736,0,9.072,3.584,9.072,7.552v27.248
                        C345.76,369.568,344.752,371.712,342.576,374.16z"/>
                </g>
                <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            </svg>';

        $icons['png'] =
            '<svg   x="0px" y="0px" viewBox="0 0 512 512"   0 512 512;" >
                <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
                <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
                <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
                <path style="fill:#A066AA;" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16
                    V416z"/>
                <g>
                    <path style="fill:#FFFFFF;" d="M92.816,303.152c0-4.224,3.312-8.848,8.688-8.848h29.568c16.624,0,31.6,11.136,31.6,32.496
                        c0,20.224-14.976,31.472-31.6,31.472H109.68v16.896c0,5.648-3.552,8.832-8.176,8.832c-4.224,0-8.688-3.184-8.688-8.832
                        C92.816,375.168,92.816,303.152,92.816,303.152z M109.68,310.432v31.856h21.376c8.56,0,15.344-7.552,15.344-15.488
                        c0-8.96-6.784-16.368-15.344-16.368L109.68,310.432L109.68,310.432z"/>
                    <path style="fill:#FFFFFF;" d="M178.976,304.432c0-4.624,1.024-9.088,7.68-9.088c4.592,0,5.632,1.152,9.072,4.464l42.336,52.976
                        v-49.632c0-4.224,3.696-8.848,8.064-8.848c4.608,0,9.072,4.624,9.072,8.848v72.016c0,5.648-3.456,7.792-6.784,8.832
                        c-4.464,0-6.656-1.024-10.352-4.464l-42.336-53.744v49.392c0,5.648-3.456,8.832-8.064,8.832s-8.704-3.184-8.704-8.832v-70.752
                        H178.976z"/>
                    <path style="fill:#FFFFFF;" d="M351.44,374.16c-9.088,7.536-20.224,10.752-31.472,10.752c-26.88,0-45.936-15.36-45.936-45.808
                        c0-25.84,20.096-45.92,47.072-45.92c10.112,0,21.232,3.456,29.168,11.264c7.808,7.664-3.456,19.056-11.12,12.288
                        c-4.736-4.624-11.392-8.064-18.048-8.064c-15.472,0-30.432,12.4-30.432,30.432c0,18.944,12.528,30.448,29.296,30.448
                        c7.792,0,14.448-2.304,19.184-5.76V348.08h-19.184c-11.392,0-10.24-15.632,0-15.632h25.584c4.736,0,9.072,3.6,9.072,7.568v27.248
                        C354.624,369.552,353.616,371.712,351.44,374.16z"/>
                </g>
                <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            </svg>';


        $icons['pdf'] =
            '<svg  x="0px" y="0px" viewBox="0 0 512 512"  >
                <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
                <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
                <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
                <path style="fill:#F15642;" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16
                    V416z"/>
                <g>
                    <path style="fill:#FFFFFF;" d="M101.744,303.152c0-4.224,3.328-8.832,8.688-8.832h29.552c16.64,0,31.616,11.136,31.616,32.48
                        c0,20.224-14.976,31.488-31.616,31.488h-21.36v16.896c0,5.632-3.584,8.816-8.192,8.816c-4.224,0-8.688-3.184-8.688-8.816V303.152z
                         M118.624,310.432v31.872h21.36c8.576,0,15.36-7.568,15.36-15.504c0-8.944-6.784-16.368-15.36-16.368H118.624z"/>
                    <path style="fill:#FFFFFF;" d="M196.656,384c-4.224,0-8.832-2.304-8.832-7.92v-72.672c0-4.592,4.608-7.936,8.832-7.936h29.296
                        c58.464,0,57.184,88.528,1.152,88.528H196.656z M204.72,311.088V368.4h21.232c34.544,0,36.08-57.312,0-57.312H204.72z"/>
                    <path style="fill:#FFFFFF;" d="M303.872,312.112v20.336h32.624c4.608,0,9.216,4.608,9.216,9.072c0,4.224-4.608,7.68-9.216,7.68
                        h-32.624v26.864c0,4.48-3.184,7.92-7.664,7.92c-5.632,0-9.072-3.44-9.072-7.92v-72.672c0-4.592,3.456-7.936,9.072-7.936h44.912
                        c5.632,0,8.96,3.344,8.96,7.936c0,4.096-3.328,8.704-8.96,8.704h-37.248V312.112z"/>
                </g>
                <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            </svg>';

        $icons['mp3'] =
            '<svg  x="0px" y="0px" viewBox="0 0 512 512"  >
                <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.616,14.4,32,32,32h320c17.6,0,32-14.384,32-32V128L352,0H128z
                    "/>
                <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
                <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
                <path style="fill:#50BEE8;" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16
                    V416z"/>
                <g>
                    <path style="fill:#FFFFFF;" d="M117.184,327.84v47.344c0,5.632-4.592,8.832-9.216,8.832c-4.096,0-7.664-3.2-7.664-8.832v-72.032
                        c0-6.64,5.632-8.832,7.664-8.832c3.712,0,5.888,2.192,8.064,4.608l28.16,38l29.152-39.408c4.24-5.248,14.592-3.2,14.592,5.632
                        v72.032c0,5.632-3.6,8.832-7.68,8.832c-4.592,0-8.192-3.2-8.192-8.832V327.84l-21.232,26.88c-4.592,5.632-10.352,5.632-14.576,0
                        L117.184,327.84z"/>
                    <path style="fill:#FFFFFF;" d="M210.288,303.152c0-4.224,3.328-8.832,8.704-8.832h29.552c16.64,0,31.616,11.136,31.616,32.496
                        c0,20.224-14.976,31.472-31.616,31.472h-21.36v16.896c0,5.632-3.584,8.832-8.192,8.832c-4.224,0-8.704-3.2-8.704-8.832V303.152z
                         M227.168,310.448v31.856h21.36c8.576,0,15.36-7.552,15.36-15.488c0-8.96-6.784-16.368-15.36-16.368L227.168,310.448
                        L227.168,310.448z"/>
                    <path style="fill:#FFFFFF;" d="M322.064,311.472h-21.872c-10.736,0-10.096-15.984,0-15.984h39.152c7.792,0,11.376,8.96,5.632,14.72
                        l-21.232,19.824c15.616-1.152,27.888,10.48,27.888,24.816c0,15.728-11.136,29.168-34.544,29.168
                        c-10.24,0-20.336-4.224-26.224-13.44c-6.144-9.072,7.024-17.776,13.936-8.832c3.328,4.352,8.704,6.528,14.448,6.528
                        c7.808,0,15.488-3.328,15.488-13.44c0-13.296-16.256-11.248-25.072-10.352c-10.752,2.048-13.936-9.6-7.664-14.448L322.064,311.472z
                        "/>
                </g>
                <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
                </svg>';

        $icons['txt'] =
            '<svg   x="0px" y="0px" viewBox="0 0 512 512" >
                <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
                <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
                <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
                <path style="fill:#576D7E;" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16
                    V416z"/>
                <g>
                    <path style="fill:#FFFFFF;" d="M132.784,311.472H110.4c-11.136,0-11.136-16.368,0-16.368h60.512c11.392,0,11.392,16.368,0,16.368
                        h-21.248v64.592c0,11.12-16.896,11.392-16.896,0v-64.592H132.784z"/>
                    <path style="fill:#FFFFFF;" d="M224.416,326.176l22.272-27.888c6.656-8.688,19.568,2.432,12.288,10.752
                        c-7.68,9.088-15.728,18.944-23.424,29.024l26.112,32.496c7.024,9.6-7.04,18.816-13.952,9.344l-23.536-30.192l-23.152,30.832
                        c-6.528,9.328-20.992-1.152-13.68-9.856l25.696-32.624c-8.048-10.096-15.856-19.936-23.664-29.024
                        c-8.064-9.6,6.912-19.44,12.784-10.48L224.416,326.176z"/>
                    <path style="fill:#FFFFFF;" d="M298.288,311.472H275.92c-11.136,0-11.136-16.368,0-16.368h60.496c11.392,0,11.392,16.368,0,16.368
                        h-21.232v64.592c0,11.12-16.896,11.392-16.896,0V311.472z"/>
                </g>
                <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            </svg>';

        $icons['raw'] =
            '<svg   x="0px" y="0px" viewBox="0 0 512 512" >
                <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
                <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
                <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
                <path style="fill:#576D7E;" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16
                    V416z"/>
                <g>
                    <path style="fill:#FFFFFF;" d="M94.912,375.68c0,11.12-17.024,11.504-17.024,0.256V303.28c0-4.48,3.472-7.808,7.68-7.808H119.6
                        c32.48,0,39.136,43.504,12.016,54.368l17.008,20.72c6.656,9.856-6.64,19.312-14.336,9.6l-19.312-27.632H94.912V375.68z
                         M94.912,337.808H119.6c16.624,0,17.664-26.864,0-26.864H94.912V337.808z"/>
                    <path style="fill:#FFFFFF;" d="M162.624,384c-4.096-2.32-6.656-6.912-4.096-12.288l36.704-71.76c3.456-6.784,12.672-7.04,15.872,0
                        l36.064,71.76c5.248,9.968-10.24,17.904-14.832,7.936l-5.648-11.264h-47.2l-5.504,11.264C171.952,384,167.216,384.912,162.624,384z
                         M217.632,351.504l-14.448-31.6l-15.728,31.6H217.632z"/>
                    <path style="fill:#FFFFFF;" d="M341.248,353.424l19.056-52.704c3.84-10.352,19.312-5.504,15.488,5.632l-25.328,68.704
                        c-2.32,7.296-4.48,9.472-8.832,9.472c-4.608,0-6.016-2.832-8.576-7.424L310.8,326.576l-21.248,49.76
                        c-2.304,5.36-4.464,8.432-9.072,8.432c-4.464,0-6.784-3.072-8.832-8.704l-24.816-69.712c-3.84-11.504,12.4-15.728,15.728-5.632
                        l18.944,52.704l22.64-52.704c3.056-7.808,11.12-8.192,14.448-0.368L341.248,353.424z"/>
                </g>
                <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            </svg>';
        return isset( $icons[$which] ) ? $icons[$which] : '';
    }


    public static function ratingReadOnly( $ratingStatus ,$score )
    {
        if( $ratingStatus == 1 && $score != 0 ){
            return
               '<div class="view-rating">
                    <span class="dashicons dashicons-star-'.Functions::ratingChecked( $score ,1 ,'filled' ,'empty').'"></span>
                    <span class="dashicons dashicons-star-'.Functions::ratingChecked( $score ,2 ,'filled' ,'empty').'"></span>
                    <span class="dashicons dashicons-star-'.Functions::ratingChecked( $score ,3 ,'filled' ,'empty').'"></span>
                    <span class="dashicons dashicons-star-'.Functions::ratingChecked( $score ,4 ,'filled' ,'empty').'"></span>
                    <span class="dashicons dashicons-star-'.Functions::ratingChecked( $score ,5 ,'filled' ,'empty').'"></span>
               </div>';
        }
        return '';
    }


    public static function ratingStars()
    {
        return
            '<div class="rating-con">
                <div class="rating-root" data-ticket-id="[ticket-id]" data-parent-id="[parent-id]">
                    <i class="dashicons dashicons-star-empty" data-rate="1"></i>
                    <i class="dashicons dashicons-star-empty" data-rate="2"></i>
                    <i class="dashicons dashicons-star-empty" data-rate="3"></i>
                    <i class="dashicons dashicons-star-empty" data-rate="4"></i>
                    <i class="dashicons dashicons-star-empty" data-rate="5"></i>
                </div> 
            </div>
        ';
    }




}


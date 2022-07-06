<?php


namespace HWP_Ticket\core\includes;





class AnalyticsData
{

    protected static $_instance = null;

    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static $until;

    public static $student_monthly;
    public static $monthly_count;
    public static $responder_monthly;
    public static $data;
    public static $destinations;
    public static $courses;


    public function __construct()
    {
        date_default_timezone_set('Asia/Tehran');
        self::$until = date('Y-m-d H:i:s', strtotime("-1 month"));
        self::$destinations = ['tango_license' => [], 'tango_support' => [], 'tango_other' => [], 'tango_sale' => []];

        self::allData();
        self::monthlyKeys();
        self::coursesKeys();
    }

    public static function allData()
    {
        global $wpdb;
        $table = Database::get_instance()::$tickets;

        $results = $wpdb->get_results(
                    $wpdb->prepare("SELECT * FROM $table WHERE created_date >= '%s' AND is_public <> 1;",self::$until)
        );
        self::$data = $results;
    }


    public static function unseenCount()
    {
        $unseen = [];

        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if ( $item->created_date >= date('Y-m-d H:i:s', strtotime('-7 days') )) {
                    if (empty($item->parent_ticket)) {
                        if ( $item->status === 'first' && empty($item->tags) ) {
                            $unseen [] = $item;
                        }
                    }
                }
            }
            return count($unseen);
        }
        return 0;
    }


    public static function nResponseCount()
    {
        $n_response = [];
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if ( $item->created_date >= date('Y-m-d H:i:s', strtotime('-7 days') )) {
                    if (empty($item->parent_ticket)) {
                        if ( $item->status === 'first' && !empty($item->tags ) ) {
                            $n_response [] = $item;
                        }
                    }
                }
            }
            return count($n_response);
        }
        return 0;
    }


    public static function openedCount()
    {
        $opened = [];
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if ( $item->created_date >= date('Y-m-d H:i:s', strtotime('-7 days') )) {
                    if (empty($item->parent_ticket)) {
                        if ( $item->status == 'in_progress' || $item->status == 'open' ) {
                            if ( !empty( self::hasChild( $item->id ) ) ){
                                $opened [] = $item;
                            }
                        }
                    }
                }
            }
            return count($opened);
        }
        return 0;
    }

    public static function closedCount()
    {
        $closed = [];
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if ( $item->created_date >= date('Y-m-d H:i:s', strtotime('-7 days') )) {
                    if (empty($item->parent_ticket)) {
                        if ($item->status == 'closed' || $item->status == 'finished' || $item->status == 'answered') {
                            $closed [] = $item;
                        }
                    }
                }
            }
            return count($closed);
        }
        return 0;
    }

    // check open ticket
    public static function hasChild( $parentID = null )
    {
        $child = [];
        if (!empty(self::$data)) {
            foreach (self::$data as $item ) {
                if ( $item->parent_ticket  == $parentID ) {
                    $child = $item;
                }
            }
        }
        return $child;
    }

    public static function masterTicketsCount()
    {
        $writer = [];
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if (empty($item->parent_ticket)) {
                    if ('master' === Permissions::whoIs($item->creator)) {
                        $writer[] = $item->creator;
                    }
                }
            }
            return count(array_unique($writer));
        }
        return null;
    }

    public static function supportTicketsCount()
    {
        $writer = [];
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if (empty($item->parent_ticket)) {
                    if ('support' === Permissions::whoIs($item->creator)) {
                        $writer[] = $item->creator;
                    }
                }
            }
            return count(array_unique($writer));
        }
        return null;
    }


    public static function studentCreatorList()
    {
        $writer = [];
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if ( !isset( $item->parent_ticket ) ){
                    if ( isset( $item->creator ) ){
                        $permission = Permissions::whoIs( $item->creator );
                        if ( 'user' == $permission || 'student' == $permission ) {
                            if ( isset(  $writer[$item->creator] ) ){
                                $writer[$item->creator] = (int) $writer[$item->creator] + 1;
                            }else{
                                $writer[$item->creator] = 1;
                            }
                        }
                    }
                    if( isset( $writer[$item->creator] )){
                        $writer[$item->creator]++;
                    }else{
                        $writer[$item->creator] = 1;
                    }
                }
            }
            return $writer;
        }
        return null;
    }

    public static function supportReplierList( $today = false )
    {
        $writer = [];
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if ( !Functions::isFill( $item ,'parent_ticket' ) )continue;
                if (!Functions::isFill($item,'creator') || $item->creator==0) continue;
                    $p_ticket = self::parentTicket( $item->parent_ticket );
                if (is_null($p_ticket)) continue;
                if (  $item->creator != $p_ticket->creator ) {
                    if ($item->main_object !== 'comment') {
                        if ( !$today || self::isToday( $item->created_date ) ) {
                            if (!Functions::isFill($writer,$item->creator )){
                                $writer[$item->creator] = 1;
                            }else{
                                $writer[$item->creator]++;
                            }
                        }
                    }
                }
            }
            return $writer;
        }
        return null;
    }


    public static function isToday( $createdDate )
    {
        if (!empty( $createdDate ) ) {
            $created_day = date('d', strtotime( $createdDate ) );
            $today_day   = date('d' );
            if( $created_day == $today_day ){
                return true;
            }
        }
        return false;
    }


    public static function parentTicket(int $parent_id)
    {
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if ($item->id == $parent_id) {
                    return (object)$item;
                }
            }
        }
        return null;
    }


    public static function studentMonthList()
    {

        $list = self::$student_monthly;
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if ($item->created_date >= date('Y-m-d H:i:s', strtotime("-30 days"))) {
                    $list[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0]) ) ] [] = $item;
                }
            }
            return $list;
        }
        return null;
    }


    public static function monthlyKeys()
    {
        $days       = 30;
        $keys       = [];
        $keys_count = [];

        $keys[ date_i18n('Y-m-d' , strtotime("now") ) ] = [];
        $keys[ date_i18n('Y-m-d' , strtotime("-1 day") ) ] = [];

        $keys_count[ date_i18n('Y-m-d' , strtotime("now") ) ] = 0;
        $keys_count[ date_i18n('Y-m-d' , strtotime("-1 day") ) ] = 0;
        for ($i = 2; $i <= $days; $i++) {
            $keys       [ date_i18n('Y-m-d' , strtotime('-' . $i . ' days') ) ] = [];
            $keys_count [ date_i18n('Y-m-d' , strtotime('-' . $i . ' days') ) ] = 0;
        }
        self::$student_monthly   = $keys;
        self::$monthly_count     = $keys_count;
        self::$responder_monthly = $keys;
    }


    public static function coursesKeys()
    {
        $keys = [];
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if (empty($item->parent_ticket)) {
                    $keys[] = $item->main_object;
                }
            }
            return self::$courses = array_unique($keys);
        }
        return null;
    }

    public static function destinations()
    {

        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if (empty($item->parent_ticket)) {
                    self::$destinations[$item->destination] = $item;
                }
            }
            return self::$destinations;
        }
        return null;
    }


    public static function courses()
    {

        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if (empty($item->parent_ticket)) {
                    self::$courses[$item->main_object][] = $item;
                }
            }
            return self::$courses;
        }
        return null;
    }


    public static function destinationMonthly()
    {
        $list  = self::studentMonthList();
        $sorts = [];

        if ( !empty( $list ) ){
            foreach ($list as $keys => $value) {
                $list[$keys] = ['tango_license' => 0, 'tango_support' => 0, 'tango_other' => 0, 'tango_sale' => 0];
            }
            if (!empty(self::studentMonthList())) {
                foreach (self::studentMonthList() as $items) {
                    foreach ($items as $item) {
                        if ( empty( Permissions::isSupporterDashboard( $item->creator ) ) ) {
                            if (empty($item->parent_ticket)) {
                                if (!empty($item) && !empty($item->destination)) {
                                    $list[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0]) ) ] [$item->destination]++;
                                }
                            }
                        }
                    }
                }
            }

            if ( empty( $list ) ) return $sorts;

            foreach ($list as $sort) {
                $sorts['first'] [] = $sort['tango_license'];
                $sorts['second'][] = $sort['tango_support'];
                $sorts['third'] [] = $sort['tango_other'];
                $sorts['fourth'][] = $sort['tango_sale'];
            }
            $sorts['keys'][] = array_keys($list);

        }
        return $sorts;

    }



    public static function openTicketLists()
    {
        $list = self::studentMonthList();
        $data = [];
        if (!empty( $list )) {
            foreach ( $list as $items) {
                foreach ($items as $item ) {
                    if (empty( $item->parent_ticket )) {
                        if ( $item->status == 'open' || $item->status == 'first' || $item->status == 'in_progress' ) {
                            $data[$item->main_object][] = $item;
                        }
                    }
                }
            }
        }
        return $data;
    }




//firstResponse
    public static function responseAverageMonthly()
    {

        $list = self::studentMonthList();
        $sorts = [];

        if ( !empty( $list ) ){
            foreach ($list as $keys => $value) {
                $list[$keys] = ['support' => [], 'master' => []];
            }
            if (!empty(self::studentMonthList())) {
                foreach (self::studentMonthList() as $items) {
                    foreach ($items as $item) {
                        if ( !empty($item) && empty($item->parent_ticket) ) {
                            $first = self::firstResponse($item);
                            if (!empty($first)) {
                                $f_creator = $first->creator;
                                $f_created = $first->created_date;
                                if ('master' === Permissions::whoIs($f_creator)) {
                                    $to_time = strtotime($f_created);
                                    $from_time = strtotime($item->created_date);

                                    $diff = round(abs($to_time - $from_time) / 60, 0);
                                    $list[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ]['master'][]  =  $diff;

                                } elseif ('support' === Permissions::whoIs($f_creator) || 'admin' === Permissions::whoIs($f_creator) ) {
                                    $to_time = strtotime($f_created);
                                    $from_time = strtotime($item->created_date);
                                    $diff = round(abs($to_time - $from_time) / 60, 0);
                                    $list[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ]['support'][] =   $diff;
                                }
                            }
                        }
                    }
                }
            }



            if ( empty( $list ) ) return $sorts;

            foreach ($list as $sort) {
                if (!empty($sort['support'])) {
                    $sorts['support'] [] = round( (array_sum($sort['support']) / count($sort['support'])) );
                } else {
                    $sorts['support'] [] = 0;
                }
                if (!empty($sort['master'])) {
                    $sorts['master'] [] = round( array_sum($sort['master']) / count($sort['master']) );
                } else {
                    $sorts['master'] [] = 0;
                }
            }
            $sorts['keys'][] = array_keys($list);
        }

        return $sorts;

    }


    public static function firstResponse($parent)
    {
        if (!empty(self::$data)) {
            foreach (self::$data as $item) {
                if (!empty($item->parent_ticket)) {
                    if ($item->parent_ticket === $parent->id) {
                        if ($item->creator != $parent->creator) {
                            return $item;
                        }
                    }
                }
            }
        }
        return null;
    }

    public static function mostStudentCreator()
    {
        $users = [];
        $creatorList = self::studentCreatorList();
        if ( !empty( $creatorList ) ){
            arsort($creatorList);
            $i = 0;

            foreach ($creatorList as $key => $value) {
                if ( $i >= 10 ) break;
                $user = get_user_by('ID', $key);
                $users[] = [
                    'avatar' => Functions::get_avatar( $key, 50, true),
                    'name'   => Functions::indexChecker( $user ,'first_name') .' '.Functions::indexChecker( $user ,'last_name' ) ,
                    'phone'  => Users::getMobile( $key ) ,
                    'id'     => $key ,
                    'count'  => $value
                ];
                $i++;
            }
        }
        return $users;
    }


    public static function mostSupportReplier( $today = false )
    {
        $users = [];
        $creatorList = self::supportReplierList( $today );
        if ( !empty( $creatorList ) ){
            arsort($creatorList);
            foreach ($creatorList as $key => $value) {
                $user = get_user_by('id', $key);
                $users[] = [
                    'avatar' => Functions::get_avatar($key, 50, true),
                    'name'   => Functions::getFillValue($user->data,'display_name'),
                    'phone'  => Users::getMobile( $key ) ,
                    'id'     => $key ,
                    'count'  => $value
                ];
            }
        }
        return $users;
    }


    public static function replyMonthlySum()
    {
        $ticket = self::$monthly_count;
        $reply  = self::$monthly_count;
        $lists  = self::$data;
        if (!empty( $lists )) {
            foreach ( $lists as $item ) {
                $whoIs = Permissions::whoIs( $item->creator );

                if ( !empty( $item->parent_ticket) ){
                    if ( 'student' !== $whoIs || 'user' !== $whoIs ) {
                        if ( $item->main_object !== 'comment'){
                            if( isset( $reply[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ] )){
                                $reply[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ] =
                                    $reply[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ] + 1;
                            }else{
                                $reply[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ] = 1;
                            }
                        }
                    }
                }

                if ( empty( $item->parent_ticket ) ){
                    if ( 'student' === $whoIs || 'user' === $whoIs ) {
                        if( isset( $ticket[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ] )){
                            $ticket[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ]   =
                                $ticket[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ] + 1 ;
                        }else{
                            $ticket[ date_i18n('Y-m-d' , strtotime(explode(' ', $item->created_date)[0] ) ) ] = 1;
                        }
                    }
                }
            }
        }

        return [
            'ticket' =>  $ticket ,
            'reply'  =>  $reply
        ];

    }


    public static function destinationPercent()
    {
        $destinationPercent = ['tango_license' => 0, 'tango_support' => 0, 'tango_other' => 0, 'tango_sale' => 0];

        if (!empty(self::studentMonthList())) {
            foreach (self::studentMonthList() as $items) {
                foreach ($items as $item) {
                    if (empty($item->parent_ticket)) {
                        if ( empty( Permissions::isSupporterDashboard(  $item->creator ) ) ) {
                            $destinationPercent[$item->destination]++;
                        }
                    }
                }
            }
        }
        arsort($destinationPercent);
        return $destinationPercent;
    }


    public static function courseStatistic(){
        $courseStatistic =[];
        if (!empty(self::studentMonthList())) {
            foreach (self::studentMonthList() as $items) {
                foreach ($items as $item) {

                    if ( !isset( $item->parent_ticket ) && is_numeric( $item->main_object )  ) {
                        if ( isset( $courseStatistic[$item->main_object]  ) ){
                            $courseStatistic[$item->main_object] =
                                (int) $courseStatistic[$item->main_object]+1;
                        }else{
                            $courseStatistic[$item->main_object] = 1;
                        }
                    }
                }

            }
        }

        arsort($courseStatistic );

        $output  =[];
        $i=0;
        foreach ( $courseStatistic as $key => $value ){
            if ( $i >= 5 ) break;
            $name = 'بدون دوره';

            if ( $key !== 0 ){
                if ( wc_get_product( $key ) ){
                    $name = wc_get_product( $key )->get_title();
                }
            }
            if (empty( $value )) $value =1;

            $output[] = ['name' => $name , 'y' => $value ] ;
            $i++;
        }
        return  $output;
    }


}




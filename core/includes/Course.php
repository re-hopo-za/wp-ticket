<?php


namespace HWP_Ticket\core\includes;




class Course
{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct(){
        add_action( 'add_meta_boxes', [ $this, 'add' ] );
        add_action( 'save_post'     , [ $this, 'save' ]);
    }


    public function add() {
        $screens = [ 'product' ];
        foreach ( $screens as $screen ) {
            add_meta_box(
                'hamfy_meta_box_id',
                __('دسترسی های تیکت'),
                [ self::class, 'html' ] ,
                $screen ,
                'side'
            );
        }
    }


    public static function html( $post ) {
        $support = get_post_meta($post->ID, '_support_duration', true );
        $license = get_post_meta($post->ID, '_has_license', true );
        $sales   = get_post_meta($post->ID, '_has_sales', true );
        wp_nonce_field('hamfy_support_save','_hamfy_support_save');
        ?>
        <div class="hamfy-meta-box">
            <div class="hamfy-support">
                <label for="hamfy_support_days_field"><?php esc_html_e( 'تعیین تعداد روز پشتیبانی', 'hamyarNotify' ) ?></label>
                <input type="number" name="hamfy_support_days_field" placeholder="<?php echo  $support; ?>" id="hamfy_support_days_field" value="<?php echo $support; ?>" >
            </div>
            <div class="hamfy-license">
                <label for="hamfy_has_license_field"><?php esc_html_e( 'دارای لایسنس می‌باشد', 'hamyarNotify' ) ?></label>
                <input type="checkbox" name="hamfy_has_license_field" id="hamfy_has_license_field" value="yes" <?php checked( $license , 'yes' ,true  ); ?> >
            </div>
            <div class="hamfy-sales">
                <label for="hamfy_has_sales_field"><?php esc_html_e( 'تیکت به بخش فروش', 'hamyarNotify' ) ?></label>
                <input type="checkbox" name="hamfy_has_sales_field" id="hamfy_has_sales_field" value="yes" <?php checked( $sales , 'yes' ,true ); ?> >
            </div>
        </div>

        <style>
            .hamfy-meta-box  {
                padding:10px;
                border:1px solid #eee;
            }
            .hamfy-meta-box>div:nth-child(1)  {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .hamfy-meta-box input[type="number" ]  {
                background-color: #2321;
                width: 40%;
                margin: 5px;
                text-align: center
            }
            .hamfy-meta-box  label{
                display: block;
                font-size: 12px;
                font-weight: bold!important;;
            }
            .hamfy-meta-box>div:not(:nth-child(1)) {
                display: flex;
                padding: 0 10px;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            .hamfy-meta-box>div:not(:nth-child(1)) input{
                position: relative;
                left: 25px;
            }
        </style>
        <?php
    }



    public function save( int $post_id ) {
        if (!isset($_POST['_hamfy_support_save']) || !wp_verify_nonce($_POST['_hamfy_support_save'],'hamfy_support_save')){
            return;
        }
        if (!is_admin()) return;
        if (!current_user_can('edit_post', $post_id )) return;

        if ( Functions::isFill($_POST, 'hamfy_support_days_field')) {
            update_post_meta(
                (int) $post_id,
                '_support_duration',
                (int)$_POST['hamfy_support_days_field']
            );
        }else{
            delete_post_meta(
                (int) $post_id,
                '_support_duration'
            );
        }

        if (Functions::isFill($_POST, 'hamfy_has_license_field')) {
            update_post_meta(
                (int) $post_id,
                '_has_license',
                'yes'
            );
        }else{
            delete_post_meta(
                (int) $post_id,
                '_has_license'
            );
        }

        if (Functions::isFill($_POST, 'hamfy_has_sales_field')) {
            update_post_meta(
                (int) $post_id,
                '_has_sales',
                'yes'
            );
        }else{
            delete_post_meta(
                (int) $post_id,
                '_has_sales'
            );
        }
    }


    public static function getFormListCourse( $user_id ){

        if ( $user_id <= 0 ) return [];
        global $wpdb;

        $_has_license = $wpdb->get_results("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key ='_has_license';" , ARRAY_A );
        $license = array_flip( array_column($_has_license , 'post_id') );

        $_has_sales   = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key ='_has_sales';" , ARRAY_A );
        $sales = array_flip( array_column($_has_sales , 'post_id') );

        do_action('recalculate_support_duration',$user_id );
        $Products  = maybe_unserialize( get_user_meta(  $user_id , '_product_support' , true ) );
        $all_purchase_ids = array_keys( \Lily_Course_Management::get_user_purchased_products($user_id) );

        if ( $all_purchase_ids ){
            $final_des = [];
            foreach ( $all_purchase_ids as $item ){
                $final_p_des = [];
                $ui  = '<p class="support-without"> این دوره بدون پشتیبانی میباشد </p>';;
                $final_p_des[] = [
                    'slug'   => 'tango_other' ,
                    'title'  => 'دیگر'
                ];
                if ( is_array( $license ) && !empty( $license )){
                    if ( array_key_exists( $item , $license ) ){
                        $final_p_des[] = [
                            'slug'   => 'tango_license',
                            'title'  => 'لایسنس'
                        ];
                    }
                }
                if ( is_array( $sales ) && !empty( $sales )) {
                    if (array_key_exists($item, $sales)) {
                        $final_p_des[] = [
                            'slug' => 'tango_sale',
                            'title' => 'مالی'
                        ];
                    }
                }
                if ( is_array( $Products ) && !empty( $Products )) {
                    if ( array_key_exists( $item , $Products )){
                        if ( isset( $Products[$item] ) ){
                            $date  = $Products[$item];
                            $diff  = $date-time();
                            $days  = floor($diff/(60*60*24));
                            if ( $Products[$item] >= strtotime( 'now' ) ){

                                $ui  =
                                    '<p class="remind-days"> پشتیبانی این دوره 
                                         <span class="day"> '.$days.'
                                         روز  
                                         </span> 
                                          باقی مانده است
                                    </p>
                                ';

                                $final_p_des[] = [
                                    'slug'   => 'tango_support',
                                    'title'  => 'پشتیبانی'
                                ];
                            }else{
                                $ui  =
                                    '<p class="support-ended">  پشتیبانی این دوره در   
                                         <span dir="ltr" style="color:#bc0b0b"> ' . jdate('Y-m-d' , $date ). '   </span>  
                                         <span style="padding: 0 5px;" >  اتمام یافته    </span> 
                                    </p>
                                ';
                            }
                        }
                    }
                }
                if ( !empty( wc_get_product( $item ) ) ){
                    $course_name = wc_get_product( (int) $item )->get_name();
                }else{
                    $course_name = 'بدون نام';
                }

                $final_des [] =[
                    $item  => [
                        'destinations' => $final_p_des ,
                        'p_name'       => $course_name ,
                        'support_time' => $ui
                    ]
                ];

            }
            return $final_des;
        }else{
            return FALSE;
        }

    }


    public static function checkCourseNew( $userID ,$course )
    {
        if ( $course == 'empty' ) return true;
        if ( !get_post( $course )  || !wc_get_product( $course ) ){
            Functions::returnResult( 403 , 'Course Not Found' );
        }
        $all = array_keys( \Lily_Course_Management::get_user_purchased_products( $userID ) );
        if ( empty($all) || !in_array( $course ,$all  )  ){
            Functions::returnResult( 403 , 'به این دوره دسترسی ندارید' );
        }
        return true;
    }


    public static function checkCourseReply( $userID ,$parent )
    {
        if ( $parent->creator == $userID || $parent->assign_to == $userID  ) {
                return true;
        }
        Functions::returnResult( 403 , ' متاسفانه به این دوره دسترسی ندارید  ' );
    }


    public static function searchProductsList()
    {
        global $wpdb;
        $keyword    = sanitize_text_field(  $_POST['keyword'] );

        $sub_query = '';
        if ( isset($_POST['exclude'] ) && !empty($_POST['exclude']) && is_array( $_POST['exclude'] ) ){
           $exclude_ids = implode("','" , $_POST['exclude'] );
           $sub_query = " ID NOT IN ('$exclude_ids') AND ";
        }

        $re_query   = $wpdb->prepare("SELECT post_title , ID FROM {$wpdb->posts} WHERE ".$sub_query." post_type = 'product' AND post_status <> 'trash' AND (post_title LIKE '%$keyword%' or ID=%d) ; ",$keyword);
        $products  = $wpdb->get_results($re_query,ARRAY_A);

        $result = [];
        $result[]= [
            'id'    => 'empty',
            'title' => 'بدون دوره'
        ];
        foreach ((array)$products as $product ){
            $result  [] = [
                'id'    => $product['ID'] ,
                'title' => $product['post_title']
            ];
        }
        if ( !empty($result) ){
            wp_send_json(  $result   , 200 );
        }else{
            Functions::returnResult( 204 ,'دوره ای یافت نشد' );
        }
    }


    public static function addTextTemplate( $userID ,$title ,$content ,$course )
    {
        $templates   = maybe_unserialize( get_post_meta( (int) $course ,'hamyar_course_text_template' , true) );

        if ( empty( $templates ) ) $templates=[];
        $templates[ time() ] = [
            'title'   => $title   ,
            'content' => $content ,
            'writer'  => $userID
        ];
        update_post_meta(  $course , 'hamyar_course_text_template' ,$templates );
        return true;
    }



    public static function getTextTemplate(  $courseID = null ){
        $item = [];

        if ( !is_null( $courseID ) ){
            $items = get_post_meta( 1 ,'hamyar_course_text_template' , true );
            foreach ((array) $items as $c ){
                $item[]=[
                    'title'   => Functions::getFillValue( $c, 'title'),
                    'content' => Functions::getFillValue( $c,'content')
                ];
            }
            if ( 'empty' != $courseID ) {
                $items = get_post_meta( $courseID , 'hamyar_course_text_template', true);
                if ( is_array( $items ) ){
                    foreach ( $items as $b ){
                        $item[]=[
                            'title'   => $b['title'] ,
                            'content' => $b['content']
                        ];
                    }
                }
            }

        }else{
            global $wpdb;
            $results = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE meta_key='hamyar_course_text_template';" );

            foreach ( (array) $results  as $result ){
                $item[ $result->post_id  ] = $result->meta_value;
            }

        }
        return $item;
    }


    public function coursesDetailList( $userID ){
        $courses = [];
        $whoIs   = Permissions::whoIs( $userID );
        if (!in_array( $whoIs ,['student' ,'user'] )) return $courses;
        $Products = maybe_unserialize( get_user_meta( $userID, '_product_support', true) );
        if ( !empty( $Products ) ){
            foreach ( $Products as $key => $val ){
                $License   = get_post_meta(  $key , '_has_license' , true );
                $courses[]=[
                    'id'       => $key ,
                    'name'     => wc_get_product($key)->get_title() ,
                    'support'  => date('Y-m-d' , $val ) ,
                    'license'  => $License
                ];
            }
        }
        return null;
    }


    public function getAllPurchase( $userID )
    {
        if ( $userID <= 0 )   return FALSE;

        $all_purchase_ids =  \Lily_Course_Management::get_user_purchased_products( $userID,true );
        $supports = maybe_unserialize( get_user_meta( $userID , '_product_support', true ));
        $final_purchase = [];

        foreach ($all_purchase_ids as $key => $val ) {

            $final_support =  'ندارد' ;
            if ( !empty( wc_get_product( $key ) ) ){
                $name = wc_get_product( (int) $key )->get_name();
            }else{
                $name = 'بدون نام';
            }


            if ( is_array( $supports ) && !empty( $supports )) {
                if ( array_key_exists( $key , $supports )){
                    if ( isset( $supports[$key] ) ){
                        if ( $supports[$key] >= strtotime( 'now' ) ){
                            $final_support = date_i18n( ' Y/m/d  ' , $supports[$key] );
                        }
                    }
                }
            }

            $final_purchase[] = [
                'name'     => $name ,
                'id'       => $key ,
                'support'  => $final_support,
                'order_id' => $val[1]
            ];

        }

        return $final_purchase;
    }



}
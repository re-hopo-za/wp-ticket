<?php


namespace HWP_Ticket\core\ui;


use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\includes\Permissions;

class DashboardUI{


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
        add_action( 'admin_menu', [ $this, 'wph_create_settings' ] );
        add_action( 'admin_init', [ $this, 'wph_setup_sections' ] );
        add_action( 'admin_init', [ $this, 'wph_setup_fields' ] );
    }

 
    public function wph_create_settings()
    { 
        add_menu_page(
            'Ticket Options Page' ,
            'Ticket Options' ,
            'manage_options',
            'TicketOptions' ,
            [$this, 'wph_settings_content'] ,
            'dashicons-megaphone',
            100 
        );

        add_submenu_page(
            'TicketOptions',
            'Ticket Permissions Page',
            'Permissions',
            'manage_options',
            'TicketPermission',
            [ $this  , 'submenuPermission' ]
        );  
    }

    public function wph_settings_content() 
    {
        ?>
            <div class="wrap">
                <h1>Ticket Options</h1>
                <?php settings_errors(); ?>
                <form method="POST" action="options.php">
                    <?php
                    settings_fields( 'TicketOptions' );
                    do_settings_sections( 'TicketOptions' );
                    submit_button();
                    ?>
                </form>
            </div>
        <?php
    }

    public function wph_setup_sections()
    {
        add_settings_section( 'TicketOptions_section', '', array(), 'TicketOptions' );
    }

    public function wph_setup_fields() 
    {
        $fields = [
            [
                'section' => 'TicketOptions_section',
                'label' => 'User token',
                'placeholder' => 'generat user token',
                'id' => 'hwp_ticket_user_hash_complement',
                'type' => 'textarea',
            ],

            [
                'section' => 'TicketOptions_section',
                'label' => 'بستن تیکت ',
                'placeholder' => 'تعیین مقدار زمان ',
                'id' => 'hwp_ticket_change_ticket_status_close',
                'desc' => 'تعیین روز برای بستن تیکت',
                'type' => 'text',
            ],

            [
                'section' => 'TicketOptions_section',
                'label' => 'اتمام تیکت',
                'placeholder' => 'تعیین مقدار زمان ',
                'id' => 'hwp_ticket_change_ticket_status_finish',
                'desc' => 'تعیین روز برای اتمام تیکت',
                'type' => 'text',
            ],
            [
                'section' => 'TicketOptions_section',
                'label' => 'متن بستن',
                'placeholder' => 'توضیحات',
                'id' => 'hwp_ticket_change_ticket_status_text_close',
                'desc' => 'توضیحات برای بستن تیکت ',
                'type' => 'textarea',
            ],
            [
                'section' => 'TicketOptions_section',
                'label' => 'متن اتمام',
                'placeholder' => 'توضیحات',
                'id' => 'hwp_ticket_change_ticket_status_text_finish',
                'desc' => 'توضیحات برای اتمام تیکت ',
                'type' => 'textarea',
            ],
            [
                'section' => 'TicketOptions_section',
                'label' => 'ارسال اعلان به اساتید ',
                'placeholder' => 'تعداد روز',
                'id' => 'hwp_ticket_send_reminder_to_masters',
                'desc' => 'تعیین زمان ارسال اعلان یادآوردی به اساتید دوره',
                'type' => 'text',
            ],

            [
                'section' => 'TicketOptions_section',
                'label' => 'امتیاز دهی',
                'id' => 'hwp_ticket_rating_enable_status',
                'desc' => 'وضعیت نمایش امتیاز به کاربر',
                'type' => 'checkbox',
            ]
        ];
        foreach( $fields as $field ){
            add_settings_field(
                    $field['id'] ,
                    $field['label'] ,
                    [ $this, 'hwp_field_callback' ] ,
                    'TicketOptions' ,
                    $field['section'] ,
                    $field
            );
            register_setting( 'TicketOptions', $field['id'] );
        }
    }


    public function hwp_field_callback( $field )
    {
        $value = get_option( $field['id'] );
        $placeholder = '';
        if ( isset($field['placeholder']) ) {
            $placeholder = $field['placeholder'];
        }
        switch ( $field['type'] ) {
            case 'checkbox':
                printf('<input %s id="%s" name="%s" type="checkbox" value="1">',
                    $value === '1' ? 'checked' : '',
                    $field['id'],
                    $field['id']
                );
                break;

            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>',
                    $field['id'],
                    $placeholder,
                    $value
                );
                break;

            default:
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
                    $field['id'],
                    $field['type'],
                    $placeholder,
                    $value
                );
        }
        if( isset($field['desc']) ) {
            if( $desc = $field['desc'] ) {
                printf( '<p class="description">%s </p>', $desc );
            }
        }
    }



    public function submenuPermission()
    {
        ?>
        <section>
            <div class="permission-table">
                <div class="header-permission">
                    <h2> لیست اشخاص دارای دسترسی </h2>
                    <i class='add-new-user dashicons dashicons-plus'></i>
                </div>
                <table>
                    <thead>
                    <tr class="permission-title">
                        <th>آیدی</th>
                        <th>نام</th>
                        <th>ایمیل</th>
                        <th>نقش</th>
                        <th>تلفن</th> 
                        <th> مجوز</th>
                        <th>تیک اعتماد</th>
                        <th> حذف </th>
                        <th>به روز رسانی </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $all_permissions = Permissions::getAssignedPermissionList();
                    foreach ( $all_permissions as $permission ){
                        $userID = $permission['user_id'];
                        ?>
                        <tr class="permission-body"  id="<?php echo $userID; ?>" >
                            <td id="user_id"><?php echo $userID; ?></td>
                            <td><?php echo $permission['full_name']; ?></td>
                            <td><?php echo $permission['email']; ?></td>
                            <td><?php echo Functions::roleTransition( $permission['role'] ); ?></td>
                            <td><?php echo $permission['phone']; ?></td> 
                            <td class="courses-list <?php echo $userID; ?>">
                                <div class="items-list">
                                <?php
                                $permissions_list = Permissions::getAdminPermissionsList( $userID );
                                if ( !empty( $permissions_list ) ){
                                    foreach ( $permissions_list as $perm_items_key => $perm_items_val  ){
                                        $course_name = get_the_title( $perm_items_key );
                                        $course_name = !empty( $course_name ) ? $course_name : 'بدون نام';
                                        ?>
                                            <div id="<?php echo $perm_items_key; ?>" class="course-permission-list ">
                                                <div class="header">
                                                    <span class="remove--item dashicons dashicons-dismiss"></span>
                                                    <p class="course-name"><?php echo $course_name.' [ '.$perm_items_key ; ?> ] </p>
                                                </div>
                                                <div class="course-permission-list-con">
                                                    <ul>
                                                        <li>
                                                            <input id="tango_support" type="checkbox" <?php echo self::permissionAdminPageCheckbox( $perm_items_val ,'tango_support'); ?> >
                                                            پشتیبانی
                                                        </li>
                                                        <li>
                                                            <input id="tango_license" type="checkbox" <?php echo self::permissionAdminPageCheckbox( $perm_items_val ,'tango_license'); ?> >
                                                            لایسنس
                                                        </li>
                                                        <li>
                                                            <input id="tango_other" type="checkbox" <?php echo self::permissionAdminPageCheckbox( $perm_items_val ,'tango_other'); ?>>
                                                            دیگر
                                                        </li>
                                                        <li>
                                                            <input id="tango_sale" type="checkbox" <?php echo self::permissionAdminPageCheckbox( $perm_items_val ,'tango_sale'); ?> >
                                                            مالی
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        <?php
                                    }
                                }
                                ?>
                                </div>
                                <div><i class="course-add dashicons dashicons-plus"></i></div>

                                <section>
                                    <div class="dynamic-courses-list">
                                        <div>
                                            <h5> انتخاب دوره </h5>
                                            <button class="close"> بستن </button>
                                        </div>
                                        <div class="add-course-form">
                                            <input type="text" placeholder="جستجوی دوره....." name="select-course">
                                            <button data-course_id="<?php echo $userID; ?>" >جستجو</button>
                                        </div>
                                    </div>
                                    <div class="courses-fetched-list">
                                        <ul>
                                            <li id="webmasteran"> گروه وبمستران </li>
                                            <li id="instagram"> گروه اینستاگرام </li>
                                            <li id="empty">بدون دوره </li>
                                            <li id="like_admin"> شبه ادمین </li>
                                        </ul>
                                    </div>
                                </section>
                            </td>
                            <td id="user_trust">
                                <input type="checkbox" value="enable" <?php echo $permission['trust']  ? 'checked' : ''; ?> >
                            </td>
                            <td  data-user_id="<?php echo $userID ?>" >
                                <i class="permission-remove dashicons  dashicons-no"></i>
                            </td>
                            <td  data-user_id="<?php echo $userID ?>" >
                                <i class="permission-update  dashicons dashicons-update-alt"> </i>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <div class="pop-up-permission">
                    <div class="viewer-permission-section">
                        <div>
                            <h3> انتخاب کاربر </h3>
                            <button class="close"> بستن </button>
                        </div>
                        <div>
                            <input type="text" placeholder="جستجوی کاربر....." name="select-user" id="select-user">
                        </div>
                    </div>
                    <div class="search-new-user-permission">
                        <button>جستجو</button>
                    </div>
                    <div class="new-user-list">
                        <ul></ul>
                    </div>
                </div>

            </div>

        </section>
        <?php
    }


    public static function permissionAdminPageCheckbox( $permissions ,$index )
    {
        if ( !empty( $permissions ) && isset( $permissions[$index] ) && $permissions[$index] == 'true'  ){
            return 'checked';
        }
        return '';
    }

}




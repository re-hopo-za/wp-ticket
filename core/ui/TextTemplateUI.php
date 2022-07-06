<?php

namespace HWP_Ticket\core\ui;



use HWP_Ticket\core\includes\Course;
use HWP_Ticket\core\includes\Functions;
use HWP_Ticket\core\includes\Users;

class TextTemplateUI{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public static function templateList()
    {
        ?>
        <div class="template-container">
            <div class="form">
                <form  id="text_template" class="" >

                    <div>
                        <label for="title">عنوان: </label>
                        <br>
                        <input type="text" name="title" id="title" placeholder="عنوان متن" required="required">
                    </div>

                    <div>
                        <label for="title">وضعیت: </label>
                        <label for="status-pub">عمومی: </label>
                        <input type="radio" name="status" value="pub" id="status-pub"  placeholder="وضعیت" checked>
                        <label for="status-priv">خصوصی: </label>
                        <input type="radio" name="status" value="priv" id="status-priv"  placeholder="وضعیت" >
                    </div>

                    <div class="course-root" >
                        <label for="course-root">دوره: </label>
                        <select type="text"  id="course-root"   placeholder="انتخاب دوره">
                        </select>
                    </div>

                    <div class="editor-con">
                        <label for="content"> متن تیکت: </label>
                        <div id="wp-create-template">
                        </div>
                    </div>

                    <div class="clear"></div>
                    <input type="submit" name="submit" value="ارسال" class="pull-left" >

                </form>
            </div>

            <div class="text-templates">
                <?php self::templates(); ?>
            </div>

        </div>
        <?php
    }

    public static function templates()
    {
        ?>
        <div>
            <div class="pub">
                <p>عمومی</p>
                <ul>
                    <?php
                    $pub_items = Course::get_instance()::getTextTemplate();
                    if ( !empty($pub_items)){
                        foreach ( $pub_items as $key => $lists  ){
                            $lists = maybe_unserialize($lists);

                            foreach ( $lists as $ke => $item ){
                                $name = Functions::getUser( (int) $item['writer'] );
                                ?>
                                <li>
                                    <div class="header">
                                        <div class="top">
                                            <div class="title">
                                                <span>عنوان</span>
                                                <p> <?php echo $item['title']; ?></p>
                                            </div>
                                            <div class="writer">
                                                <span>نویسنده</span>
                                                <p  id="<?php echo $ke; ?>" ><?php echo $name->first_name.' '.$name->last_name; ?></p>
                                            </div>
                                        </div>

                                        <div class="bottom">
                                            <div class="course-name">
                                                <span>دوره</span>
                                                <p><?php 
                                                     $get_product=wc_get_product( $key );
                                                    if ( $key != 1  ) {
                                                        if (  $get_product){
                                                            echo   $get_product->get_title(); 
                                                        }else{
                                                            echo 'بدون نام';
                                                        }
                                                    }else{
                                                        echo 'بدون نام';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                            <div class="course-delete">
                                                <?php  if ( current_user_can('administrator') ){ ?>
                                                    <a class="remove"  id="<?php echo $ke; ?>" data-p_id="<?php echo $key ?>">حذف</a>
                                                <?php } ?>
                                                <a class="read-template-pub" id="<?php echo $key; ?>">رویت</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="content" id="<?php echo $key; ?>">
                                        <p><?php echo $item['content']; ?></p>
                                    </div>
                                </li>
                            <?php }  }  }else{ ?>
                                <li>
                                    <p> لیست خالی میباشد </p>
                                </li>
                            <?php } ?>
                </ul>
            </div>
            <div class="priv">
                <div>
                    <p>خصوصی</p>
                    <a class="save-sort"> مرتب کردن </a>
                </div>
                <ul id="template-sortable">
                    <?php
                    $pub_items = Users::get_instance()::getTextTemplate( get_current_user_id() );
                    if ( !empty($pub_items) ){
                        foreach ( $pub_items as $key => $val ){  ?>
                            <li id="<?php echo $key; ?>" >
                                <div class="title">
                                    <h6> عنوان :<span>  <?php echo $val['title'] ?></span></h6>
                                    <a  class="read-template" id="<?php echo $key; ?>">رویت</a>
                                    <a class="remove" id="<?php echo $key; ?>" >حذف</a>
                                </div>
                                <div id="<?php echo $key; ?>" class="content">
                                    <?php echo $val['content']; ?>
                                </div>
                            </li>
                        <?php } ?>
                        <?php }else{ ?>
                        <li>
                            <p> لیست خالی میباشد </p>
                        </li>
                        <?php }?>
                </ul>
            </div>
        </div>
<?php
    }


    public static function add()
    {
        $title   = $_POST['title'] ?? '';
        $status  = $_POST['status'] ?? '';
        $content = $_POST['content'] ?? '';
        $content = stripslashes( $content );
        $course  = $_POST['course'] ?? 1;
        $userID  = get_current_user_id();

        if ( 'pub' == $status ){
            Course::get_instance()::addTextTemplate( $userID ,$title ,$content ,$course  );
        }else{
            Users::get_instance()::addTextTemplate( false ,$userID ,$title ,$content );
        }
        self::templates();
        exit();
    }



    public static function loadTemplate( $courseID ,$userID )
    {
        $pub_items = Course::get_instance()::getTextTemplate( $courseID );
        ob_start();
        ?>
        <div  class="slider-text">
            <div class="fixed-content">
                <p>بستن</p>
                <h4>متن های آماده</h4>
            </div>
            <div class="tabs">
                <a class="pub a-active"  href="#item_1" onclick="return false;"> عمومی</a>
                <a class="priv" href="#item_2" onclick="return false;">خصوصی</a>
            </div>

            <div class="ready-text">
                <div class="items-con">
                    <div class="item_1 d-active" id="item_1">
                        <h6> </h6>
                        <ul>
                    <?php
                    if ( !empty( $pub_items ) ){
                        foreach ( $pub_items as $item  ){
                            ?>
                            <li id="content-1">
                                <div class="top">
                                    <div>
                                        <h5><?php echo isset( $item['title'] ) ? $item['title'] : 'بدون نام' ; ?></h5>
                                        <p class="read">رویت</p>
                                        <p class="add">افزودن</p>
                                    </div>
                                </div>
                                <div class="bottom">
                                    <div>
                                        <?php echo $item['content'];  ?>
                                    </div>
                                </div>
                            </li>
                            <?php
                        }
                    }
                ?>
           </ul>
        </div>
        <div class="item_2" id="item_2">
           <h6> </h6>
           <ul>
        <?php
        $priv_items = Users::get_instance()::getTextTemplate( $userID );

        if ( !empty( $priv_items ) ){
            foreach ( $priv_items as $key => $val  ){
                ?>
                <li id="content-1">
                    <div class="top">
                        <div>
                            <h5><?php echo $val['title']; ?></h5>
                            <p class="read">رویت</p>
                            <p class="add">افزودن</p>
                        </div>
                    </div>
                    <div class="bottom">
                        <div>
                            <?php echo $val['content'];  ?>
                        </div>
                    </div>
                </li>
            <?php  }  } ?>
            </ul>
                    </div>
                </div>
            </div>
        </div>
       <?php
        return ob_get_clean();
    }




    public static function delete()
    {
        $userID = get_current_user_id();
        $template_id = $_POST['template_id'];
        $type        = $_POST['type'];
        if ( $type == 'pub' ){
            $course_id  = $_POST['course_id'];
            $templates    = get_post_meta( $course_id , 'hamyar_course_text_template' ,true );

            unset($templates[$template_id]);
            update_post_meta(  $course_id , 'hamyar_course_text_template' ,$templates  );

        }else{
            $templates = Users::get_instance()::getTextTemplate( $userID );
            unset($templates[$template_id]);

            update_user_meta(  $userID , 'hamyar_user_text_template' ,$templates  );

        }
        self::templates();
        exit();
    }



    public static function sortPrivate()
    {
        $data = $_POST['data'];
        $userID  = get_current_user_id();
        $items =[];
        foreach ( $data as $key => $val ){
            $items [$key] = [
                  'title'   => $val['title'] ,
                  'content' => stripslashes( $val['content'] )
                ];
        }

        Users::get_instance()::addTextTemplate( $items , $userID , null , null );
        return self::templates();
    }


}
<?php


namespace HWP_Ticket\core\includes;




class Destination
{


    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public static function getDestinationsList(){
        return Functions::getTicketOptions('_hamfy_ticket_destinations' ,[] );
    }



    public static function checkDestinationNew( $userID ,$course ,$destination )
    {
        if ( $destination == 'tango_other' ){
            return true;
        }
        $destinations  = Destination::getDestinationsList();
        if ( !array_key_exists( $destination , $destinations )  ){
            Functions::returnResult( 404 , ['errorText' => 'واحد انتخابی مجاز نیست'] );
        }
        $products = maybe_unserialize( get_user_meta( $userID ,'_product_support' , true ) );

        if ( $destination == 'tango_support' ){
            if ( !isset( $products[$course] ) || $products[$course] < strtotime('now' ) ){
                Functions::returnResult( 403 , ['errorText' => 'زمان پشتیبانی  این دوره اتمام یافته '] );
            }
        }

        if ( $destination == 'tango_license' ){
            $purchase = \Lily_Course_Management::get_user_purchased_products( $userID );
            if ( !isset( $purchase[$course] ) || !metadata_exists( 'post', $course, '_has_license' ) ){
                Functions::returnResult( 403 , ['errorText' => 'این دوره بدون لایسنس میباشد'] );
            }
        }

        if ( $destination == 'tango_sale' ){
            if ( !isset( $products[$course] ) || !metadata_exists( 'post', $course, '_has_sales' )  ){
                Functions::returnResult( 403 , ['errorText' => 'دوره دارای پشتیبانی مالی نمیباشد'] );
            }
        }

        return true;
    }



    public static function checkDestinationReply( $userID ,$parent ,$destination )
    {
        if ( $parent->creator == $userID   ||
             $parent->assign_to == $userID ||
             $parent->destination == $destination  ){
            return true;
        }
        return false;
    }




}
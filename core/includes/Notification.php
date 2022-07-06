<?php

namespace HWP_Ticket\core\includes;





class Notification{

    public static function send_notification( $type ,object $parent ,$whoIs ,$userID )
    {
        $course      = $parent->main_object;
        $creator     = $parent->creator;
        if ( isset( $parent->parent_ticket ) ){
            $ticketID = $parent->parent_ticket;
        }else{
            $ticketID = $parent->id;
        }
        $destination = $parent->destination;
        if (!function_exists('new_ticket_sms')) return false;

        if ( $whoIs === 'student' || $whoIs === 'user' ){
            $masters =  Permissions::getMastersByCourseID( $course );
            if ( !empty( $masters ) && $destination !== 'tango_license' ){
                try {
                    if ( 'new_ticket' === $type ){
                        $post_title = get_post( $course );
                        $post_title = ( !empty( $post_title) && !is_wp_error( $post_title ) ) ? $post_title->post_title : 'ناشناس';
                        foreach ( $masters as $user ){
                            if ( !empty( $user['phone'] ) ){
                                $display_name = !empty( $user['full_name'] ) ? $user['full_name'] :'ناشناس';
                                new_ticket_sms( $user['phone'],$display_name ,$ticketID ,$post_title );
                            }
                        }
                    }elseif ( 'response_ticket' === $type ){
                        foreach ( $masters as $user ){
                            if ( !empty( $user['phone'] ) ){
                                $display_name = !empty( $user['full_name'] ) ? $user['full_name'] :'ناشناس';
                                new_response_sms( $user['phone'] ,$display_name ,$ticketID );
                            }
                        }
                    }
                }catch (\Exception $e){
                }
            }
        }else{
            try {
                if ( 'response_ticket' === $type ){
                     if ( isset( $creator ) && is_numeric( $creator ) && $userID != $creator  ){
                         if( function_exists( 'hf_user_mobile_meta_key'  )) {
                             $user_phone = get_user_meta( (int) $creator,hf_user_mobile_meta_key(),true);
                             $user       = Functions::getUser( $creator );
                             if ( !empty( $user_phone ) ){
                                 $display_name = $user->user_status ? $user->first_name .' '.$user->last_name :'ناشناس';
                                 new_response_sms( $user_phone ,$display_name ,$ticketID );
                             }
                         }
                     }
                }elseif ( 'assign_ticket' === $type ){
                    if ( !empty( $userID) ){
                        if( function_exists( 'hf_user_mobile_meta_key' )) {
                            $user_phone = get_user_meta( (int) $userID,hf_user_mobile_meta_key(),true);
                            $user       = Functions::getUser( $userID );
                            if ( !empty( $user_phone ) ){
                                $display_name = $user->user_status ? $user->first_name .' '.$user->last_name :'ناشناس';
                                $post_title = get_post( $course );
                                new_ticket_sms( $user_phone ,$display_name ,$ticketID ,$post_title->post_title );
                            }
                        }
                    }
                }
            }catch (\Exception $e){
            }
        }
        return true;
    }
}
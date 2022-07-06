<?php


namespace HWP_Ticket\core\includes;
use Aws\S3\S3Client;

class Uploader
{

    protected static  $_instance = null;
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }



    private static $AWS_S3_KEY    = 'L3UZP5WWSYOT61OEAVQZ';
    private static $AWS_S3_SECRET = 'zl7RjWdqi3NklPRrLtLlIczNAGdQCv1YbBIUBkO9';
    private static $AWS_S3_BUCKET = 'tango-ticket';
    private static $AWS_S3_URL    = 'https://ticket.hamyarwp.c3.mountains.poshtiban.com';
    public  static $extension     = '.zip,.rar,.jpg,.jpeg,.png,.pdf,.mp3,.wave,.txt,.wav';
    public  static $file_upload;
    public  static $table_logs;



    public function __construct()
    {
        global $wpdb;
        self::$file_upload = $wpdb->prefix.'tango_'.'file_upload';
        self::$table_logs  = $wpdb->prefix.'tango_'.'logs';
        add_action( 'rest_api_init' ,[ $this ,'registerRoute'] );
    }

    public function registerRoute()
    {
        register_rest_route('hamfy', '/v1.1/upload/',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'creatableRouteCallback' ] ,
                    'permission_callback' => function (\WP_REST_Request $request) {
                        $params = $request->get_params();
                        return Functions::decryptID( $params['user_token'] );
                    }
                ],
            ]
        );
    }



    public function creatableRouteCallback( \WP_REST_Request $request )
    {
        $file    = $request->get_file_params();
        $temp_file = $file['file']['tmp_name'];

        if( function_exists('hf_minio_upload') ){
            $result = hf_minio_upload( $file['file'],'ticket',true);
        }else{
            $type = $file['file']['type'];
            $name = $file['file']['name'];
            $ext  = pathinfo( $name, PATHINFO_EXTENSION );
            $new_name = $this->gen_uuid().'-'.time().'.'.$ext;
    
            try {
                date_default_timezone_set('America/Los_Angeles');
                $bucket = self::$AWS_S3_BUCKET;
                $s3 = new S3Client([
                    'version' => 'latest',
                    'region' => 'ir',
                    'endpoint' => self::$AWS_S3_URL,
                    'use_path_style_endpoint' => true,
                    'credentials' => [
                        'key' => self::$AWS_S3_KEY,
                        'secret' =>self::$AWS_S3_SECRET,
                    ],
                ]);
                $path = date('Y').'/'.date('m').'/'.date('d').'/';
    
                $result = $s3->putObject([
                    'Bucket'      => $bucket,
                    'Key'         => $path.$new_name,
                    'SourceFile'  => $temp_file,
                    'ContentType' => $type,
                    'Body'        => 'this is the body!'
                ]);
            }catch (\Exception $e){
                $result = $e->getMessage();
            }
        }
        @unlink( $temp_file );
        self::uploadLog( $result );
    }


    public static function uploadLog( $upload_result )
    {
        global $wpdb;
        try {
            if ( is_object( $upload_result ) && $upload_result->hasKey( 'ObjectURL' )){
                $online_path     = ( $upload_result->hasKey('ObjectURL'))? $upload_result->get('ObjectURL'):false;
                $current_user    = get_current_user_id();
                $etag            = ( $upload_result->hasKey('ETag'))? $upload_result->get('ETag'):false;
                if ( $online_path &&  $etag ){

                    $table       = self::$file_upload;
                    $extension   = pathinfo( $online_path )['extension'];
                    $online_path = str_replace(self::$AWS_S3_URL.'/'.self::$AWS_S3_BUCKET.'/','',$online_path );
                    $wpdb->insert(
                        $table ,[
                            'online_path' => $online_path ,
                            'etag' => $etag,
                            'user_id' => $current_user,
                            'type' => $extension
                        ],['%s','%s','%d','%s']
                    );
                    $id   = $wpdb->insert_id;
                    if (is_integer( $id ) ){
                        wp_send_json( ['fileName'=> $id ] );
                    }
                }
            }

        }catch (\Exception $e){
            self::addLog( $upload_result );
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/plain');
            exit('مشکل در آپلود فایل ایجاد شد. لطفا مجدد سعی نمایید.');

        }
    }

    public static function addLog( $result )
    {
        if ( !empty( $result ) ){
            global $wpdb;
            $table = self::$table_logs;
            $wpdb->insert(
                $table ,
                [
                    'log' => json_encode( $result )
                ],
                ['%s']
            );
        }
    }

    public static function getLink( $id )
    {
        $file_id  = (int)$id;
        $file_details = [];
        $fileObject = self::file_url( $file_id );
        if(function_exists('hf_minio_generate_secure_link')){
            $link=trim(str_replace('ticket/','',$fileObject->online_path),'/');
            $preSignedRequest= hf_minio_generate_secure_link('ticket/'.$link);
        }else{
            try {
                $file_url = $fileObject->online_path;
    
                $s3 = new S3Client([
                    'version'  => 'latest',
                    'region'   => 'ir',
                    'endpoint' => self::$AWS_S3_URL,
                    'use_path_style_endpoint' => true,
                    'credentials' => [
                        'key'    => self::$AWS_S3_KEY,
                        'secret' =>self::$AWS_S3_SECRET,
                    ],
                ]);
                $command = $s3->getCommand('GetObject', [
                    'Bucket' => self::$AWS_S3_BUCKET,
                    'Key'    => $file_url
                ]);
                $preSignedRequest = $s3->createPresignedRequest( $command, '+20 minutes');
                $preSignedRequest=(string) $preSignedRequest->getUri();
            }catch (\Exception $e){
                global $wpdb;
                $result = $e->getMessage();
                $table  = self::$table_logs;
                $wpdb->insert( $table,['log'=>'file download error ----- '.$result],['%s'] );
            }
        }

        $file_details['file_path'] = $preSignedRequest;
        $file_details['extension'] = $fileObject->type;
        return $file_details;
    }


    public static function file_url( $file_id )
    {
        global $wpdb;
        $table = self::$file_upload;
        if ( is_numeric( $file_id ) ) {
            return (object) $wpdb->get_row(
                "SELECT * FROM {$table} WHERE id = {$file_id}"
            );
        }
       return false;
    }


    public function gen_uuid() {
        return
            sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }


    public static function saveFiles( $files ,$ticketID ,$userID )
    {
        global $wpdb;
        if (!empty( $files ) && is_array( $files )) {
            foreach ( $files as $file_id ){
                if ( !is_numeric( $file_id ) ){
                    continue;
                }
                $uploaded_file = self::getFilesResult( $file_id );
                if ( empty( $uploaded_file ) ){
                    $wpdb->update(
                        self::$file_upload  ,
                        ['ticket_id' => $ticketID ,'user_id'=> $userID ] ,
                        ['id' => $file_id ] ,
                        ['%d' ,'%d'] ,
                        ['%d']
                    );
                }
            }
        }
        return true;
    }


    public static function getFilesResult( $fileID   )
    {
        global $wpdb;
        $file_table = self::$file_upload;
        $result = $wpdb->get_col(
            "SELECT ticket_id FROM {$file_table} WHERE id={$fileID}"
        );
        if ( !is_wp_error( $result ) && !empty( $result ) ){
            return $result[0];
        }
        return false;
    }



    public static function getFiles( $ticketID )
    {
        global $wpdb;
        $table   = self::$file_upload;
        $uploads = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} WHERE ticket_id= %d ;",$ticketID )
        );
        if ( !is_wp_error( $uploads ) && !empty( $uploads ) ){
            return $uploads;
        }
        return [];
    }


}
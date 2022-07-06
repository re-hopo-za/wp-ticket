




jQuery(function ($) {

    const object            = hamfy_object;
    const upload_url        = object.root + 'hamfy/v1.1/upload/';
    const ticket_url        = object.root + 'hamfy/v1.1/tickets/';
    const user_token        = object.user_token;
    const captcha_pub_key   = object.captcha_pub;
    Dropzone.autoDiscover   = false;

    uploader();

    function uploader(){
        $(document).find('.article-content #file').each( function () {
            let dropzoneControl = $(this)[0].dropzone;
            if (dropzoneControl) {
                dropzoneControl.destroy();
            }
        });
        $(document).find('.article-content #file').dropzone({
            url: upload_url   ,
            paramName: "file" ,
            maxFilesize:  10  ,
            maxThumbnailFilesize: 2,
            acceptedFiles: '.zip,.rar,.jpg,.jpeg,.png,.pdf,.mp3,.wave,.txt',
            maxFiles: 2    ,
            method: 'post' ,
            withCredentials: false,
            headers: null  ,
            timeout: 300000,
            addRemoveLinks: true,
            uploadMultiple: false,
            parallelUploads: 1,
            dictDefaultMessage: 'فایل را اینجا رها کن یا کلیک کن',
            dictFallbackMessage: 'متاسفانه مرورگر شما امکان بارگزاری فایل ندارد.',
            dictFileTooBig: 'حجم فایل نباید بیشتر از {{maxFilesize}}  مگابایت باشد',
            dictInvalidFileType: 'این نوع فایل معتبر نمی‌باشد.',
            dictResponseError: 'در فرایند ارسال فایل خطایی رخ داد. کد خطا {{statusCode}}',
            dictCancelUpload: 'حذف',
            dictRemoveFile: 'حذف',
            dictUploadCanceled: 'آپلود متوقف شد',
            dictMaxFilesExceeded: 'حداکثر تعداد فایل های مجاز {{maxFiles}} می‌باشد.',
            dictRemoveFileConfirmation: 'آیا فایل پاک گردد؟',
            init: function () {

                this.on("sending", function (file, xhr, formData) {
                    $('[name="submit"]').replaceWith('<span data-name="submit" class="pull-left btn-send">در حال آپلود فایل ...</span>');
                    formData.append('user_token', user_token );
                });

                this.on("success", function (file, responseText) {
                    $('[data-name="submit"]').replaceWith('<input type="submit" name="submit" value="ارسال" class="pull-left">');
                    if (responseText && responseText.fileName) {
                        $(document).find('#file_holder').append('<input type="hidden" name="files[]" value="' + responseText.fileName + '">');
                    }
                });

                this.on("removedfile", function (file) {
                    $('[data-name="submit"]').replaceWith('<input type="submit" name="submit" value="ارسال" class="pull-left">');
                    if (file && file.xhr && file.xhr.responseText && file.xhr.response);
                    $('#file_holder input[value="' + JSON.parse(file.xhr.response).fileName + '"]').remove();
                });
            }
        });

    }






    // Event: click on new ticket   // Result: show create ticket page
    $( document ).on('submit' , '#master-new' , function(e) {
        e.preventDefault();

        if ( confirm(' Are you Sure') ){
            hamfy_loader(true );

            let $this       = $(this);
            let course      = $this.data('course_id');
            let user_id     = $this.data('user_id');
            let title       = $this.find('#title').val();
            let content     = $this.find('textarea').val();

            let is_public   = 0 ;

            let files       = [];
            $('#file_holder input').each(function(){
                files.push($(this).val());
            });

            grecaptcha.ready(function() {
                grecaptcha.execute( captcha_pub_key ,
                    { action: 'submit' }).then( function( token ) {

                    if ( token ){
                        $.ajax({
                            url      : ticket_url ,
                            method   : 'POST'     ,
                            dataType : 'json'     ,
                            data: {
                                token       :  token        ,
                                action      :  'submit'     ,
                                act         :  'master-new' ,
                                course      :  course       ,
                                assign_to   :  user_id      ,
                                content     :  content      ,
                                title       :  title        ,
                                files       :  files        ,
                                is_public   :  is_public    ,
                                send_method :  'ticket'     ,
                                priority    :  3
                            },
                            headers: {
                                'usertoken' :  user_token
                            },
                            success: function (date) {
                                location.href = date.result
                            },

                        }) .always (function( jqXHR,textStatus ,jqXHR2 ) {
                            console.log( jqXHR,textStatus ,jqXHR2 )
                        });

                    }

                });
            });
        }

    });


    function hamfy_loader( status ) {
        let  body = $('body') ;
        if ( status === true ){
            body.addClass('tango_loader');
        }else{
            setTimeout(function () {
                body.removeClass('tango_loader');
            }, 1000);
        }
    }






})
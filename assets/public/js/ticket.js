
jQuery(function ($) {

    const object            = hamfy_object;
    const ticket_url        = object.root + 'hamfy/v1.1/tickets/';
    const upload_url        = object.root + 'hamfy/v1.1/upload/';

    const user_token        = object.user_token;
    const http_params       = object.params;
    const new_ticket_select = object.ticket_create;
    const captcha_pub_key   = object.captcha_pub;
    const public_nonce      = object.hamfy;
    const admin_url_ajax    = object.admin_url;
    const load_sup_filter   = object.load_sup_filter;
    const ckEditorButton    = object.template_btn;
    let preview_status      = false;
    let send_request_status = false;
    Dropzone.autoDiscover   = false;



    function reset_filter(){
        return {
            theid           : http_params.theid  ,
            sort            : http_params.sort   ,
            _page           : http_params._page  ,
            limit           : http_params.limit  ,
            search          : http_params.search ,
            status          : http_params.status ,
            destination     : http_params.destination ,
            course          : http_params.course ,
            username        : null               ,
            course_name     : null               ,
            last_response   : 0 ,
            unseen_tickets  : 0 ,
            n_reply_tickets : 0
        };
    }
    let filters = reset_filter();
    set_old_value();

    $(document).on('click' ,'#hwp-root-element .search svg', function () {
        filters.search = $(document).find('#ticket-filter .search').val();
        filters._page  = 0;
        tickets_page();
    });

    $(document).on('keyup' ,'#hwp-root-element .search input' , function (e) {
        e.preventDefault();
        if (e.key === 'Enter' || e.keyCode === 13) {
            filters.search = $(this).val();
            filters._page  = 0;
            tickets_page( false );
        }
    });

    $(document).on('click' ,'#all-user-tickets', function (e) {
        filters = reset_filter();
        filters.theid    = $(this).data('theid');
        filters.username = $(this).data('username');
        filters._page    = 0;
        window.history.pushState('all', 'All Ticket', '/ticket/user/'+filters.theid );
        tickets_page( true );
    });


    $(document).on('click' ,'#hwp-root-element #reset-tickets , #tango-panel #back', function () {
        filters = reset_filter();
        tickets_page( true );
        window.history.replaceState('list', 'Tickets', '/ticket/' );
    });


    $(document).on('click' ,'#hwp-root-element #reload-tickets ', function () {
        tickets_page();
        window.history.replaceState('list', 'Tickets', '/ticket/' );
    });


    $(document).on('click' ,'#hwp-root-element #last-response', function () {
        groupIconHandler( $(this) ,'last_response' );
        tickets_page();
        window.history.replaceState('list', 'Tickets', '/ticket/' );
    });

    $(document).on('click' ,'#hwp-root-element #unseen-tickets', function () {
        groupIconHandler( $(this) ,'unseen_tickets' );
        tickets_page();
        window.history.replaceState('list', 'Tickets', '/ticket/' );
    });

    $(document).on('click' ,'#hwp-root-element #n-reply-tickets', function () {
        groupIconHandler( $(this) ,'n_reply_tickets' );
        tickets_page();
        window.history.replaceState('list', 'Tickets', '/ticket/' );
    });

    $(document).on('click' ,'#hwp-root-element .pagination a' , function () {
        filters._page = $(this).attr('id');
        tickets_page();
    });


    $( document ).on('change' , '#hwp-root-element #ticket-filter select' , function(e) {
        if ( $(this).hasClass('select-2-filter' ) ){
            let object_key  = $(this).attr('name');
            let object_text = $(this).data('text');
            filters[object_key]  = $(this).val();
            filters[object_text] = $(this).text();
        }else {
            let object_key  = $(this).attr('name');
            filters[object_key]  = $(this).val();
        }
        filters._page = 0;
        tickets_page();
    });


    function tickets_page(){
        if ( !send_request_status ){
            send_request_status = true;
            hamfy_loader(true );
            $.ajax({
                url      : ticket_url ,
                dataType : "json"     ,
                method   : 'GET'      ,
                data: {
                    _page           : filters._page           ,
                    limit           : filters.limit           ,
                    search          : filters.search          ,
                    status          : filters.status          ,
                    destination     : filters.destination     ,
                    sort            : filters.sort            ,
                    course          : filters.course          ,
                    the_user        : filters.theid           ,
                    last_response   : filters.last_response   ,
                    unseen_tickets  : filters.unseen_tickets  ,
                    n_reply_tickets : filters.n_reply_tickets ,
                    call            : 'web'
                } ,
                headers: {
                    'usertoken' : user_token
                },
                success: function ( data ) {
                    $(document).find('.article-content').html( data.result );
                    reset_action_on_root_ticket();
                    set_old_value();
                }
            }).always(function ( data ) {
                send_request_status = false;
                hamfy_loader( false );
                if (data.result) return;
                console.log(data);
                if (data.responseText && data.responseText.length > 0) {
                    var responseText = JSON.parse(data.responseText);
                    if (responseText.message) {
                        iziToast.error({
                            title: 'خطا!!',
                            message: responseText.message,
                            position: 'topRight',
                            rtl: true
                        });
                    }
                }else{
                    iziToast.error({
                        title: 'خطا!!',
                        message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                        position: 'center',
                        rtl: true
                    });
                }
                console.log(data);
            });
        }
    }

    function reset_action_on_single_ticket(){
        ratingHandler();
    }

    function reset_action_on_root_ticket(){
        usersSeenListHandler();
        forwardListHandler();
        assignedListHandler();
        previewHandler();
        ticketPopUpCloser();

        showVisitorHover();
        summaryMouseLeave();
        loadSingleTicketClickable();
        init_select_2('#user');
        init_select_2('#course-root');
        loadTemplateButtonOnEditor();
    }
    reset_action_on_root_ticket();
    reset_action_on_single_ticket();

    function initial_editor(){
        editor_creator('#hwp-ticket-new-reply');
        editor_creator('#hwp-ticket-new');
        editor_creator('#hwp-master-ticket');
    }
    initial_editor();


    function set_old_value(){
        $(document).find('#ticket-filter #limit').val( filters.limit );
        $(document).find('#ticket-filter #search').val( filters.search );
        $(document).find('#ticket-filter #status').val( filters.status );
        $(document).find('#ticket-filter #destination').val( filters.destination );
        $(document).find('#ticket-filter #sort').val( filters.sort );
        $(document).find('#ticket-filter #user').append('<option value="'+filters.theid+'" selected>'+filters.username+'</option>');
        $(document).find('#ticket-filter #course').append('<option value="'+filters.course+'" selected>'+filters.course_name+'</option>');
        extra_filter_value('#n-reply-tickets' ,filters.n_reply_tickets );
        extra_filter_value('#unseen-tickets' ,filters.unseen_tickets );
        extra_filter_value('#last-response' ,filters.last_response );
        $(document).find('#ticket-filter #user').val( filters.theid );
    }


    function extra_filter_value( element ,value ){
        let $this = $(document).find( element );
        if( element && $this.length ){
            if( value == 1 ){
                $this.addClass('active');
            }else if( value == 0 && $this.hasClass('active')  ){
                $this.removeClass('active');
            }
        }
    }


    function loadSingleTicketClickable(){
        $(document).find('.article-content .ticket-item').on('click',function(event){
            if ( !$(event.target).is('.ticket-list__item-name ,.ticket-list__item-name *') ) {
                ticketItem( $(this).data('id') );
            }
        });
    }
    loadSingleTicketClickable();


    function ticketItem( ticketID ) {
        if ( !send_request_status ){
            send_request_status = true;
            hamfy_loader(true );
            $.ajax({
                url      : ticket_url  ,
                dataType : "json"      ,
                method   : 'GET'       ,
                data: {
                    id   : ticketID    ,
                    call : 'web'       ,
                },
                headers: {
                    'usertoken' :  user_token
                },
                success: function ( data ) {
                    $(document).find('.article-content').html( data.result );
                    window.history.pushState('single page', 'Single Ticket', '/ticket/single/'+ticketID );
                    initial_editor();
                    loadTemplateButtonOnEditor();
                    uploader();
                    showOthersCourses();
                    reset_action_on_single_ticket();
                    hamfy_loader(false );
                }
            }).always(function ( data ) {
                send_request_status = false;
                hamfy_loader( false );
                if (data.result) return;
                if (data.responseText && data.responseText.length > 0) {
                    var responseText = JSON.parse(data.responseText);
                    if (responseText.message) {
                        iziToast.error({
                            title: 'خطا!!',
                            message: responseText.message,
                            position: 'topRight',
                            rtl: true
                        });
                    }
                }else{
                    iziToast.error({
                        title: 'خطا!!',
                        message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                        position: 'center',
                        rtl: true
                    });
                }
                console.log(data);
            });
        }
    }

    $( document ).on('click' ,'.article-content #new' , function(e) {
        if ( !send_request_status ){
            send_request_status = true;
            hamfy_loader(true);
            $.ajax({
                url      : ticket_url  ,
                dataType : "json"      ,
                method   : 'GET'       ,
                data: {
                    id   : 'new'       ,
                    call : 'web'       ,
                },
                headers: {
                    'usertoken' :  user_token
                },
                success: function ( data ) {
                    $(document).find('.article-content').html( data.result );
                    window.history.pushState('new page', 'New Ticket', '/ticket/new' );
                    initial_editor();
                    uploader();
                }
            }).always(function ( data ) {
                send_request_status = false;
                hamfy_loader( false );
                if (data.result) return;
                if (data.responseText && data.responseText.length > 0) {
                    var responseText = JSON.parse(data.responseText);
                    if (responseText.message) {
                        iziToast.error({
                            title: 'خطا!!',
                            message: responseText.message,
                            position: 'topRight',
                            rtl: true
                        });
                    }
                }else{
                    iziToast.error({
                        title: 'خطا!!',
                        message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                        position: 'center',
                        rtl: true
                    });
                }
                console.log(data);
            });
        }


    });



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
            maxFilesize:  40  ,
            maxThumbnailFilesize: 2,
            acceptedFiles: '.zip,.rar,.jpg,.jpeg,.png,.pdf,.mp3,.wave,.txt',
            maxFiles: 4    ,
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


    $( document ).on('click' , '.article-content #destination' , function(e) {
        let courseEl = $(document).find('.article-content #course') ;
        if ( parseInt( courseEl.val() ) === 0 ){
            courseEl.css('border', '1px solid red');
        }
    });



    $( document ).on('change' , '.article-content #course' , function(e) {
        let course   = $(this).val();
        $(document).find('#destination input').prop("checked", false );


        if ( course === 'empty' ){
            $(document).find('#destination #other').prop("checked", true );
        }
        let license_checker = false;
        let support_checker = false;

        $.each( new_ticket_select , function( index , object ) {
            if ( course == Object.keys( object ) ){
                $.each( Object.values( object )[0].destinations , function( index , object_2 ) {

                    $(document).find('.support-remained').html(  Object.values( object )[0].support_time);
                    if ( Object.values( object_2 )[0]  === 'tango_license' ){
                        $(document).find('#destination #license').prop("disabled", false );
                        license_checker = true;
                    }

                    if ( Object.values( object_2 )[0]  === 'tango_support' ){
                        $(document).find('#destination #support').prop("disabled", false );
                        support_checker = true;
                    }
                });
            }

            if (  license_checker === false ){
                $(document).find('#destination #license').prop("disabled", true );
                $(document).find('#destination .license-con').addClass('disabled');
                $(document).find('#destination .license-con').hide();
            }else {
                $(document).find('#destination .license-con').removeClass('disabled');
                $(document).find('#destination .license-con').show();
            }

            if (  support_checker === false  ){
                $(document).find('#destination #support').prop("disabled", true );
                $(document).find('#destination .support-con').addClass('disabled');
            }else {
                $(document).find('#destination .support-con').removeClass('disabled');
            }
        });
    });



    $( document ).on('click' , '#destination label' , function(e) {
        let $this =$(this);
        if( $this.hasClass('disabled') ){
            if ( $this.attr('id') == 'support-c' ){
                let alert_text = $(document).find('.support-remained').html();

                if ( alert_text.length > 3 ){
                    iziToast.error({
                        title: 'خطا!!',
                        message: alert_text,
                        position: 'topRight',
                        rtl: true
                    });
                }else{
                    iziToast.warning({
                        title: 'هشدار',
                        message: ' برای ارسال تیکت به بخش پشتیبانی بایستی یک دوره دارای پشتیبانی را انتخاب کنید ! ',
                        position: 'topRight',
                        rtl: true
                    });
                }
            }else if ( $this.attr('id') == 'license-c' ){
                iziToast.info({
                    title: 'اعلان ',
                    message: ' این دوره بدون لایسنس میباشد  ',
                    position: 'topRight',
                    rtl: true
                });
            }
        }
    });


    $( document ).on('submit' , '#tango_form' , function(e) {
        e.preventDefault();
        let $this    = $(this);
        let content  = window.editor.getData();
        let title    = $this.find('#title').val();
        let course = $this.find('#course').val();
        let destination = $('input[name=destination]:checked', $this ).val();
        let files = [];
        $('#file_holder input').each(function () {
            files.push($(this).val());
        });
        if ( title && title.length < 250 ){
            if ( content && content.length >1) {
                iziToast.question({
                    timeout: 20000,
                    close: false,
                    overlay: true,
                    zindex: 99999,
                    title: 'تیکت ساخته شود ؟',
                    position: 'center',
                    rtl: true,
                    buttons: [
                        ['<button><b>ثبت شود</b></button>', function ( instance, toast ) {
                            instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                            hamfy_loader(true );
                            ajax_create_new_ticket( '' ,course ,destination ,content ,title ,files );


                            // if( captcha_pub_key == '' ){
                            // }else{
                            //     try {
                            //         grecaptcha.ready( function() {
                            //             grecaptcha.execute( captcha_pub_key , {  action: 'submit'
                            //             }).then(  function( token ) {
                            //                 ajax_create_new_ticket( token ,course ,destination ,content ,title ,files );
                            //             }).catch( function (errr) {
                            //                 console.log(errr);
                            //                 iziToast.error({
                            //                     title: 'خطا!!',
                            //                     message: ' خطای کپتچا رخ داد . لطفا مجدد تلاش کنید'+errr.message,
                            //                     position: 'topRight',
                            //                     rtl: true
                            //                 });
                            //                 hamfy_loader( false );
                            //             });
                            //         });
                            //     } catch (err) {
                            //         hamfy_loader( false );
                            //         iziToast.error({
                            //             title: 'خطا!!',
                            //             message: 'خطای کپچا :' + err.message,
                            //             position: 'topRight',
                            //             rtl: true
                            //         });
                            //         console.log(err);
                            //     }
                            // }
                        }, true],
                        ['<button>خیر</button>', function (instance, toast) {
                            instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                        }],
                    ],
                    onClosing: function(instance, toast, closedBy){
                    },
                    onClosed: function(instance, toast, closedBy){
                    }
                });
            }else {
                iziToast.error({
                    title: 'خطا!!',
                    message: 'محتوا الزامی میباشد',
                    position: 'topRight',
                    rtl: true
                });
            }
        }else {
            iziToast.error({
                title: 'خطا!!',
                message: ' طول عنوان بیشتر از حد مجاز میباشد ',
                position: 'topRight',
                rtl: true
            });
        }
    });



    function ajax_create_new_ticket( token ,course ,destination ,content ,title ,files  ){
        $.ajax({
            url      : ticket_url,
            method   : 'POST',
            dataType : 'json',
            data: {
                token    : token,
                course   : course,
                act      : 'new-ticket',
                destination: destination,
                content  : content,
                title    : title,
                files    : files,
                priority : 2
            },
            headers: {
                'usertoken': user_token
            },
            success: function ( data ) {
                $(document).find('.article-content').html( data.result );
                window.history.pushState('single page', 'Single Page', '/ticket/single/'+data.extra_data.ticket_id );
                initial_editor();
            }
        }).always(function ( data ) {
            hamfy_loader( false );
            if (data.result) return;
            if (data.responseText && data.responseText.length > 0) {
                var responseText = JSON.parse(data.responseText);
                if (responseText.message) {
                    iziToast.error({
                        title: 'خطا!!',
                        message: responseText.message,
                        position: 'topRight',
                        rtl: true
                    });
                }
            }else{
                iziToast.error({
                    title: 'خطا!!',
                    message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                    position: 'center',
                    rtl: true
                });
            }
            console.log(data);
        });
    }

    $( document ).on('submit' , '#reply_form' , function(e) {
        e.preventDefault();
        let act         = 'reply';
        let $this       = $(this);
        let content     = window.editor.getData();
        let access_submit =  true;
        if ( $(document).find('.is-comment').length ){
            let is_comment  = $this.find('input[name=is-comment]:checked').val();
            act = is_comment === 'reply' ? 'reply' : 'comment';
        }
        if ( $(document).find('#tango-panel').data( 'is_student' ) == 'is' && content.length < 30 ){
            access_submit = false;
        }

        if( access_submit ){
            iziToast.question({
                timeout: 20000,
                close: false,
                overlay: true,
                zindex: 99999,
                title: 'پاسخ ساخته شود ؟',
                position: 'center',
                rtl: true,
                buttons: [
                    ['<button><b>ثبت شود</b></button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                        hamfy_loader(true );
                        let parent_id   = $this.data('parent_id' );
                        let status      = $this.find('#status').val();
                        status          = status !== undefined ? status: 'open';
                        let files       = [];
                        let voice_type  = '';
                        $('#file_holder input').each( function(){
                            if ( $(this).data('type') ){
                                voice_type = 'voice_type';
                            }
                            files.push( $(this).val() );
                        });
                        ajax_create_reply( '' ,content ,files ,act ,parent_id ,status ,voice_type );


                        // try {
                        //     if( captcha_pub_key == '' ){
                        //     }else {
                        //         grecaptcha.ready( function() {
                        //             grecaptcha.execute( captcha_pub_key  , { action: 'submit'
                        //             }).then( function( token ) {
                        //                 if ( token ){
                        //                     ajax_create_reply( token ,content ,files ,act ,parent_id ,status ,voice_type );
                        //                 }else {
                        //                     hamfy_loader( false );
                        //                     iziToast.error({
                        //                         title: 'خطا!!',
                        //                         message:' خطای کپتچا رخ داد . لطفا مجدد تلاش کنید',
                        //                         position: 'topRight',
                        //                         rtl: true
                        //                     });
                        //                 }
                        //             }).catch( function (errr) {
                        //                 hamfy_loader( false );
                        //                 iziToast.error({
                        //                     title: 'خطا!!',
                        //                     message:'خطای کپچا :' + errr.message,
                        //                     position: 'topRight',
                        //                     rtl: true
                        //                 });
                        //             });
                        //         });
                        //     }
                        //
                        // }catch (err) {
                        //     hamfy_loader( false );
                        //     iziToast.error({
                        //         title: 'خطا!!',
                        //         message:'خطای کپچا :' + err.message,
                        //         position: 'topRight',
                        //         rtl: true
                        //     });
                        //     console.log(err);
                        // }
                    }, true],
                    ['<button>خیر</button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    }],
                ],
                onClosing: function(instance, toast, closedBy){
                },
                onClosed: function(instance, toast, closedBy){
                }
            });
        }else{
            iziToast.error({
                title: 'خطا!!',
                message: 'محتوا الزامیست',
                position: 'topRight',
                rtl: true
            });
        }

    });



    function ajax_create_reply( token ,content ,files ,act ,parent_id ,status ,voice_type ){

        $.ajax({
            url     : ticket_url  ,
            method  : 'POST'      ,
            data    : {
                token         :  token      ,
                content       :  content    ,
                files         :  files      ,
                act           :  act        ,
                parent_id     :  parent_id  ,
                status        :  status     ,
                send_method   :  'ticket'   ,
                course        :  voice_type
            },
            headers: {
                'usertoken'   : user_token
            },
            success: function ( data ) {
                $(document).find('#loop-container').html( data.result );
                $(document).find('#recorded-list').html('');
                window.editor.setData('');
                uploader();
                $(document).find('#file_holder').html('');
            }
        }).always(function ( data ) {
            hamfy_loader( false );
            if (data.result) return;
            if (data.responseText && data.responseText.length > 0) {
                var responseText = JSON.parse(data.responseText);
                if (responseText.message) {
                    iziToast.error({
                        title: 'خطا!!',
                        message: responseText.message,
                        position: 'topRight',
                        rtl: true
                    });
                }
            }else{
                iziToast.error({
                    title: 'خطا!!',
                    message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                    position: 'center',
                    rtl: true
                });
            }
            console.log(data);
        });
    }


    function init_select_2( element ){
        let element_object = $(document).find( '.article-content '+element );
        if( load_sup_filter == 'load' && element_object.length  ){
            element_object.select2({
                placeholder: 'انتخاب ',
                allowClear: false,
                width: '100%',
                ajax: {
                    url: admin_url_ajax ,
                    dataType: 'json'    ,
                    method: 'post'      ,
                    delay: 250          ,
                    data: function ( params ) {
                        return {
                            keyword : params.term         ,
                            nonce   : public_nonce        ,
                            action  : element == '#user' ? 'hamfy_user_search_public' : 'hamfy_search_products' ,
                            call    : 'public'
                        };
                    },
                    processResults: function (data) {
                        let options = [];
                        if (data) {
                            $.each(data, function (index, text) {
                                options.push({id: text.id, text: text.title});
                            });
                        }
                        return {
                            results: options
                        };
                    },
                    cache: false
                },
                minimumInputLength: 3
            }).on('click' , function () {
                $(document).find('.select2-search__field').focus();
            });
        }
    }

    function loadTemplateButtonOnEditor(){
        if ( load_sup_filter == 'load' ){
            setTimeout(function () {
                    $(document).find('#reply_form .ck-toolbar__items .ck-dropdown:last-of-type').after( ckEditorButton );
            } , 2000);
        }
    }


    $(document).on('click' ,'button#ck-load-template' , function () {
        $(document).find('.slider-text').toggleClass('slider-ready-content-open');
    });

    $(document).on('click' ,'.fixed-content>p' , function () {
        $(document).find('.slider-text').removeClass('slider-ready-content-open');
    });

    $(document).on('click' ,'.ready-text .top .add' , function () {
        let html = $(this).parent().parent().parent().find('.bottom>div').html();

        let old_content = window.editor.getData();
        window.editor.setData( old_content + html );
        $(document).find('.slider-text').removeClass('slider-ready-content-open');
    });


    $(document).on('click' ,'.slider-text .tabs a' , function () {
        let which_element = $(this).attr('href');
        $(document).find('.slider-text .tabs a').removeClass('a-active');
        $(this).addClass('a-active');
        $(document).find('.slider-text .items-con div').removeClass('d-active');
        $(document).find(which_element).addClass('d-active');
    });


    ///// remove template
    $(document).on('click' ,'.slider-text .top .read' , function () {
        $(this).parent().parent().parent().find('.bottom ').toggle();
    });


    $(document).on('change' ,'.select-item #destination' , function () {
        let $this = $(this);
        let old_destination = $this.data('old_destination');
        iziToast.question({
            timeout: 20000,
            close: false,
            overlay: true,
            zindex: 99999,
            title: 'واحد تغییر کند ؟',
            position: 'center',
            rtl: true,
            buttons: [
                ['<button><b>ثبت شود</b></button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    let new_destination  = $this.val();
                    let ticket_id        = $this.data('ticket_id');
                    hamfy_loader(true );
                    $.ajax({
                        url      : ticket_url ,
                        dataType : "json"     ,
                        method   : 'PUT'      ,
                        data: {
                            action        : 'update_destination' ,
                            n_destination : new_destination      ,
                            o_destination : old_destination      ,
                            ticket_id     : ticket_id
                        },
                        headers: {
                            'usertoken' : user_token
                        },
                        success: function () {
                            filters.last_response  = 0;
                            filters.unseen_tickets = 0;
                            tickets_page(true, true);
                            window.history.replaceState('list', 'Tickets', '/ticket/');
                        }
                    }).always(function ( data ) {
                        hamfy_loader( false );
                        if (data.result) return;
                        if (data.responseText && data.responseText.length > 0) {
                            var responseText = JSON.parse(data.responseText);
                            if (responseText.message) {
                                iziToast.error({
                                    title: 'خطا!!',
                                    message: responseText.message,
                                    position: 'topRight',
                                    rtl: true
                                });
                            }
                        }else{
                            iziToast.error({
                                title: 'خطا!!',
                                message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                                position: 'center',
                                rtl: true
                            });
                        }
                        console.log(data);
                    });
                }, true],
                ['<button>خیر</button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    $(this).val( old_destination );
                }],
            ],
            onClosing: function(instance, toast, closedBy){
            },
            onClosed: function(instance, toast, closedBy){
            }
        });
    });



    $(document).on('change' ,'.select-item #status' , function () {
        let $this     = $(this);
        let status    = $this.val();
        let ticket_id = $this.data('ticket_id');
        if ( status === 'closed' && load_sup_filter == 'load'  ){
            iziToast.question({
                timeout: 20000,
                close: false,
                overlay: true,
                zindex: 99999,
                title: 'وضعیت به بسته یافته تغییر کند ؟؟ ',
                position: 'center',
                rtl: true,
                buttons: [
                    ['<button><b>ثبت شود</b></button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                        hamfy_loader(true );
                        $.ajax({
                            url      : ticket_url ,
                            dataType : "json"     ,
                            method   : 'PUT'      ,
                            data: {
                                action        : 'update_status' ,
                                ticket_id     : ticket_id
                            },
                            headers: {
                                'usertoken' : user_token
                            },
                            success: function () {
                                filters.last_response  = 0;
                                filters.unseen_tickets = 0;
                                filters.unseen_tickets = 0;
                                tickets_page(true, true);
                                window.history.replaceState('list', 'Tickets', '/ticket/');
                            }
                        }).always(function ( data ) {
                            hamfy_loader( false );
                            if (data.result) return;
                            if (data.responseText && data.responseText.length > 0) {
                                var responseText = JSON.parse(data.responseText);
                                if (responseText.message) {
                                    iziToast.error({
                                        title: 'خطا!!',
                                        message: responseText.message,
                                        position: 'topRight',
                                        rtl: true
                                    });
                                }
                            }else{
                                iziToast.error({
                                    title: 'خطا!!',
                                    message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                                    position: 'center',
                                    rtl: true
                                });
                            }
                            console.log(data);
                        });
                    }, true],
                    ['<button>خیر</button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                        $(this).val('closed');
                    }],
                ],
                onClosing: function(instance, toast, closedBy){
                },
                onClosed: function(instance, toast, closedBy){
                }
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



    $(document).on('change' ,'.select-item #assign' , function () {
        let $this = $(this);
        iziToast.question({
            timeout: 20000,
            close: false,
            overlay: true,
            zindex: 99999,
            title: 'به کاربر مورد نظر اختصاص یابد ؟؟ ',
            position: 'center',
            rtl: true,
            buttons: [
                ['<button><b>ثبت شود</b></button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    let ticket_id   = $this.data('ticket_id');
                    let user_id     = $this.val();

                    if ( user_id == 'all' ){
                        user_id = 0;
                    }
                    hamfy_loader(true );
                    $.ajax({
                        url      : ticket_url ,
                        dataType : "json"     ,
                        method   : 'PUT'      ,
                        data: {
                            action        : 'assign_to_user'  ,
                            ticket_id     : ticket_id         ,
                            assign_to     : user_id
                        },
                        headers: {
                            'usertoken'   : user_token
                        },
                        success: function ( data ) {
                            filters.last_response = 0;
                            filters.unseen_tickets = 0;
                            filters.unseen_tickets = 0;
                            tickets_page(true ,true );
                            window.history.replaceState('list', 'Tickets', '/ticket/' );
                        }
                    }).always(function ( data ) {
                        hamfy_loader( false );
                        if (data.result) return;

                        if (data.responseText && data.responseText.length > 0) {
                            var responseText = JSON.parse(data.responseText);
                            if (responseText.message) {
                                iziToast.error({
                                    title: 'خطا!!',
                                    message: responseText.message,
                                    position: 'topRight',
                                    rtl: true
                                });
                            }
                        }else{
                            iziToast.error({
                                title: 'خطا!!',
                                message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                                position: 'center',
                                rtl: true
                            });
                        }
                        console.log(data);
                    });
                }, true],
                ['<button>خیر</button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    $(this).val('all');
                }],
            ],
            onClosing: function(instance, toast, closedBy){
            },
            onClosed: function(instance, toast, closedBy){
            }
        });
    });



    $(document).on('click' ,'#remove-reply' , function () {
        let ticketId  =  parseInt( $(this).data('ticket_id') );
        iziToast.question({
            timeout: 20000,
            close: false,
            overlay: true,
            zindex: 99999,
            title: 'حذف شود ؟',
            position: 'center',
            rtl: true,
            buttons: [
                ['<button><b>حذف شود</b></button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    hamfy_loader(true );

                    $.ajax({
                        url      : ticket_url ,
                        dataType : "json"     ,
                        method   : 'DELETE'   ,
                        data: {
                            ticket_id : ticketId
                        },
                        headers: {
                            'usertoken': user_token
                        },
                        statusCode: {
                            200: function () {
                                iziToast.success({
                                    title: ' اعلان ',
                                    message: ' پاسخ حذف شد',
                                    position: 'topRight',
                                    rtl: true
                                });
                                $('#reply-'+ticketId ).remove();
                                hamfy_loader(false );
                            },
                            401: function () {
                                iziToast.warning({
                                    title: ' اخطار ',
                                    message: 'خطا در شناسه تیکت',
                                    position: 'topRight',
                                    rtl: true
                                });
                                hamfy_loader( false );
                            },
                            500: function () {
                                iziToast.error({
                                    title: ' اخطار ',
                                    message: 'خطا داخلی',
                                    position: 'topRight',
                                    rtl: true
                                });
                                hamfy_loader( false );
                            },
                            403: function () {
                                iziToast.info({
                                    title: ' اعلان ',
                                    message: 'پاسخ رویت شده و امکان حذف وجود ندارد',
                                    position: 'topRight',
                                    rtl: true
                                });
                                hamfy_loader( false );
                            },
                        }
                    });
                }, true],
                ['<button>خیر</button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                }],
            ],
            onClosing: function(instance, toast, closedBy){
            },
            onClosed: function(instance, toast, closedBy){
            }
        });
    });


    function groupIconHandler( $this , input ){
        $this.siblings(".group").removeClass('active');
        $this.toggleClass('active');
        switch (input) {
            case 'last_response':
                filters.last_response   =  filters.last_response === 0 ? 1 : 0;
                filters.unseen_tickets  = 0;
                filters.n_reply_tickets = 0;
                filters._page           = 0;
                break;
            case 'unseen_tickets':
                filters.unseen_tickets  = filters.unseen_tickets === 0 ? 1 : 0;
                filters.last_response   = 0;
                filters.n_reply_tickets = 0;
                filters._page           = 0;
                break;
            case 'n_reply_tickets':
                filters.n_reply_tickets = filters.n_reply_tickets === 0 ? 1 : 0;
                filters.last_response   = 0;
                filters.unseen_tickets  = 0;
                filters._page           = 0;
                break;
        }
    }


    function showOthersCourses(){
        $(document).find('.courses-icon').click(function (e) {
            $(this).siblings('.ticket-single-courses').toggle();
        });
        $(document).click(function (e) {
            if(!$(e.target).hasClass('courses-icon')    )
            {
                $(document).find('.ticket-single-courses').hide();
            }
        });
    }
    showOthersCourses();

    function showVisitorHover(){
        let startTime, endTime, long_press;
        let see_icon = $(document).find('.users-seen-opener');
        see_icon.on('click', function () {
            if( long_press ){
                iziToast.question({
                    timeout: 20000,
                    close: false,
                    overlay: true,
                    zindex: 99999,
                    title: 'لیست پاک شود ؟؟ ',
                    position: 'center',
                    rtl: true,
                    buttons: [
                        ['<button><b>پاک شود</b></button>', function (instance, toast) {
                            instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                            let ticket_id = see_icon.data('ticket-id');
                            $.ajax({
                                url      : ticket_url ,
                                dataType : "json"     ,
                                method   : 'PUT'      ,
                                data: {
                                    action    : 'remove_read',
                                    ticket_id : ticket_id
                                },
                                headers: {
                                    'usertoken': user_token
                                },
                                success: function () {
                                    tickets_page(false ,false );
                                }
                            });
                        }, true],
                        ['<button>خیر</button>', function (instance, toast) {
                            instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                            $(this).val('all');
                        }],
                    ],
                    onClosing: function(instance, toast, closedBy){
                    },
                    onClosed: function(instance, toast, closedBy){
                    }
                });
            }
        });

        see_icon.on('mousedown', function () {
            startTime = new Date().getTime();
        });

        see_icon.on('mouseup', function () {
            endTime = new Date().getTime();
            long_press =  endTime - startTime > 500 ;
        });
    }


    function summaryShow( $this )
    {
        if ( preview_status === false ) {
            preview_status = true;
            $.ajax({
                url      : ticket_url  ,
                dataType : "json"      ,
                method   : 'GET'       ,
                data: {
                    id   : $this.data('ticket-id') ,
                    call : 'web-preview'           ,
                },
                headers: {
                    'usertoken' :  user_token
                },
                success: function ( data ) {
                    $this.siblings('.summary-con').find('.root').html( data.result );
                    preview_status = false;
                }
            }).always(function ( data ) {
                hamfy_loader( false );
                if (data.result) return;
                if (data.responseText && data.responseText.length > 0) {
                    var responseText = JSON.parse(data.responseText);
                    if (responseText.message) {
                        iziToast.error({
                            title: 'خطا!!',
                            message: responseText.message,
                            position: 'topRight',
                            rtl: true
                        });
                    }
                }else{
                    iziToast.error({
                        title: 'خطا!!',
                        message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                        position: 'center',
                        rtl: true
                    });
                }
                console.log(data);
            });
        }else{
            $this.find('.summary-con').html( '' );
        }

    }


    function summaryMouseLeave(){
        $(document).find('.summary-icon .summary-con').mouseleave(function () {
            $(this).html('').hide();
            preview_status = false;
        });
    }


    function usersSeenListHandler(){
        $(document).find('.ticket-list__item-name .users-seen-opener').on('click',function() {
            $(this).siblings().toggle();
            ticketPopUpOpener( $(this).data('ticket-id') );
        });
    }

    function forwardListHandler(){
        $(document).find('.ticket-list__item-name .forwards-opener').on('click',function() {
            $(this).siblings().toggle();
            ticketPopUpOpener( $(this).data('ticket-id') );
        });
    }

    function assignedListHandler(){
        $(document).find('.ticket-list__item-name .assigned-opener').on('click',function() {
            $(this).siblings().toggle();
            ticketPopUpOpener( $(this).data('ticket-id') );
        });
    }

    function previewHandler(){
        $(document).find('.ticket-list__item-name .summary-opener').on('click',function() {
            $(this).siblings().toggle();
            ticketPopUpOpener( $(this).data('ticket-id') );
            summaryShow($(this));
        });
    }


    function ticketPopUpOpener( ticket_id ){
        if ( ticket_id && !isNaN( ticket_id ) ){
            $(document).find('.popup-container.'+ticket_id ).toggle();
        }
    }

    function ticketPopUpCloser(){
        $(document).find('.popup-container').on('click',function() {
            $(this).siblings('a').find('.ticket-actions>div>div' ).hide();
            $(this).hide();
        });
    }


    function editor_creator( editor_creator ){
        if ( $(document).find( editor_creator ).length  ){
            if ( window.editor ){
                window.editor.destroy()
                    .then( () => {
                        editor.ui.view.toolbar.element.remove();
                        editor.ui.view.editable.element.remove();
                    } );
            }
            const characters_con = $(document).find('.demo-update__words' );
            ClassicEditor
                .create( document.querySelector( editor_creator), {
                    toolbar: {
                        items: [
                            'heading',
                            'link',
                            'bold',
                            'code',
                            'pageBreake',
                            '|',
                            'outdent',
                            'indent',
                            'alignment',
                            '|',
                            'specialCharacters',
                            'codeBlock'
                        ]
                    },
                    wordCount: {
                        onUpdate: stats => {
                            characters_con.text( stats.characters ).css('color' , '#999');
                        }
                    },
                    language: 'fa',
                    licenseKey: ''
                } )
                .then( editor => {
                    window.editor = editor;

                } )
                .catch( error => {
                    console.error( 'Oops, something went wrong!'+ error );
                } );
        }

    }



    var blob_file = '';

    $(document).on('click','#recorder-voice',function() {
        let $this = $(this);
        if ( $this.hasClass('recording') ){
            pauseRecording();
            $this.removeClass('recording');
            $this.addClass('puased');

        }else if ( $this.hasClass('puased') ){
            pauseRecording();
            $this.removeClass('puased');
            $this.addClass('recording');
            $this.siblings('svg').addClass('active');

        }else{
            startRecording();
            $this.addClass('recording');
            $this.siblings('svg').addClass('active');
        }
    });



    $(document).find('#save-recorded-voice').on('click',function() {
        $this = $(this);
        rec.pause();
        console.log(rec.state);
        $this.siblings('svg').addClass('puased');
        $this.siblings('svg').removeClass('recording');
        iziToast.question({
            timeout: 20000,
            close: false,
            overlay: true,
            zindex: 99999,
            title: 'حذف شود ؟',
            position: 'center',
            rtl: true,
            buttons: [
                ['<button><b>ذخیره شود ؟ </b></button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    $this.addClass('sending');
                    stopRecording();
                    setTimeout( function (){

                        var form = new FormData();
                        form.append("file", blob_file, 'voice.wav' );
                        form.append('file_type', 'voice_type' );
                        form.append('user_token', user_token );
                        $.ajax({
                            url      : upload_url  ,
                            method   : 'POST'       ,
                            data     :  form  ,
                            processData: false,
                            contentType: false,
                            success: function ( data ) {
                                var url = URL.createObjectURL( blob_file );
                                var audio = '<div><audio controls="controls" src="'+url+'" id="'+'audio-id-'+data.fileName+'"></audio>';
                                audio     =  audio + '<span class="remove-audio" data-audio-id="'+data.fileName +'"> ' +
                                    'حذف' +
                                    ' </span> </div> ';
                                $(document).find('#recorded-list').append( audio  );
                                $(document).find('#file_holder').append('<input type="hidden" data-type="voice" id="audio-input-holder-'+data.fileName+'" name="files[]" value="' + data.fileName + '">');

                                $this.removeClass('active');
                                $this.removeClass('sending');
                                let audio_src = $(document).find('#recorded-list audio');
                            }
                        }).always(function ( data ) {
                            send_request_status = false;
                            hamfy_loader( false );
                            if (data.result) return;
                            if (data.responseText && data.responseText.length > 0) {
                                var responseText = JSON.parse(data.responseText);
                                if (responseText.message) {
                                    iziToast.error({
                                        title: 'خطا!!',
                                        message: responseText.message,
                                        position: 'topRight',
                                        rtl: true
                                    });
                                }
                            }else{
                                iziToast.error({
                                    title: 'خطا!!',
                                    message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                                    position: 'center',
                                    rtl: true
                                });
                            }
                            console.log(data);
                        });
                    },2000 );
                }, true],

                ['<button>حذف شود ؟ </button>', function (instance, toast) {
                    stopRecording();
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                    $this.removeClass('active');
                    $this.siblings('svg').removeClass('puased');
                    $this.siblings('svg').removeClass('recording');
                    blob_file = '';
                }],

                ['<button>لغو</button>', function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                }],

            ],
            onClosing: function(instance, toast, closedBy){
            },
            onClosed: function(instance, toast, closedBy){
            }
        });

    });



    $(document).on('click' ,'.remove-audio' ,function() {
        let $this = $(this);
        let audio_id = $this.data('audio-id');
        if( confirm('حذف شود ؟') ){
            $(document).find('#reply_form #audio-input-holder-'+audio_id ).remove();
            $(document).find('#recorded-list #audio-id-'+audio_id ).remove();
            $this.remove();
        }
    });
    URL = window.URL || window.webkitURL;

    function startRecording() {
        var constraints = { audio: true, video:false };
        navigator.mediaDevices.getUserMedia(constraints).then(function( stream) {
            rec = new MediaRecorder(stream);
            rec.start();
        });
    }

    function pauseRecording(){

        if ( rec.state == "recording" ){
            rec.pause();
        }else{
            rec.resume();
        }
    }

    function stopRecording() {
        rec.stop();
        rec.ondataavailable = e => {
            if (rec.state == "inactive"){
                let blob = new Blob([e.data],{type:'audio/mpeg-3'});
                createDownloadLink(blob);
            }
        };
    }


    function createDownloadLink(blob) {
        blob_file = blob;
    }


    function ratingHandler(){

        $(document).find('.rating-root i').mouseenter(function() {
            let $this = $(this);
            $this.addClass('start-hover-in');
            $this.prevAll().addClass('start-hover-in');
            $this.nextAll().removeClass('start-hover-in');
        });

        $(document).find('.rating-root i').mouseleave(function() {
            let $this = $(this);
            $this.removeClass('start-hover-in');
            $this.prevAll().removeClass('start-hover-in');
        });

    }

    $(document).on('click' ,'.rating-root i' ,function() {
        let $this = $(this);
        if ( $this.hasClass('dashicons-star-empty') ){
            $this.removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
            $this.prevAll().removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
            $this.nextAll().removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
            let ticket_id = $this.parent().data('ticket-id');
            let parent_id = $this.parent().data('parent-id');
            let rate      = $this.data('rate');
            if ( ticket_id && parent_id ){
                iziToast.question({
                    timeout: 20000,
                    close: false,
                    overlay: true,
                    zindex: 99999,
                    title: 'نظر ثبت شود ؟',
                    position: 'center',
                    rtl: true,
                    buttons: [
                        ['<button><b>ذخیره شود ؟ </b></button>', function (instance, toast) {
                            instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                            hamfy_loader( true );
                            $.ajax({
                                url      : ticket_url ,
                                dataType : "json"     ,
                                method   : 'PUT'      ,
                                data: {
                                    action    : 'add_rating',
                                    ticket_id : parent_id ,
                                    parent_id : ticket_id ,
                                    rate      : rate
                                },
                                headers: {
                                    'usertoken' :  user_token
                                },
                                success: function ( data, textStatus, xhr ) {
                                    if ( xhr.status === 200 ){
                                        iziToast.success({
                                            title: ' اعلان ',
                                            message: ' نظر شما با موفقیت ثبت شد ',
                                            position: 'topRight',
                                            rtl: true
                                        });
                                        $this.parent().removeClass('rating-root');
                                    }
                                    if ( xhr.status === 500  ){
                                        iziToast.error({
                                            title: ' خطا ',
                                            message: 'خطا هنگام ثبت نظر',
                                            position: 'topRight',
                                            rtl: true
                                        });
                                        $this.parent().find('i').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
                                    }
                                    hamfy_loader( false );
                                }
                            }).always(function ( data ) {
                                hamfy_loader( false );
                                if (data.result) return;
                                if (data.responseText && data.responseText.length > 0) {
                                    var responseText = JSON.parse(data.responseText);
                                    if (responseText.message) {
                                        iziToast.error({
                                            title: 'خطا!!',
                                            message: responseText.message,
                                            position: 'topRight',
                                            rtl: true
                                        });
                                    }
                                }else{
                                    iziToast.error({
                                        title: 'خطا!!',
                                        message: 'خطایی رخ داده است. لطفا مجدد سعی نمایید.',
                                        position: 'center',
                                        rtl: true
                                    });
                                }
                                console.log(data);
                            });
                        }, true],
                        ['<button>لغو</button>', function (instance, toast) {
                            instance.hide({ transitionOut: 'fadeOut' }, toast, 'button');
                            $this.parent().find('i').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
                        }],

                    ],
                    onClosing: function(instance, toast, closedBy){
                    },
                    onClosed: function(instance, toast, closedBy){
                    }
                });
            }

        }else{
            $this.parent().find('i').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
        }
    });





});
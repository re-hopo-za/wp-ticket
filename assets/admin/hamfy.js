jQuery(function($){


    let all_destinations = {
            'tango_support' : '<p id="tango_support"> پشتیبانی </p>' ,
            'tango_license' : '<p id="tango_license">لایسنس </p>' ,
            'tango_other'   : '<p id="tango_other">دیگر  </p>' ,
            'tango_sale'    : '<p id="tango_sale">فروش  </p>'
        }

    let permission_loader = '<svg xmlns="http://www.w3.org/2000/svg" id="permission-loader" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="30px" height="30px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">\n' +
        '  <path d="M10 50A40 40 0 0 0 90 50A40 43.1 0 0 1 10 50" fill="#3e3d3d" stroke="none">\n' +
        '    <animateTransform attributeName="transform" type="rotate" dur="0.1s" repeatCount="indefinite" keyTimes="0;1" values="0 50 51.55;360 50 51.55"></animateTransform>\n' +
        '  </path>\n' +
        ' </svg>';




    $(document).on('click' , '.search-new-user-permission button' , function (){
        let $this  = $(this);
        let keyword   = $this.parent().parent().find('input').val();
        if ( keyword.length  ){
            let exclude_users = [] ;
            $( $('.permission-body #user_id') ).each( function() {
                exclude_users.push( parseInt( $(this).text() ) );
            });
            $this.html( permission_loader );

            $.ajax({
                url: hamfy_admin_object.admin_url ,
                dataType: "json" ,
                method  : 'POST' ,
                data: {
                    'action'   : 'hamfy_user_search_admin' ,
                    'nonce'    : hamfy_admin_object.hamfy ,
                    'ex_users' : exclude_users            ,
                    'keyword'  : keyword
                } ,
                success: function ( data, textStatus, xhr ) {
                    let users_list = '';
                    for (const [key, value] of Object.entries(data)) {
                        users_list += '<li id="'+ value.id +'"><p id="u_name">'+ value.name +'</p> <p id="u_email">'+ value.email +'</p> ' +
                            '<p id="u_phone">'+ value.phone +'</p> <p id="user_id">'+ value.id +'</p> </li>';
                    }
                    $this.parent().siblings('.new-user-list').find('ul').html(users_list)
                    if ( xhr.status === 200 ){
                        $this.html('جستجو');
                    }
                }
            }).always(function(xhr, textStatus) {
                if ( xhr.status === 404 ){
                    $this.html('کاربری یافت نشد');
                }
            });
        }

    });


    // remove destination or course item
    $(document).on('click' , '.remove--item' , function (){
        $(this).parent().parent().remove();
    });


    // show add new user section
    $(document).on('click' , '.add-new-user' , function (){
        $(document).find('.pop-up-permission').toggle();
        $(document).find('#select-user').focus();
    });


    // add new destination to list
    $(document).on('click' , '.distination-add' , function (){

        $(this).parent().siblings('section').toggle();
        let dis_les = $(this).parent().siblings('.items-list').children('p');
        let dest_list = [];
        let final_des = '';
        $( dis_les).each( function( ) {
            dest_list.push( $( this ).attr('id') );
        });
        for ( const [key, value] of Object.entries(all_destinations) ) {
            if ( !dest_list.includes( key ) ){
                final_des += value;
            }
        }
        $(this).parent().parent().children('section').find('.dynamic-destination-list').html(final_des);
    });

    // add new destination to list
    $(document).on('click' , '.dynamic-destination-list p' , function (){
        $(this).toggleClass('dest-list-active');
    });

    // add new destination to list
    $(document).on('click' , '.save-dest-list-dynamic button.add' , function (){
        let raw_des = '';
        let element = $(this).data('dest_id');
        let dest_actives = $(this).parent().siblings('.dynamic-destination-list').html();

        $( dest_actives).each( function( ) {
            if ( $(this).hasClass('dest-list-active' ) ){
                raw_des += '<p id="'+$(this).attr('id') +'"> '+$(this).text() +'<span class="remove--item dashicons dashicons-dismiss"></span></p>';
            }
        });
        element = '.permission-body>.destination-list.'+element+' .items-list';
        let old_item = $(document).find( element ).html();
        old_item  = old_item + raw_des;
        $(document).find( element ).html(old_item);
        $(this).parent().parent().hide();
    });


    $(document).on('click' , '.save-dest-list-dynamic button.close' , function (){
        $(this).parent().parent().hide();
    });

    // add new course to list
    $(document).on('click' , '.course-add' , function (){
        $(this).parent().siblings('section').toggle();
    });


    $(document).on('click' , '.permission-remove' , function (){
        let $this   = $(this).parent();
        let user_id = $this.data('user_id');
        if ( $(this).hasClass('not-save') ){
            $this.parent().remove();
            return true;
        }
        if ( confirm('تمامی دسترسی ها گرفته شود ؟؟') ){
            $this.html(permission_loader);
            $.ajax({
                url: hamfy_admin_object.admin_url ,
                dataType: "json" ,
                method  : 'POST' ,
                data: {
                    'action'       : 'hamfy_remove_all_permission' ,
                    'nonce'        : hamfy_admin_object.hamfy      ,
                    'user_id'      : user_id    ,
                } ,
                success: function ( data, textStatus, xhr ) {
                    if ( xhr.status === 200 ){
                        $this.parent().remove();
                    }else {
                        $this.html('<i class="permission-remove dashicons  dashicons-no"></i>');
                    }
                }
            });
        }
    });


    $(document).on('click' , '.dynamic-courses-list button.close' , function (){
        $(this).parent().parent().parent().hide();
    });


    $(document).on('click' , '.add-course-form button' , function (){
        let $this      = $(this);
        let courses_id = [];
        let element = $this.data('course_id');
        let keyword = $this.siblings('input').val();
        if (keyword.length > 3 ){
            element = '.permission-body>.courses-list.'+element+' .items-list';

            $( $(element).html() ).each( function( ) {
                courses_id.push( $(this).attr('id') )
            });

            $this.html( permission_loader );

            $.ajax({
                url: hamfy_admin_object.admin_url ,
                dataType: "json" ,
                method  : 'POST' ,
                data: {
                    'action'   : 'hamfy_search_products'   ,
                    'nonce'    : hamfy_admin_object.hamfy  ,
                    'exclude'  : courses_id                ,
                    'keyword'  : keyword                   ,
                    'call'     : 'admin'
                } ,
                success: function ( data, textStatus, xhr ) {
                    if ( xhr.status === 200 ){
                        let course_final ='';
                        $this.text( 'جستجو' );
                        for (const [key, value] of Object.entries(data)) {
                            course_final += '<li id="'+ value.id +'"> '+ value.title +' </li>';
                        }
                        $this.parent().parent().siblings('.courses-fetched-list').children('ul').html(course_final);
                    }

                }
            });
        }
    });



    $(document).on('dblclick' ,'.courses-fetched-list li' , function (){
        let new_permission =
            '<div id="'+ $(this).attr('id') +'" class="course-permission-list">' +
                '<div class="header">' +
                    '<span class="remove--item dashicons dashicons-dismiss"></span>' +
                    '<p class="course-name"> '+$(this).text() +' </p>' +
                '</div>' +
                '<div class="course-permission-list-con">' +
                    '<ul>' +
                        '<li><input id="tango_support" type="checkbox" value="tango_support"> پشتیبانی</li>' +
                        '<li><input id="tango_license" type="checkbox" value="tango_license"> لایسنس</li>' +
                        '<li><input id="tango_other" type="checkbox" value="tango_other"> دیگر</li>' +
                        '<li><input id="tango_sale" type="checkbox" value="tango_sale"> مالی</li>' +
                    '</ul>' +
                '</div>' +
            '</div>';

        $(this).parent().parent().parent().siblings('.items-list').append( new_permission );
        $(this).parent().parent().parent().hide();
    });




    $(document).on('click' , '.permission-update' , function (){
        let $this   = $(this).parent();
        let element = $this.data('user_id');
        element = "tbody .permission-body#"+element;
        let tr = $(document).find( element );

        if ( tr !== undefined ){
            $this.html(permission_loader);
            let user_id = parseInt( tr.find('#user_id').text() );
            let permissions = {};
            let trust  = tr.find('#user_trust input').is(':checked');

            tr.find('.courses-list .items-list>div').each( function() {
                let support =  $(this).find('input#tango_support').is(":checked");
                let license =  $(this).find('input#tango_license').is(":checked");
                let other   =  $(this).find('input#tango_other').is(":checked");
                let sales   =  $(this).find('input#tango_sale').is(":checked");
                let course  =  $(this).attr('id');
                permissions[course] = {'tango_support' :support ,'tango_license':license ,'tango_other':other ,'tango_sale':sales  };
            });

            $.ajax({
                url: hamfy_admin_object.admin_url ,
                dataType: "json" ,
                method  : 'POST' ,
                data: {
                    'action'   : 'hamfy_update_user_permission' ,
                    'nonce'    : hamfy_admin_object.hamfy       ,
                    'user_id'  : user_id                        ,
                    'permissions' : permissions                 ,
                    'u_trust'  : trust
                } ,
                success: function ( data, textStatus, xhr ) {
                    if ( xhr.status === 200 ){
                        $this.html('<i class="permission-update  dashicons dashicons-update-alt"> </i>');
                        $this.prev().html('<i class="permission-remove dashicons  dashicons-no"></i>');
                    }
                }
            });
        }
    });





    $(document).on('click' , '.viewer-permission-section .close' , function (){
        $(this).parent().parent().parent().hide();
        $(document).find('.new-user-list ul').html('');
    });



    $(document).on('dblclick' , '.new-user-list ul li' , function (){
        let $this = $(this);
        let u_id  = $this.attr('id');
        let name  = $this.find('#u_name').text();
        let phone = $this.find('#u_phone').text();
        let email = $this.find('#u_email').text();

        let tr = '<tr class="permission-body" id="'+u_id+'">\n' +
            '            <td id="user_id">'+u_id+'</td>\n' +
            '            <td>'+name+'</td>\n' +
            '            <td>'+email+'</td>\n' +
            '            <td>'+roleTransition("user")+'</td>\n' +
            '            <td>'+phone+'</td>\n' +
            '            <td class="courses-list '+u_id+'">\n' +
            '            <div class="items-list"></div><div><i class="course-add dashicons dashicons-plus"></i></div>' +
            '                <section>\n' +
            '                    <div class="dynamic-courses-list">\n' +
            '                        <div>\n' +
            '                            <h5> انتخاب دوره </h5>\n' +
            '                            <button class="close"> بستن</button>\n' +
            '                        </div>\n' +
            '                        <div class="add-course-form">\n' +
            '                            <input type="text" placeholder="جستجوی دوره....." name="select-course">\n' +
            '                                <button data-course_id="'+u_id+'">' +
            ' جستجو </button>                                ' +
            '                        </div>\n' +
            '                    </div>\n' +
            '                    <div class="courses-fetched-list">\n' +
            '                        <ul>\n' +
            '                           <li id="webmasteran"> گروه وبمستران </li>' +
            '                           <li id="instagram"> گروه اینستاگرام </li>' +
            '                           <li id="empty"> بدون دوره </li>' +
            '                           <li id="like_admin"> شبه ادمین </li>' +
            '                        </ul>\n' +
            '                    </div>\n' +
            '                </section>\n' +
            '            </td>\n' +
            '            <td id="user_trust">\n' +
            '                <input type="checkbox" value="enable"  >\n' +
            '            </td>\n' +
            '            <td data-user_id="'+u_id+'">\n' +
            '                <i class="permission-remove not-save dashicons  dashicons-no"></i>\n' +
            '            </td>\n' +
            '            <td data-user_id="'+u_id+'">\n' +
            '                <i class="permission-update not-save  dashicons dashicons-update-alt"> </i>\n' +
            '            </td>\n' +
            '        </tr>';

        $(this).parent().parent().parent().hide();
        $(document).find('.new-user-list ul').html('');
        $(document).find('table tbody').append(tr);
    });


    function roleTransition( keyword ){
        let output = '';
        switch ( keyword ){
            case 'user':
                output = 'دانشجو';
                break;
            case 'master':
                output = 'استاد';
                break;
            case 'support':
                output = 'پشتیبان';
                break;
            case 'admin':
                output = 'مدیر';
                break;
        }
        return output;
    }



///// Options Handler

    $(document).on( 'click' ,'#hwp_ticket_user_hash_complement' , function () {
        $(this).val( makeId(300 , 400) )
    })




    function makeId(length) {
        let result           = '';
        let characters       = '0123456789';
        let charactersLength = characters.length;
        for ( let i = 0; i < length; i++ ) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }



});



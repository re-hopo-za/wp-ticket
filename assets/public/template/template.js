
jQuery(function ($) {






    sortable('#template-sortable', {});

    ///// remove template
    $(document).on('click' ,'#template-sortable .read-template' , function () {
        let id = $(this).attr('id');
        $(this).parent().parent().find('div.content#'+id ).toggle();
    })
    $(document).on('click' ,'.bottom .read-template-pub' , function () {
        let id = $(this).attr('id');
        $(this).parents('li').find('div.content#'+id ).toggle();
    })


    $(document).on('click' ,'.save-sort' , function () {
        let elements = $(document).find('#template-sortable li');

        if ( confirm('Are you Sure') ){
            let data = $.map( elements , function( elem, count ){
                return   {
                    'title'   :  $(elem).find('h6 span').text() ,
                    'content' :  $(elem).find('.content').html() ,
                }
            });
            $.ajax({
                url: hamfy_object.admin_url  ,
                dataType: "json" ,
                method  : 'POST' ,
                data: {
                    action    :'hamfy_sort_text_template' ,
                    nonce     : hamfy_object.template_nonce ,
                    data      : data
                } ,
                success: function ( html ) {
                    // console.log(html)
                }
            })

        }


    })

    $(document).find('#course-root').select2({
        placeholder: 'انتخاب دوره',
        allowClear: false,
        width: '100%',
        ajax: {
            url: hamfy_object.admin_url,
            dataType: 'json',
            method: 'post',
            delay: 250,
            data: function ( params ) {
                return {
                    keyword : params.term ,
                    action  : 'hamfy_search_products',
                    nonce   : hamfy_object.template_nonce ,
                    call    : 'admin'
                };
            },
            processResults: function (data) {
                let options = [];
                if (data) {
                    $.each(data, function (index, text) {
                        options.push({id: text.id, text: text.title});
                        $(document).find('.select-permission-users').attr('id' , text.id );
                    });
                }
                return {
                    results: options
                };
            },
            cache: false
        },
        minimumInputLength: 3
    });

///////  create template page
    $('input[type=radio][name=status]').change(function() {
        let status = $(this).val();
        let status_element = $(document).find('.course-root');
        if ( status === 'priv' ){
            status_element.hide();
            status_element.children('input').prop('required',false);
        }else {
            status_element.show();
            status_element.children('input').prop('required',true);
        }
    });



    ///// add template
    $( document ).on('submit' , '#text_template' , function(e) {
        e.preventDefault();
        let $this    = $(this);
        let title    = $this.find('#title').val();
        let status   = $this.find('input[name="status"]:checked').val();
        let content  = window.editor.getData();
        let course   = null;

        if ( status === 'pub' ){
            course = $this.find('#course-root').val();
            if ( course === null ){
                course = 1;
            }
        }

        if ( confirm('are you sure') ){
            $.ajax({
                url: hamfy_object.admin_url  ,
                dataType: "html" ,
                method  : 'POST' ,
                data: {
                    action    :'hamfy_add_text_template' ,
                    nonce     : hamfy_object.template_nonce ,
                    title     : title ,
                    status    : status ,
                    content   : content ,
                    course    : course
                } ,
                success: function ( html ) {
                    $(document).find('.text-templates').html(html);
                    sortable('#template-sortable', {});
                }
            })
        }

    });



    ///// remove template
    $(document).on('click' ,'.text-templates .remove' , function () {
        let course_id   = $(this).data('p_id');
        let template_id = $(this).attr('id');
        let type = course_id === undefined  ? 'priv' : 'pub';

        if ( confirm('are you sure' ) ){
            $.ajax({
                url: hamfy_object.admin_url  ,
                dataType: "html" ,
                method  : 'POST' ,
                data: {
                    action       :'hamfy_remove_text_template' ,
                    nonce        : hamfy_object.template_nonce ,
                    type         : type,
                    course_id    : course_id ,
                    template_id  : template_id
                } ,
                success: function ( html ) {
                    $(document).find('.text-templates').html(html);
                    sortable('#template-sortable', {});
                }
            })
        }

    })


    let new_ticket_element = $(document).find('#wp-create-template');
    if ( new_ticket_element.length ){
        ClassicEditor
            .create( document.querySelector( '#wp-create-template' ) , {
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'underline',
                        'strikethrough',
                        'link',
                        '|',
                        'fontSize',
                        'fontColor',
                        '|',
                        'blockQuote',
                        'horizontalLine',
                        '|',
                        'bulletedList',
                        'todoList',
                        'numberedList',
                        '|',
                        'indent',
                        'alignment',
                        'outdent',
                        '|',
                        'insertTable',
                        'undo',
                        'redo'
                    ]
                },
                language: 'fa',
                table: {
                    contentToolbar: [
                        'tableColumn',
                        'tableRow',
                        'mergeTableCells'
                    ]
                },
                licenseKey: '',

            } )
            .then( editor => {
                window.editor = editor;
            } )
            .catch( error => {
                console.error( error );
            } );
    }









});

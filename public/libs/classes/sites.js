$(function(){
    var Sites = {

        index : function () {

            $(document).on('click', '.toggle-delete-site', function (e) {
                e.preventDefault();
                var site_id = $(this).data('id')

                UIkit.modal.confirm('Are you sure?', function(){
                    $.ajaxSetup({
                        headers: {
                            'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type  : 'post',
                        url   : '/template/' + site_id + '/delete',
                        cache : false,
                        dataType: 'json',

                        beforeSend : function() {
                        },

                        success : function(data) {
                            if (data.status) {
                                UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                                setTimeout(function () { $('#sites-container').html(data.html); }, 1000);
                            } else {
                                UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                            }
                        },

                        error : function() {

                        }
                    });
                });
            });

        },

        add : function () {
            $(document).on('click', '#toggle-save-input-template', function (e) {
                e.preventDefault();

                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                var form_data = new FormData($('#site-form')[0]);

                $.ajax({
                    type  : 'post',
                    url   : '/template/save',
                    data  : form_data,
                    cache : false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,

                    beforeSend : function() {
                        $('.loader').addClass('uk-icon-spinner');
                    },

                    success : function(data) {
                        $('.loader').removeClass('uk-icon-spinner');

                        if (data.status) {
                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                            setTimeout(function () { window.location = '/templates'; }, 3000);
                        } else {
                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                        }
                    },

                    error : function() {

                    }
                });

            })
        },

        update : function () {
            $(document).on('click', '#toggle-update-input-template', function (e) {
                e.preventDefault();

                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                var form_data = new FormData($('#site-form')[0]);

                $.ajax({
                    type  : 'post',
                    url   : '/template/'+ $(this).data('id') +'/edit',
                    data  : form_data,
                    cache : false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,

                    beforeSend : function() {
                    },

                    success : function(data) {
                        if (data.status) {
                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                            setTimeout(function () { window.location = '/templates'; }, 3000);
                        } else {
                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                        }
                    },

                    error : function() {

                    }
                });
            });
        }
    }

    window.Sites = Sites;
});

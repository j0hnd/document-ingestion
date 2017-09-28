$(function(){
    var Users = {

        indexUser : function () {
            $(document).on('change', '#check-all-users', function (e) {
                $(".check-user-row").prop('checked', $(this).prop("checked"));
            })
        },

        createUser : function () {

            $(document).on('submit', '#create-user-form', function (e) {
                e.preventDefault();

                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    type  : 'post',
                    url   : '/users/save',
                    data  : $(this).serialize(),
                    cache : false,
                    dataType: 'json',

                    beforeSend : function() {
                    },

                    success : function(data) {
                        if (data.status) {
                            var $create_user_modal = UIkit.modal('#create-user-container');
                            UIkit.notify(data.message, {pos: 'bottom-left'});
                            setTimeout(function(){
                                $('#create-user-form').closest('form').find("input[type=text], input[type=password], textarea").val("");
                                $create_user_modal.hide();
                                window.location = '/users'
                            }, 3000);
                        } else {
                            var msg;
                            if (typeof data.message === 'string' || data.message instanceof String) {
                                UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                            } else {
                                $.each(data.message, function (k, v) {
                                    UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + v[0], {pos: 'bottom-left'});
                                });
                            }
                        }
                    },

                    error : function() {

                    }
                });
            });

        },

        updateUser : function () {

            $(document).on('click', '#user_disable', function () {
                UIkit.modal.confirm('Are you sure?', function(){
                    $.ajaxSetup({
                        headers: {
                            'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: 'post',
                        url: '/user/' + $('#user_disable').data('id') + '/disable',
                        cache: false,
                        data: { disable: 0 },
                        dataType: 'json',

                        beforeSend: function () {
                        },

                        success: function (data) {
                            if (data.status) {
                                window.location = '/users';
                            }

                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                        }
                    });
                });
            });

            $(document).on('click', '#toggle-user-reset-pwd', function (e) {
                e.preventDefault();

                var user_id = $('#user_resetpw').data('id');

                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'post',
                    url: '/user/' + user_id + '/resetpwd',
                    cache: false,
                    data: $('#reset-password-form').serialize(),
                    dataType: 'json',

                    beforeSend: function () {
                    },

                    success: function (data) {
                        if (data.status) {
                            UIkit.notify(data.message, {pos: 'bottom-left'});
                            setTimeout(function(){
                                $('#reset-password-form').find("input[type=password]").val("");
                            }, 3000);
                        } else {
                            var msg;
                            if (typeof data.message === 'string' || data.message instanceof String) {
                                UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                            } else {
                                $.each(data.message, function (k, v) {
                                    UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + v[0], {pos: 'bottom-left'});
                                });
                            }
                        }
                    }
                });
            });

        },

        saveUserSites : function () {
            $(document).on('click','.toggle-save-user-sites',function(e){
                e.preventDefault();
                var form_data = $('#user-sites-form').serialize();
                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'post',
                    url: '/user/save_user_sites',
                    cache: false,
                    data: form_data,
                    dataType: 'json',

                    beforeSend: function () {
                    },

                    success: function (data) {
                        setTimeout(function(){
                            window.location = '/users/'+$('input[name=user_id]').val()+'/edit';
                        }, 1000);
                        UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                    }
                });
            });

            $(document).on('click','.toggle-update-site',function(e){
                e.preventDefault();
                var id = $(this).data('id');
                var user_id = $('input[name="user_id"]').val();
                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'get',
                    url: '/user/site/load/'+id,
                    cache: false,
                    data: {user_id: user_id},
                    dataType: 'json',

                    beforeSend: function () {
                    },

                    success: function (data) {
                        $("#edit-site").html(data);
                    }
                });
            });

            $(document).on('click','.toggle-show-add-site',function(e){
                e.preventDefault();
                var user_id = $('input[name=user_id]').val();
                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'get',
                    url: '/user/site/get/',
                    cache: false,
                    data: {user_id : user_id},
                    dataType: 'json',

                    beforeSend: function () {
                    },

                    success: function (data) {
                        $("#add-edit-site").html(data);
                    }
                });
            });
        },

        updateUserSites: function () {
            $(document).on('click','.toggle-update-user-sites',function(e){
                e.preventDefault();
                var form_data = $('#user-sites-form-update').serialize();
                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'put',
                    url: '/user/save_user_sites',
                    cache: false,
                    data: form_data,
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function (data) {
                        if(data.status){
                            setTimeout(function(){
                                window.location = '/users/'+$('input[name=user_id]').val()+'/edit';
                            }, 1000);
                        }
                        UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                    }
                });
            });
        },

        deleteUserSite: function () {
            $(document).on('click', '.toggle-delete-site', function () {
                var id = $(this).data('id');
                UIkit.modal.confirm('Are you sure?', function(){
                    $.ajaxSetup({
                        headers: {
                            'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: 'post',
                        url: '/user/site/delete',
                        cache: false,
                        data: { id: id },
                        dataType: 'json',

                        beforeSend: function () {
                        },

                        success: function (data) {
                            if (data.status) {
                                setTimeout(function(){
                                    window.location = '/users/'+$('input[name=user_id]').val()+'/edit';
                                }, 1000);
                            }
                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                        }
                    });
                });
            });
        }
    }

    window.Users = Users;
});

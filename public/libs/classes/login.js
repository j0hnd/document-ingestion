$(function(){
    var Login = {

        authenticateUsers : function () {
            $(document).on('click', '#authenticate-user', function (e) {

                e.preventDefault();

                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    type  : 'post',
                    url   : '/authenticate/signin',
                    data  : $('#login-form').serialize(),
                    cache : false,
                    dataType: 'json',

                    beforeSend : function() {
                    },

                    success : function(data) {
                        if (data.status) {
                            window.location = data.url;
                        } else {
                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.error, {pos: 'bottom-left'});
                        }
                    },

                    error : function() {

                    }
                });

            })
        },

        ForgotPassword : function () {
            $(document).on('click','#submit-forgot-password',function (e) {
                e.preventDefault();

                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    type  : 'post',
                    url   : '/user/forgotpassword',
                    data  : $('#forgot-password-form').serialize(),
                    cache : false,
                    dataType: 'json',

                    beforeSend : function() {
                    },

                    success : function(data) {
                        console.log(data);
                        if (data.status) {
                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + data.message, {pos: 'bottom-left'});
                            setTimeout(function () { window.location = '/sign-in'; }, 3000);
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

    window.Login = Login;
});

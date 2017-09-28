$(function(){
    var Register = {

        form : function () {

            $(document).on('click', '#register', function() {
                $.ajax({
                    url: '/process/registration',
                    data: { data: $('#frm').serialize(), _token: $('#token').val() },
                    type: 'post',
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        if (response.status) {
                            console.log(response.message);
                        } else {
                            console.log('error');
                        }
                    }
                });
            });
        }
    }

    window.Register = Register;
});
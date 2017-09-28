$(function(){
    var Test = {

        form : function () {

            $(document).on('click', '#submit', function() {
                $.ajax({
                    url: '/process/form',
                    data: { data: $('#frm').serialize(), _token: $('#token').val() },
                    type: 'post',
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        if (response.status) {
                            console.log(response.message);
                        } else {
                            console.log('uy! mali');
                        }
                    }
                });
            });
        }
    }

    window.Test = Test;
});


(function($){

    var Upload = {

        init : function()
        {
            var self = this;

        } ,

        index: function ()
        {
            var self = this;

            Dropzone.autoDiscover = false;

            var form_data   = '';
            var upload_info = null;

            var myDropzone = new Dropzone('.dropzone', {
                autoProcessQueue: true,
                maxFilesize: 500,
                addRemoveLinks: true,
                acceptedMimeTypes:'application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });

            myDropzone.on('success', function (file, response) {
                if (response.status) {
//                    form_data   = $('#attachments-container :input, #attachments-container #notes').serialize();
                    upload_info = response.data;

//                    attachment_detail_ids.push(response.data.attachment_detail_id);

//                    $.ajax({
//                        url: '/attachments/preview/' + response.data.attachment_detail_id,
//                        type: 'get',
//                        beforeSend: function () {
//                            Hippocamp.initProcess().initWrapperLoader("open");
//                        },
//                        success: function (response) {
//                            Hippocamp.initProcess().initWrapperLoader("close");
//                    
//                            if (response.status) {
//                                $('#preview').html(response.data.html);
//                            }
//                        }
//                    });
                }
            });

            //$(document).on('click', '.save-attachments', function (e) {
            //    e.preventDefault();
            //
            //    form_data = (form_data == null) ? $('#attachments-container :input, #attachments-container #notes').serialize() : form_data;
            //
            //    console.log(form_data);
            //    console.log($('input[name="attachment_name"]').val().length);
            //
            //    if (form_data.length && $('input[name="attachment_name"]').val().length) {
            //        $.ajax({
            //            url: '/attachment/save',
            //            type: 'post',
            //            data: {
            //                module: $('#module').val(),
            //                formdata: form_data,
            //                uploadinfo: upload_info,
            //                attachment_detail_ids: attachment_detail_ids,
            //                _token: $('#csrf').val()
            //            },
            //            success: function (response) {
            //                if (response.status) {
            //                    attachment_detail_ids = [];
            //                    form_data = null;
            //
            //                    $('#attachments-container').find(':input').each(function() {
            //                        switch(this.type) {
            //                            case 'text':
            //                            case 'textarea':
            //                            case 'select-one':
            //                                $(this).val('');
            //                                break;
            //                            case 'checkbox':
            //                            case 'radio':
            //                                this.checked = false;
            //                        }
            //                    });
            //
            //                    myDropzone.removeAllFiles(true);
            //                    Hippocamp.initNotification('green-notification', response.message);
            //
            //                    setTimeout(function(){ window.location = '/'+ response.data.module +'/' + response.data.id + '/?tab=attachments' }, 3000);
            //                } else {
            //                    Hippocamp.initNotification('red-notification', response.message);
            //                    form_data = null;
            //                }
            //            }
            //        });
            //    } else {
            //        Hippocamp.initNotification('red-notification', 'Missing attachment name or attachment file');
            //        form_data = null;
            //    }
            //});

            //$(document).on('click', '.update-attachments', function (e) {
            //    e.preventDefault();
            //
            //    if (form_data.length) {
            //        data = {
            //            module: $('#module').val(),
            //            formdata: form_data,
            //            uploadinfo: upload_info,
            //            attachment_detail_ids: attachment_detail_ids,
            //            id: $('#aid').val(),
            //            ownerid: $('#id').val(),
            //            _token: $('#csrf').val(),
            //            toggle: $('#toggle').val()
            //        };
            //    } else {
            //        form_data = $('#attachments-container :input, #attachments-container #notes').serialize();
            //        data = {
            //            module: $('#module').val(),
            //            formdata: form_data,
            //            id: $('#aid').val(),
            //            ownerid: $('#id').val(),
            //            _token: $('#csrf').val(),
            //            toggle: $('#toggle').val()
            //        };
            //    }
            //
            //    if (form_data.length) {
            //        Hippocamp.request().ajax(
            //            'POST' ,
            //            '/attachment/'+ $(this).data('id') +'/update',
            //            data,
            //            true ,
            //            'json' ,
            //            function(){
            //                Hippocamp.initProcess().initWrapperLoader("open");
            //            } ,
            //            function(){
            //                Hippocamp.initProcess().initWrapperLoader("close");
            //            } ,
            //            function(done){
            //                if (done.status) {
            //                    attachment_detail_ids = [];
            //                    form_data = null;
            //
            //                    myDropzone.removeAllFiles(true);
            //                    Hippocamp.initNotification('green-notification', done.message);
            //
            //                    setTimeout(function(){ window.location = '/'+ done.data.module +'/' + done.data.id + '/?tab=attachments' }, 5000);
            //                } else {
            //                    Hippocamp.initNotification('red-notification', done.message);
            //                }
            //            }
            //        );
            //    }
            //});

            return self;
        }

    }

    window.Upload = Upload;

})(jQuery);
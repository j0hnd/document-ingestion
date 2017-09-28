$(function(){
    var Documents = {

        index : function () {
            $(document).on('change', '#input_processor', function (e) {
                var upload_name = $('#template-name').val();
                var id = $(this).val();

                // read input processor details
                if (id.length) {
                    $('#template-name').val(upload_name + $('#input_processor :selected').text() + ' - ' + moment().format('DDMMMYYYY HH:mm'));

                    $.ajax({
                        url: '/template/'+ id +'/read',
                        type: 'get',
                        dataType: 'json',
                        cache: false,

                        beforeSend: function () {

                        },

                        success: function (response) {
                            $("#destination option").each(function() {
                                if (response.data) {
                                    if($(this).text().toLowerCase() == response.data.output.toLowerCase()) {
                                        $(this).attr('selected', 'selected');
                                    }
                                }
                            });
                        }
                    });
                }
            });

            $(document).on('click', '#toggle-upload', function (e) {
                var ids = $.cookie('ids');
                var id  = '';

                if (ids !== undefined && ids.length) {
                    $.each(ids.split(','), function (k, v) {
                        id += '&id[]=' + v;
                    });

                    $.ajax({
                        url: '/document/save',
                        type: 'post',
                        data: $('#upload-form').serialize() + id,
                        dataType: 'json',
                        cache: false,

                        success: function (response) {
                            if (response.status) {
                                UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                                setTimeout(function () {
                                    $.removeCookie('ids');
                                    window.location = '/queue';
                                }, 2000);
                            } else {
                                var msg;
                                if (typeof response.message === 'string' || response.message instanceof String) {
                                    UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + response.message, {pos: 'bottom-left'});
                                } else {
                                    $.each(response.message, function (k, v) {
                                        UIkit.notify('<i class="uk-icon-exclamation-circle"></i>&nbsp;' + v, {pos: 'bottom-left'});
                                    });
                                }
                            }
                        }
                    });
                } else {
                    UIkit.notify('<i class="uk-icon-warning"></i> No file to process', {pos: 'bottom-left'});
                }
            });
        },

        initList: function() {
            var filterBatch = function(filter){
                var modal = UIkit.modal.blockUI('<div class="uk-text-center"><i class="uk-icon-large uk-icon-spinner uk-icon-spin"></i></div>');
                $.ajaxSetup({
                    headers: {
                        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'get',
                    url: '/queue/filtered',
                    cache: false,
                    data: {status : filter},
                    dataType: 'json',

                    beforeSend: function () {
                        modal.show();
                    },

                    success: function (data) {
                        modal.hide();
                        $("#table-body-queue").html(data);
                    }
                });
            }

            $(document).on('click','.filters',function(e){
                e.preventDefault();
                var filter = $(this).data('filter');
                var parent = $(this).closest("li");
                $('#doc-processing-filter li').removeClass("uk-active");
                parent.addClass("uk-active");
                if(filter !== ""){
                    filterBatch(filter);
                }
            });

            $(document).on('click', '.toggle-batch-status', function (e) {
                e.preventDefault();

                var batch_id     = $(this).data('batch');
                var status       = $(this).data('status');
                var status_label = $(this).data('status-label');


                UIkit.modal.confirm("Are you sure you want to " + status_label + " this documents?", function(){
                    $.ajax({
                        url: '/batch/' + batch_id + '/status',
                        type: 'post',
                        data: { _token: $('#csrf_token').val(), status: status },
                        dataType: 'json',
                        cache: false,

                        success: function (response) {
                            if (response.status) {
                                $('#table-body-queue').html(response.data.html);
                                setTimeout(function () { window.location = '/download/' + response.zip + '/' + response.folder_name }, 2000);
                            }
                        }
                    });
                });
            });
        },

        Process : function() {
            $(document).on('click', '.toggle-document-status', function (e) {
                e.preventDefault();

                var document_details_id = $(this).data('id');
                var batch_id = $(this).data('batch');
                var document_status  = $(this).data('status');

                $.ajax({
                    url: '/document/details/' + document_details_id + '/update',
                    type: 'post',
                    data: { _token: $('#csrf_token').val(), status: document_status },
                    cache: false,
                    dataType: 'json',

                    success: function (response) {
                        if (response.status) {
                            UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                            setTimeout(function () { window.location = '/documents/'+ batch_id +'/list' }, 2000);
                        } else {
                            UIkit.notify('<i class="uk-icon-exclamation"></i> ' + response.message, {pos: 'bottom-left'});
                        }
                    }
                });

            });


            $('.zoom_01').elevateZoom({
                zoomType: "inner",
                cursor: "crosshair",
                zoomWindowFadeIn: 500,
                zoomWindowFadeOut: 750
            });
        },

        Events : function () {
            $.ajax({
                url: '/fire/events',
                dataType: 'json',
                cache: false,

                beforeSend: function () {
                    UIkit.notify('Updating queue list', {pos: 'bottom-left'});
                },

                success: function (response) {
                    if (response.status) {
                        $('#table-body-queue').html(response.data.html);
                        UIkit.notify('<i class="uk-icon-check"></i> Queue updated', {pos: 'bottom-left'});
                    } else {
                        UIkit.notify('<i class="uk-icon-exclamation"></i> ' + response.message, {pos: 'bottom-left'});
                    }
                }
            });
        }
    }

    window.Documents = Documents;
});

$(function(){
    var ids = [];
    var uploadCount = 1;
    var progressbar = $("#progressbar"),
    bar         = progressbar.find('.uk-progress-bar'),
    settings    = {
        action: '/document/uploads', // upload url

        allow : '*.(pdf|xls|xlsx)', // allow only documents

        params: { '_token':  $('#csrf_token').val() },

        loadstart: function() {
            bar.css("width", "0%").text("0%");
            progressbar.removeClass("uk-hidden");
        },

        progress: function(percent) {
            percent = Math.ceil(percent);
            bar.css("width", percent+"%").text(percent+"%");
        },

        type: 'json',

        complete: function (response) {
            if (response.status) {
                $(".filesInfo").append("<div class='uk-text-success uk-animation-shake'>"+response.data.filename+" - 100% <a href='#'><i class='uk-icon-remove'></i></a></div>");
                var x = uploadCount++;
                $(".filesCount").text("("+x + " files uploaded)");
                ids.push(response.data.id);
            }
        },

        allcomplete: function(response) {
            bar.css("width", "100%").text("100%");

            setTimeout(function(){
                progressbar.addClass("uk-hidden");
            }, 250);

            if (response.status) {
                $.cookie('ids', ids, { expire: 1 });
            } else {
                UIkit.notify('<i class="uk-icon-warning"></i> ' + response.message, {pos: 'bottom-left'});
            }

        }
    };

    var select = UIkit.uploadSelect($("#upload-select"), settings),
    drop   = UIkit.uploadDrop($("#upload-drop"), settings);

});

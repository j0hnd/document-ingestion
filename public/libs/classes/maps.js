$(function(){
    var Maps = {

        form : function () {
             

                $(document).on('keyup', '.numbers', function(event) {
                    var ext = $('#extension').val();

                    if (ext == 'pdf') {

                        var v = this.value;
                        if ($.isNumeric(v) === false) {
                            this.value = this.value.replace(/[^0-9\.]/g,'');
                        }

                    } else {

                        this.value = this.value.replace(/[^a-z\d]/, '');
                    }
                });



            $(document).on('click', '#submit', function() {

                $.ajax({
                    url:'/upload',
                    data: new FormData($("#upload_form")[0]), company,
                    dataType:'json',
                    async:false,
                    type:'post',
                    processData: false,
                    contentType: false,
                    
                    success: function(response) {

                    if (response.status) {
                            UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                            $("#company_name").val(response.company);
                            $("#name").val(response.company);
                            $("#extension").val(response.ext);
                            $('#url').val(response.file_url);
                            $('#iframe').attr('src', 'https://view.officeapps.live.com/op/embed.aspx?src='+response.file_url);

                            var result = response.get_selected;
                            var total = response.get_count;

                            var query = "";
                            for (var i = 0; i < total; i++) {

                                var a = result[i];
                                var b = JSON.stringify(a['fieldname']);
                                var str = new String();
                                str = b.toString().replace(/"/g, "");
                                var string = new String();
                                string = str.toString().replace(/\s+/g, "");
                                query += string + ',';
                            }

                            $("#query").val(query);
                            $("#count").val(total);

                            var header = response.get_header;
                            var count = response.header_count;
                            var head = "";
                            for (var i = 0; i < count; i++) {

                                var a = result[i];
                                var b = JSON.stringify(a['fieldname']);
                                var str = new String();
                                str = b.toString().replace(/"/g, "");
                                var string = new String();
                                string = str.toString().replace(/\s+/g, "");
                                head += string + ',';
                            }

                            $('#selected').val(head);

                            if (response.ext == 'pdf') {
                                jQuery('#results').hide();
                                jQuery('#upload_form').hide();
                                jQuery('#zip').hide();
                                jQuery('#view-xml-window').hide();
                                jQuery('#download-xml').hide();
                                jQuery('#back-xml').hide();

                                jQuery('#bar').show();
                                jQuery('#header').show();
                                jQuery('#textarea').show();
                                jQuery('#view-all-data').show();
                                jQuery('#form').show();
                                jQuery('#fieldname').show();
                                jQuery('#textnum').show();

                                $("#company_name").val(response.company);
                                $("#name").val(response.company);
                                $("#extension").val(response.ext);
                                $('#textarea').html(response.html);
                                $(".lined").linedtextarea();
                                $('#numlines').val(textarea.value.match(/\n/g).length + 1);

                        
                            } else if (response.ext == 'xlsx' || response.ext == 'xls') {

                                jQuery('#results').hide();
                                jQuery('#upload_form').hide();
                                jQuery('#zip').hide();
                                jQuery('#view-xml-window').hide();
                                jQuery('#download-xml').hide();
                                jQuery('#back-xml').hide();

                                jQuery('#td_iframe').show();
                                jQuery('#form').show();
                                jQuery('#header').show();
                                jQuery('#view-all-data').show();
                                jQuery('#fieldname').show();
                                jQuery('#bar').show();
                                $('#legend').text('Please enter cell id of the following:');
                                $('#legend').css('font-style', 'italic');

                            } 

                        } else {
                                    
                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i> ' + response.message, {pos: 'bottom-left'});
                        }
                    },
                });
            });
            
            $(document).on('click', '#view-all-data', function (e) {
                var company = $('#name').val();
                var ext = $('#extension').val();

                var a = confirm("Are you sure you want to view all data? your uploaded file will be deleted");
                if (a) {

                    $.ajax({
                        url: '/view-all-data',
                        data: { comp: company , extension: ext, _token: $('#token').val() },
                        type: 'post',
                        dataType:'json',
                        success: function (response) {
                            window.location.reload(true);
                        },

                    });
                    
                } else {
                    console.log("not ok");
                }

            });
           
            $(document).on('click', '#back2', function () {
                var del = confirm("Are you sure you want to go back? your update will not be saved");    
                if (del) {
                    window.location.reload(true);
                }
            });
               

            $(document).on('click', '#create-map', function() {
                var button_value = $('#create-map').val();
                var numlines = $('#numlines').val();
                var line = parseInt(numlines, 10);

                var count = $('#count').val();
                var query = $('#query').val();
                var company = $('#company_name').val();
                var extension = $('#extension').val();

                $.ajax({
                    url: '/submit-map',
                    data: {data: $('#map-details').serialize(), total: count, arr: query ,numlines: line, comp: company,ext: extension, _token: $('#token').val() },
                    dataType: 'json',
                    type:'post',
                    beforeSend: function (e) {
                        var array = query.split(',');
                        for (var i = 0; i < count; i++) {

                            var string = array[i];
                            var id = '#'+string;
                            var value = $(id).val();
                           
                            $(".numbers").change(function () {
                                if (extension == 'pdf') {
                                    if (!value) {
                                        $('#dup-'+string).show();
                                        $('#dup-'+string).css('width', "70px");
                                        $('#dup-'+string).val('* data required');
                                        $('#'+string).css("border", "2px solid red");
                                        e.abort();

                                    } else if (value > line || value == 0) {
                                        $('#dup-'+string).show();
                                        $('#dup-'+string).css('width', "160px");
                                        $('#dup-'+string).val('* valid line number is from 1-' + numlines);
                                        $('#'+string).css("border", "2px solid red");
                                        e.abort();
                                   
                                    } else {
                                        $('#dup-'+string).hide();
                                        $("#"+string).css("border", "");
                                    }

                                } else if (extension == 'xls' || extension == 'xlsx') {
                                    
                                    $(".numbers").attr("pattern", "(?=[a-zA-Z])(?=.[0-99]).{2,4}");
                                    var object = document.getElementById(string);

                                    if (!value) {
                                        $('#dup-'+string).show();
                                        $('#dup-'+string).css('width', "70px");
                                        $('#dup-'+string).val('* data required');
                                        $('#'+string).css("border", "2px solid red");
                                        e.abort();
                                    } else if (object.checkValidity() == false) {
                                        $('#dup-'+string).show();
                                        $('#dup-'+string).css('width', "75px");
                                        $('#dup-'+string).val('* invalid cell id');
                                        $('#'+string).css("border", "2px solid red");
                                        e.abort();
                                    } else {
                                        $('#dup-'+string).hide();
                                        $("#"+string).css("border", "");

                                    }

                                }

                            }).trigger("change");
                        }
                    },
                    success: function (response) {
                        
                        window.location.reload(true);
                    },
                });

            });


            $(document).on('click', '#edit', function() {
               
                var company = $(this).data('comp');
                var file = $(this).data('name');
                var file_name = prompt("Edit Company name", company);
                if (file_name) {
                
                    $.ajax({
                        url: '/update-modal',
                        data: { saved_comp: company ,input_comp: file_name, name: file,_token: $('#token').val()},
                        type: 'post',
                        dataType:'json',
                        success: function (response) {
                           if (response.status) {
                                UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                                $("#view-div").load(location.href + " #view-div");

                            } 
                        },
                    });
                }
            });



            $(document).on('click', '#delete', function() {
                var company = $(this).data('comp');
                var file_name = $(this).data('name');
                var file_mime = $(this).data('mime');

                $.ajax({
                    url: '/delete-map',
                    data: { comp: company, name: file_name, mime: file_mime ,_token: $('#token').val() },
                    type: 'post',
                    dataType:'json',
                    beforeSend: function (e) {
                        var comp = company.toUpperCase();
                        if ( confirm("Are you sure you want to delete " + comp + "?") ) {
                            console.log('yes');
                        } else {
                            e.abort();
                        }

                    },
                    success: function (response) {
                        if (response.status) {
                                UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                               
                        } else {
                                UIkit.notify('<i class="uk-icon-exclamation-circle"></i> ' + response.message, {pos: 'bottom-left'});
                        }      
                        window.location.reload(true);  
                    },

                });
            });

            $(document).on('click', '#zip', function() {

                var count_checked = $("[id='check']:checked").length; // count the checked rows
                var arr = '';
                if(count_checked == 0) 
                {
                    UIkit.notify('<i class="uk-icon-exclamation-circle"></i> ' + "No files selected", {pos: 'bottom-left'});
                } else {
                    $('input[id="check"]:checked').each(function() {
                       company = this.value;
                       arr += company+".xml,";
                    });
                
                    var array = arr.split(',');
                    array.slice(0,-1)
                    var zipname = prompt("File Name", name);

                    if (zipname) {
                        $.ajax({
                            url: '/zip',
                            data: {arr: array, zname: zipname,  _token: $('#token').val()},
                            type: 'post',
                            dataType: 'json',
                            success: function (response) {
                                if (response.status) {
                                    UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                                    window.location.replace(response.zip);                            
                                } 

                            },
                        });
                    };
                }
           
            });

            $('#check_all').click(function() {
                $(':checkbox').prop('checked',this.checked);
            });



            $(document).on('click', '#view-xml', function() {
                var company = $(this).data('comp');
                var file_name = $(this).data('name');
                var file_mime = $(this).data('mime');

                 $.ajax({
                    url: '/view-xml',
                    data: { comp: company, filename: file_name, filemime: file_mime, _token: $('#token').val() },
                    type: 'post',
                    dataType:'json',
                    success: function (response) {
                          
                        $("#div_details").load(" #div_details", function(){
                        jQuery('#results').hide();
                        jQuery('#upload_form').hide();
                        $("#create-map").attr("disabled", true); 
                        
                        if (response.ext == 'pdf') {

                        jQuery('#zip').hide();
                        jQuery('#back-xml').hide();
                        
                        jQuery('#create-map').show();
                        jQuery('#header').show();
                        jQuery('#textarea').show();
                        jQuery('#back2').show();
                        jQuery('#edit-map-details').show();
                        jQuery('#form').show();
                        jQuery('#textnum').show();
                        jQuery('#refreshtext').show();

                        // $("#url_download").val(response.url);
                        $("#company_name").val(response.company);
                        $("#name").val(response.company);
                        $("#extension").val(response.ext);
                        $('#textarea').html(response.html);
                        $(".lined").linedtextarea();
                        $('#numlines').val(textarea.value.match(/\n/g).length + 1);

                        } else if (response.ext == 'xls' || response.ext == 'xlsx') {

                            // var b = JSON.stringify(response.url['temp_path']);
                            // alert(response.url);
                            var result = response.url;
                            var str = JSON.stringify(result[0]['temp_path']);
                            var string = new String();
                            string = str.toString().replace(/"/g, "");
                            var ext = response.ext;
                            console.log(str);

                            jQuery('#zip').hide();
                            jQuery('#back-xml').hide();
                            
                            jQuery('#create-map').show();
                            jQuery('#view-xml-window').show();
                            jQuery('#download-xml').show();
                            jQuery('#td_iframe').show();
                            jQuery('#form').show();
                            jQuery('#header').show();
                            jQuery('#back2').show();
                            jQuery('#refreshtext').show();

                            $('#iframe').attr('src', 'https://view.officeapps.live.com/op/embed.aspx?src='+string);
                            $("#company_name").val(response.company);
                            $("#name").val(response.company);
                            $("#extension").val(response.ext);

                            $('#legend').text('Please enter cell id of the following:');
                            $('#legend').css('font-style', 'italic');
                        }

                        }); 
                            
                    },

                });

           });

            $(document).on('click', '#refresh', function(){
                var company = $('#company_name').val();
                var extension = $('#extension').val();

                $.ajax({
                    url: '/refresh',
                    data: {comp: company, ext: extension, _token: $('#token').val()},
                    type: 'post',
                    dataType:'json',
                    success: function(response) {
                        var header_value = response.header_value;
                        var header_fieldname = response.header_fieldname;
                        var item_value = response.item_value;
                        var item_fieldname = response.item_fieldname;

                        var header_array = header_value.split(",");
                        var header_array_name = header_fieldname.split(",");
                        var header_count = header_array.length - 1; 

                        var item_array = item_value.split(",");
                        var item_array_name = item_fieldname.split(",");
                        var item_count = item_array.length - 1;
                        

                        for (var i=0; i < header_count; i++) {
                             $("#"+header_array_name[i]).val(header_array[i]);
                             console.log("#"+header_array_name[i] + ": " + header_array[i] );
                        }

                        for (var i=0; i < item_count; i++) {
                             $("#"+item_array_name[i]).val(item_array[i]);
                             console.log("#"+item_array_name[i] + ": " + item_array[i] );
                        } 
                        $("#create-map").attr("disabled", false); 

                        var result = response.query;
                        var total = response.count;

                        var query = "";
                        for (var i = 0; i < total; i++) {

                            var a = result[i];
                            var b = JSON.stringify(a['fieldname']);
                            var str = new String();
                            str = b.toString().replace(/"/g, "");
                            var string = new String();
                            string = str.toString().replace(/\s+/g, "");
                            query += string + ',';
                        }

                        $("#query").val(query);
                        $("#count").val(total);

                        var header = response.selected;
                        var count = response.header_count;
                        var head = "";
                        for (var i = 0; i < count; i++) {

                            var a = result[i];
                            var b = JSON.stringify(a['fieldname']);
                            var str = new String();
                            str = b.toString().replace(/"/g, "");
                            var string = new String();
                            string = str.toString().replace(/\s+/g, "");
                            head += string + ',';
                        }

                        $('#selected').val(head);
                    },
                });
            });



            $(document).on('click', '#view-xml-window', function () {
                var company = $('#name').val();
                var ext = $('#extension').val();
                var url = $('#url_download').val();
                $.ajax({
                    url: '/view-xml-window',
                    data: { comp: company ,_token: $('#token').val()},
                    type: 'post',
                    dataType:'json',
                    success: function (response) {
                         
                        var header_f = response.header_field;
                        var header_v = response.header_value;

                        var item_f = response.item_field;
                        var item_v = response.item_value;

                        var headerf = header_f.split(",");
                        var headerv = header_v.split(",");
                        var headerlen = headerf.length - 1;
                        
                        var itemf = item_f.split(","); 
                        var itemv = item_v.split(",");
                        var itemlen = itemf.length - 1;


                        var xml = 
                            '&lt;<font color=#7e01ab><b>template</b></font>&gt;<br>' +
                            '&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>companyname</b></font>&gt;' + company + '&lt;/<font color=#7e01ab><b>companyname</b></font>&gt;<br>' + 
                            '&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>output</b></font>&gt;' + 'LIVE' + '&lt;/<font color=#7e01ab><b>output</b></font>&gt;<br>' + 
                            '&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>filetype</b></font>&gt;' + ext + '&lt;/<font color=#7e01ab><b>filetype</b></font>&gt;<br>' + 
                            '&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>content</b></font>&gt;<br>' +   
                            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>consumer</b></font>&gt;<br>'+ 
                            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>stores</b></font>&gt;<br>'+ 
                            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>store</b></font>&gt;<br>'+ 
                            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>headers</b></font>&gt;<br>';
                        
                        for (var i=0; i < headerlen;i++){ 
                            xml += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>'+headerf[i]+'</b></font>&gt;' + headerv[i] + '&lt;/<font color=#7e01ab><b>'+headerf[i]+'</b></font>&gt;<br>';
                        } 
                        
                        xml += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/<font color=#7e01ab><b>headers</b></font>&gt;<br>'+ 
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>items</b></font>&gt;<br>'; 
                        
                        for (var i=0; i < itemlen;i++){ 
                            xml += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;<font color=#7e01ab><b>'+itemf[i]+'</b></font>&gt;' + itemv[i] + '&lt;/<font color=#7e01ab><b>'+itemf[i]+'</b></font>&gt;<br>';
                        }              
                        
                        xml += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/<font color=#7e01ab><b>items</b></font>&gt;<br>'+ 
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/<font color=#7e01ab><b>store</b></font>&gt;<br>'+ 
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/<font color=#7e01ab><b>stores</b></font>&gt;<br>'+ 
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/<font color=#7e01ab><b>consumer</b></font>&gt;<br>' + 
                        '&nbsp;&nbsp;&nbsp;&nbsp;&lt;/<font color=#7e01ab><b>content</b></font>&gt;<br>' + 
                        '&lt;/<font color=#7e01ab><b>template</b></font>&gt;';
                        

                        
                        var new_window = window.open();
                        new_window.document.write(xml);


                    },

                });
               
            });

           


            $(document).on('click', '#fieldname', function() {
                $('#fieldname_selector').slideDown();
                 jQuery("#fieldname_selector").show();

                 $.ajax({
                    url: '/remove-header',
                    data: {_token: $('#token').val()},
                    type: 'post',
                    dataType: 'json',
                    success: function (response, e) {
                        var result = response.get_selected;
                        var total = response.total;

                        var query = "";
                        for (var i = 0; i < total; i++) {

                            var a = result[i];

                            var b = JSON.stringify(a['fieldname']);
                            var str = new String();
                            str = b.toString().replace(/"/g, "");
                            var string = new String();
                            string = str.toString().replace(/\s+/g, "");

                            query += string + ',';
                        }

                        $("#query").val(query);
                        $("#count").val(total);

                    },
                 });

            });


            $(document).on('click', '#download-xml', function (e) {
                var company = $("#company_name").val();
                var xml = company+'.xml';
                
                    
                
                    $.ajax({
                        url: '/download',
                        data: {file: xml,comp: company,  _token: $('#token').val()},
                        type: 'post',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status) {
                                UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                                window.location.replace(response.zip);                            
                            } 

                        },
                    });
                    
                

                
            });


             $(document).on('click', '#done-slider', function() {
                
                $('#fieldname_selector').slideUp();
                var company = $("#company_name").val();
                var numlines = $("#numlines").val();
                var ext = $("#extension").val();

                var fieldname = "";
                var count_checked = $("[id='header']:checked").length; // count the checked rows
                if(count_checked == 0) 
                {
                    console.log('no header selected');
                } else {
                    $('input[id="header"]:checked').each(function() {
                       fieldname += this.value + ",";
                       
                    });
                }
                $('#selected').val(fieldname);
                $.ajax({
                    url: '/get-selected',
                    data: { comp: company, selected: fieldname, _token: $('#token').val()},
                    type: 'post',
                    dataType:'json',
                    success: function (response) {
                        
                        
                        $("#name").val(company);
                        $("#company_name").val(company);
                        $("#extension").val(ext);
                        $("#numlines").val(numlines);

                    
                        var query = response.get_selected;
                        var count = response.get_count;

                        var id="";
                        for (var i = 0; i < count; i++) {
                            var a = query[i];
                            var b = JSON.stringify(a['fieldname']);
                            var str = new String();
                            str = b.toString().replace(/"/g, "");
                            var string = new String();
                            string = str.toString().replace(/\s+/g, ""); // remove space fieldname
                            id += string +",";
                            
                        }
                        var array = id.split(','); // convert to array
                        
                        $("#query").val(id);
                        $("#count").val(count);
                        $("#div_details").load(" #div_details");
                        

                    },

                });

            });

             
            $(document).on('click', '#create-fieldname', function() {
                var name = $('#txt_fieldname').val();

                $.ajax({
                    url: '/create-name',
                    data: { fieldname: name ,_token: $('#token').val()},
                    type: 'post',
                    dataType:'json',
                    success: function (response) {
                        if (response.status) {
                            UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                            $('#txt_fieldname').val('');
                            $("#div").load(location.href + " #div");

                        } else {
                            UIkit.notify('<i class="uk-icon-exclamation-circle"></i> ' + response.message, {pos: 'bottom-left'});
                        }
                    },

                });
            });


            $(document).on('click', '#delete-fieldname', function() {
                var name = $(this).data('name');
                $.ajax({
                    url: '/delete-fieldname',
                    data: { fieldname: name ,_token: $('#token').val()},
                    type: 'post',
                    dataType:'json',
                    beforeSend: function (e) {
                        var a = confirm("Are you sure you want to delete this?");
                        if (!a) {
                            e.abort();
                        }
                    },
                    success: function (response) {
                         $("#div").load(location.href + " #div");

                    },

                });
            });


            $(document).on('click', '#edit-fieldname', function () {
                var name = $(this).data('name');
                var name_update = prompt("Edit Field name", name);
                if (name_update) {
-                
                    $.ajax({
                        url: '/edit-fieldname',
                        data: { saved: name ,input: name_update,_token: $('#token').val()},
                        type: 'post',
                        dataType:'json',
                        success: function (response) {
                           if (response.status) {
                                UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                                $("#div").load(location.href + " #div");

                            } 

                        },

                    });
                }

            });


            $(document).on('click', '#add-fieldname', function () {
                var name = $(this).data('name');
                $.ajax({
                    url: '/add-fieldname',
                    data: { name: name ,_token: $('#token').val()},
                    type: 'post',
                    dataType:'json',
                    success: function (response) {
                       if (response.status) {
                            UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                            $("#div").load(location.href + " #div");
                            $("#div_selected").load(location.href + " #div_selected");

                        } 

                    },

                });

            });


            $(document).on('click', '#remove-fieldname', function () {
                var name = $(this).data('name');

                $.ajax({
                    url: '/remove-fieldname',
                    data: { name: name ,_token: $('#token').val()},
                    type: 'post',
                    dataType:'json',
                    success: function (response) {
                       if (response.status) {
                            UIkit.notify('<i class="uk-icon-check"></i> ' + response.message, {pos: 'bottom-left'});
                            $("#div").load(location.href + " #div");
                            $("#div_selected").load(location.href + " #div_selected");

                        } 

                    },

                });

            });


        }
    }

    window.Maps = Maps;
});
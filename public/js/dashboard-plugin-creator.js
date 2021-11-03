$(function(){

    var $body = $("body");

    var loader = '<div id="loader" class="overlay-loader"><img class="loader-icon spinning-cog" src="/MelisCore/assets/images/cog12.svg" data-cog="cog12"></div>';

    $body.on("click", ".melis-dashboard-plugin-creator-steps-content .btn-steps", function(){      
        var curStep = $(this).data("curstep");
        var nextStep = $(this).data("nextstep");

        var dataString = new Array;
        var mainFormData = new FormData();
        var stepForm = $(".melis-dashboard-plugin-creator-steps-content form.dashboard-plugin-creator-step-"+curStep);

        //remove highlight errors
        $("form.dashboard-plugin-creator-step-"+curStep + " .form-control").parents('.form-group').find("label").css("color","#686868");  
        
        stepForm.each(function(index, val){
            var form_name = $(this).closest('form').attr('name');    
            var countForm = $('form[name='+form_name+']').length;
            var formData = new FormData($(this)[0]);
            var formValues = formData.entries();
               
            for(var pair of formValues){      
                if(countForm > 1){
                    mainFormData.append('step-form['+index+']['+pair[0]+']',pair[1]);
                }else{
                    mainFormData.append('step-form['+pair[0]+']',pair[1]);
                } 
            }   
           
            //add empty fields to form data
            $(this).find('input[type="radio"]:not(:checked),input[type="checkbox"]:not(:checked),input[type="text"][value=""],select[value=""]').each(function(){    
                if(countForm > 1){
                    var key = 'step-form['+index+']['+this.name+']';                                              
                }else{
                    var key = 'step-form['+this.name+']';
                }          

                if(!mainFormData.has(key)){                    
                    mainFormData.append(key, "");
                }               
            }); 
        });
   
        mainFormData.append('curStep',curStep);
        mainFormData.append('nextStep',nextStep);       
       
        if ($(this).hasClass("dpc-validate")){
            mainFormData.append('validate',true);          
        }         
        
        $("#id_melisdashboardplugincreator_steps").append(loader);

        $.ajax({
            type: 'POST',
            url: '/melis/MelisDashboardPluginCreator/DashboardPluginCreator/renderDashboardPluginCreatorSteps',
            data: mainFormData,          
            dataType: "json",
            encode: true,
            cache: false,
            contentType: false,
            processData: false
        }).done(function (data) {
            
            $("#id_melisdashboardplugincreator_steps #loader img").removeClass('spinning-cog').addClass('shrinking-cog');

            setTimeout(function(){
                if(!data.errors) {    
                  
                    if(data.textMessage){
                        melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors);
                        $("#id_melisdashboardplugincreator_steps #loader").remove();
                    }else{
                        $("#id_melisdashboardplugincreator_steps").html(data.html);
                        $(".melis-dashboard-plugin-creator-steps li").removeClass("active");
                        var targetId = $("#id_melisdashboardplugincreator_steps .steps-content").attr("id");
                        $("#dpc_"+targetId).addClass("active");  
                    }

                }else{

                    //check if plugin thumbnail error is set
                    if(data.errors.dpc_plugin_upload_thumbnail){
                        //remove the thumbnail display
                        if($(".plugin_thumbnail_div").length){
                            $(".plugin_thumbnail_div").remove();
                        }
                    }
                    melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors);
                    tcHighlightErrors(0, data.errors, ".dashboard-plugin-creator-step-"+curStep);
                    $("#id_melisdashboardplugincreator_steps #loader").remove();
                }


            }, 500);

        }).fail(function(xhr, textStatus, errorThrown) {  
            console.log( translations.tr_meliscore_error_message );
        });   
    });

    /*this will highlight form fields with errors*/
    function tcHighlightErrors(success, errors, divContainer) {    
        $.each( errors, function( key, error ) {  
            //highlight the Dashboard Tab Icon Title 
            if (key.indexOf("dpc_plugin_tab_icon") >= 0){
                $("form"+divContainer + " label#dashboard_tab_icon_field_title").css("color","red");
            }             
            $("form"+divContainer + " .form-control[name='"+key +"']").parents('.form-group').find("label").css("color","red");           
        });
    } 

    /*display tab count field if multi-tab plugin type is selected*/
    $body.on("change", "#dashboard-plugin-creator-step-1 input[name='dpc_plugin_type']", function() {    
        if($('input:radio[name=dpc_plugin_type]:checked').val() == "multi"){
            $("#tab_count_div").show();           
        }else{
            $("#tab_count_div").hide();
            $("#dpc_tab_count").val('');
        }
    });

    /*this will hide or show the New Module or Existing Module Name options based on the selected plugin destination*/
    $body.on("change", "#dashboard-plugin-creator-step-1 input[name='dpc_plugin_destination']", function() {    
        if($('input:radio[name=dpc_plugin_destination]:checked').val() == "new_module"){
            $("#dpc_new_module_name").parents('.form-group').show();
            $("#dpc_existing_module_name").parents('.form-group').hide();   
            $("#dpc_existing_module_name option").prop("selected", false);        
        }else{
            $("#dpc_new_module_name").parents('.form-group').hide();
            $("#dpc_new_module_name").val('');
            $("#dpc_existing_module_name").parents('.form-group').show();    
        }
    });
 
    /*manually sets the checked properties of Activate Plugin checkbox in the step 5 form*/
    $body.on("click", ".melis-dpc-final-content .fa", function(){
        if ($(this).hasClass("fa-check-square-o")){
            // unChecking
            $(this).addClass("fa-square-o");
            $(this).removeClass("text-success");
            $(this).removeClass("fa-check-square-o");
            $(this).next("input").attr("checked", false);
        }else{
            // Checking
            $(this).removeClass("fa-square-o");
            $(this).addClass("fa-check-square-o");
            $(this).addClass("text-success");
            $(this).next("input").attr("checked", true);
        }
    });
  
    /*when dashboard plugin creator tab is closed, delete the temp thumbnail folder for the session if there are any*/
    $body.on("click", ".close-tab", function(e){  
        $.ajax({
            type: 'POST',
            url: '/melis/MelisDashboardPluginCreator/DashboardPluginCreator/removeTempThumbnailDir',
            data: {},          
            dataType: "text",           
        }).done(function (data) {                   
        }).fail(function(xhr, textStatus, errorThrown) {  
            console.log( translations.tr_meliscore_error_message );
        }); 
    }); 

    /*upload plugin thumbnail*/
    $body.on("change", "#dpc_plugin_upload_thumbnail", function(e){                  
        var uploadUrl = '/melis/MelisDashboardPluginCreator/DashboardPluginCreator/processUpload';        
        var form = $("#id-dashboard-plugin-creator-thumbnail-upload-form")[0]; // You need to use standard javascript object here
        var uploadFormData = new FormData(form);
        uploadFormData.append('file', $('input[type=file]')[0].files[0]); 
        
        melisCoreTool.pending(".btn-steps");

        $.ajax({
            type: 'POST',
            url:  uploadUrl,
            data: uploadFormData,          
            dataType: "json",
            encode: true,
            cache: false,
            contentType: false,
            processData: false,
            async:true,
            xhr: function () {
                var fileXhr = $.ajaxSettings.xhr();
                    if (fileXhr.upload) {
                        fileXhr.upload.addEventListener('progress', pluginCreatorTool.progress, false);
                    }
                    return fileXhr;
            }
        }).done(function (data) {
           
            if(data.success){   
                $("div.progressContent").addClass("hidden");

                //remove highlight errors
                $("form#id-dashboard-plugin-creator-thumbnail-upload-form .form-control").parents('.form-group').find("label").css("color","#686868");  

                //show preview after successful upload                   
                var uploadArea = $("#pluginThumbnailUploadArea");
                var imageHolder = $(".plugin_thumbnail_div");
                
                if(imageHolder.length){                   
                    imageHolder.empty();
                }else{                    
                    //append image holder to the upload area div
                    $('<div class="col-xs-12 col-lg-6"><div class="plugin_thumbnail_div"></div></div>').appendTo(uploadArea);     
                }   
               
                $("<img />", {
                    "src": data.pluginThumbnail,
                    "class": "plugin_thumbnail"
                }).appendTo('.plugin_thumbnail_div');

                //append view and remove thumbnail icons
                $('<div class="hover-details">'+
                    '<div class="me-p-btn-cont">'+
                        '<a id="plugin-thumbnail-eye" class="viewImageDocument" href="" target="_blank">'+
                            '<i class="fa fa-eye"></i>'+
                        '</a>'+
                        '<a id="removePluginThumbnail" data-type="image">'+
                            '<i class="fa fa-times"></i>' + 
                        '</a>'+
                    '</div></div>').appendTo('.plugin_thumbnail_div');     

                //set href src to view icon
                $("#plugin-thumbnail-eye").attr('href', data.pluginThumbnail);    
                imageHolder.show();
                   
            }else{       

                $("div.progressContent").addClass("hidden");

                if($('.plugin_thumbnail_div').length){
                    $('.plugin_thumbnail_div').remove();
                }
                
                melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors);
                tcHighlightErrors(0, data.errors, ".dashboard-plugin-creator-step-2");
            }
            
            melisCoreTool.done(".btn-steps");

        }).fail(function(xhr, textStatus, errorThrown) {  
            console.log( translations.tr_meliscore_error_message );
        });    
      
    });

    /*when remove icon is clicked, remove the uploaded thumbnail from the session*/
    $body.on("click", ".melis-dashboard-plugin-creator-steps-content #removePluginThumbnail", function() { 
        melisCoreTool.pending("#removePluginThumbnail");
        melisCoreTool.confirm(
            translations.tr_melisdashboardplugincreator_common_label_yes,
            translations.tr_melisdashboardplugincreator_common_label_no,
            translations.tr_melisdashboardplugincreator_delete_plugin_thumbnail_title,
            translations.tr_melisdashboardplugincreator_delete_plugin_thumbnail_confirm,              
            function() {   
                var ajaxUrl = '/melis/MelisDashboardPluginCreator/DashboardPluginCreator/removePluginThumbnail';

                $.ajax({
                    type: 'POST',
                    url: ajaxUrl,
                    data: null,
                    dataType: 'json',
                    encode: true
                }).done(function (data) {                        
                    if(data.success) {            
                        $('#dpc_plugin_upload_thumbnail').val('');
                        $('.plugin_thumbnail_div').remove();         
                                                                              
                    } else {
                        melisHelper.melisKoNotification(translations.tr_melisdashboardplugincreator_delete_plugin_thumbnail_title, translations.tr_melisdashboardplugincreator_delete_plugin_thumbnail_error, null);
                    }
                    melisCore.flashMessenger();
                }).fail(function () {
                    alert( translations.tr_meliscore_error_message );
                });                              
          });

        melisCoreTool.done("#removePluginThumbnail");
    });
});

/*ref: newstool.js*/
var pluginCreatorTool = {  
    progress: function progress(e) {
        var progressContent = $("div.progressContent");
            progressContent.removeClass("hidden");

        var progressBar = $("div.progressContent > div.progress > div.progress-bar");
            progressBar.attr("aria-valuenow", 0).css("width", '0%');
            
        var status = $("div.progressContent > div.progress > span.status");
            status.html("");

        if ( e.lengthComputable ) {
            var max         = e.total,
                current     = e.loaded,
                percentage  = (current * 100) / max;

                progressBar.attr("aria-valuenow", percentage);
                progressBar.css("width", percentage + "%");

                if (percentage > 100 ) {                   
                    progressContent.addClass("hidden");
                }
                else {
                    status.html(Math.round(percentage) + "%");
                }
        }else{
            alert('not computable');
        }
    }
};
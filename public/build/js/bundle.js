$(function(){var e=$("body");e.on("click",".melis-dashboard-plugin-creator-steps-content .btn-steps",function(){var e=$(this).data("curstep"),a=$(this).data("nextstep"),t=(new Array,new FormData),s=$(".melis-dashboard-plugin-creator-steps-content form.dashboard-plugin-creator-step-"+e);$("form.dashboard-plugin-creator-step-"+e+" .form-control").parents(".form-group").find("label").css("color","#686868"),s.each(function(e,a){var s=$(this).closest("form").attr("name"),o=$("form[name="+s+"]").length,r=new FormData($(this)[0]).entries();for(var n of r)o>1?t.append("step-form["+e+"]["+n[0]+"]",n[1]):t.append("step-form["+n[0]+"]",n[1]);$(this).find('input[type="radio"]:not(:checked),input[type="checkbox"]:not(:checked),input[type="text"][value=""],select[value=""]').each(function(){if(o>1)var a="step-form["+e+"]["+this.name+"]";else a="step-form["+this.name+"]";t.has(a)||t.append(a,"")})}),t.append("curStep",e),t.append("nextStep",a),$(this).hasClass("dpc-validate")&&t.append("validate",!0),$("#id_melisdashboardplugincreator_steps").append('<div id="loader" class="overlay-loader"><img class="loader-icon spinning-cog" src="/MelisCore/assets/images/cog12.svg" data-cog="cog12"></div>'),$.ajax({type:"POST",url:"/melis/MelisDashboardPluginCreator/DashboardPluginCreator/renderDashboardPluginCreatorSteps",data:t,dataType:"json",encode:!0,cache:!1,contentType:!1,processData:!1}).done(function(a){$("#id_melisdashboardplugincreator_steps #loader img").removeClass("spinning-cog").addClass("shrinking-cog"),setTimeout(function(){if(a.errors)a.errors.dpc_plugin_upload_thumbnail&&$(".plugin_thumbnail_div").length&&$(".plugin_thumbnail_div").empty(),melisHelper.melisKoNotification(a.textTitle,a.textMessage,a.errors),s=a.errors,o=".dashboard-plugin-creator-step-"+e,$.each(s,function(e,a){e.indexOf("dpc_plugin_tab_icon")>=0&&$("form"+o+" label#dashboard_tab_icon_field_title").css("color","red"),$("form"+o+" .form-control[name='"+e+"']").parents(".form-group").find("label").css("color","red")}),$("#id_melisdashboardplugincreator_steps #loader").remove();else if(a.textMessage)melisHelper.melisKoNotification(a.textTitle,a.textMessage,a.errors),$("#id_melisdashboardplugincreator_steps #loader").remove();else{$("#id_melisdashboardplugincreator_steps").html(a.html),$(".melis-dashboard-plugin-creator-steps li").removeClass("active");var t=$("#id_melisdashboardplugincreator_steps .steps-content").attr("id");$("#dpc_"+t).addClass("active")}var s,o},500)}).fail(function(e,a,t){console.log(translations.tr_meliscore_error_message)})}),e.on("change","#dashboard-plugin-creator-step-1 input[name='dpc_plugin_type']",function(){"multi"==$("input:radio[name=dpc_plugin_type]:checked").val()?$("#tab_count_div").show():$("#tab_count_div").hide()}),e.on("change","#dashboard-plugin-creator-step-1 input[name='dpc_plugin_destination']",function(){"new_module"==$("input:radio[name=dpc_plugin_destination]:checked").val()?($("#dpc_new_module_name").parents(".form-group").show(),$("#dpc_existing_module_name").parents(".form-group").hide()):($("#dpc_new_module_name").parents(".form-group").hide(),$("#dpc_existing_module_name").parents(".form-group").show())}),e.on("keyup",'input[name="dpc_tab_count"]',function(e){/\D/g.test(this.value)&&(this.value=this.value.replace(/\D/g,""))}),e.on("click",".melis-dpc-final-content .fa",function(){$(this).hasClass("fa-check-square-o")?($(this).addClass("fa-square-o"),$(this).removeClass("text-success"),$(this).removeClass("fa-check-square-o"),$(this).next("input").attr("checked",!1)):($(this).removeClass("fa-square-o"),$(this).addClass("fa-check-square-o"),$(this).addClass("text-success"),$(this).next("input").attr("checked",!0))}),e.on("click",".close-tab",function(e){$.ajax({type:"POST",url:"/melis/MelisDashboardPluginCreator/DashboardPluginCreator/removeTempThumbnail",data:{},dataType:"text"}).done(function(e){}).fail(function(e,a,t){console.log(translations.tr_meliscore_error_message)})})});

var WatuPRO={};WatuPRO.forceSubmit=false;WatuPRO.changeQCat=function(e){if(e.value=="-1")jQuery("#newCat").show();else jQuery("#newCat").hide()};WatuPRO.current_question=1;WatuPRO.total_questions=0;WatuPRO.mode="show";WatuPRO.checkAnswer=function(e,t){this.answered=false;var t=t||WatuPRO.qArr[WatuPRO.current_question-1];this.answered=this.isAnswered(t);if(!this.answered&&e){if(jQuery.inArray(t,WatuPRO.requiredIDs)!=-1){alert(watupro_i18n.answering_required);return false}if(!confirm(watupro_i18n.did_not_answer)){e.preventDefault();e.stopPropagation();return false}}return true};WatuPRO.isAnswered=function(e){var t=false;if(e==0)return true;var n=jQuery("#answerType"+e).val();if(n=="textarea"){if(jQuery("#textarea_q_"+e).val()!="")return true}jQuery(".answerof-"+e).each(function(e){if(n=="radio"||n=="checkbox"){if(this.checked)t=true}if(n=="gaps"){if(this.value)t=true}});return t};WatuPRO.nextQuestion=function(e,t){var t=t||"next";if(t=="next"){if(!WatuPRO.checkAnswer(e))return false}jQuery("#question-"+WatuPRO.current_question).hide();questionID=jQuery("#qID_"+WatuPRO.current_question).val();if(t=="next")WatuPRO.current_question++;else WatuPRO.current_question--;jQuery("#question-"+WatuPRO.current_question).show();if(WatuPRO.total_questions<=WatuPRO.current_question){jQuery("#next-question").hide();jQuery("#action-button").show();if(jQuery("#WTPReCaptcha").length)jQuery("#WTPReCaptcha").show()}else{jQuery("#next-question").show();if(jQuery("#WTPReCaptcha").length)jQuery("#WTPReCaptcha").hide()}if(WatuPRO.current_question>1)jQuery("#prev-question").show();else jQuery("#prev-question").hide();if(jQuery("#questionWrap-"+WatuPRO.current_question).is(":hidden")){jQuery("#liveResultBtn").hide()}else{if(jQuery("#liveResultBtn").length)jQuery("#liveResultBtn").show()}var n={exam_id:WatuPRO.exam_id,question_id:questionID,action:"watupro_store_details"};n=WatuPRO.completeData(n);jQuery.post(WatuPRO.siteURL,n)};WatuPRO.submitResult=function(e){var t=true;this.curCatPage=this.curCatPage||1;if(this.examMode==0&&this.total_questions>this.current_question)t=false;if(this.examMode==2&&this.curCatPage<this.numCats)t=false;if(!WatuPRO.forceSubmit&&!t&&!confirm(watupro_i18n.not_last_page))return false;for(i=0;i<WatuPRO.requiredIDs.length;i++){if(!this.isAnswered(WatuPRO.requiredIDs[i])){alert(watupro_i18n.missed_required_question);return false}}if(jQuery("#timerDiv").length>0){jQuery("#timerDiv").hide();clearTimeout(WatuPRO.timerID)}if(jQuery("#watuproTakerEmail").length){if(jQuery("#watuproTakerEmail").val()==""){alert(watupro_i18n.email_required);jQuery("#watuproTakerEmail").focus();return false}}jQuery("#quiz-"+WatuPRO.exam_id).hide();jQuery("#submittingExam"+WatuPRO.exam_id).show();jQuery("html, body").animate({scrollTop:jQuery("#watupro_quiz").offset().top-50},1e3);jQuery("#action-button").val(watupro_i18n.please_wait);jQuery("#action-button").attr("disabled",true);var n={action:"watupro_submit",quiz_id:WatuPRO.exam_id,"question_id[]":WatuPRO.qArr,watupro_questions:jQuery("#quiz-"+WatuPRO.exam_id+" input[name=watupro_questions]").val()};n=WatuPRO.completeData(n);n["start_time"]=jQuery("#startTime").val();if(jQuery("#WTPReCaptcha").length>0){jQuery("#quiz-"+WatuPRO.exam_id).show();n["recaptcha_challenge_field"]=jQuery("#quiz-"+WatuPRO.exam_id+" input[name=recaptcha_challenge_field]").val();n["recaptcha_response_field"]=jQuery("#quiz-"+WatuPRO.exam_id+" input[name=recaptcha_response_field]").val()}try{jQuery.ajax({type:"POST",url:WatuPRO.siteURL,data:n,success:WatuPRO.success,error:WatuPRO.errHandle,cache:false,dataType:"text"})}catch(e){alert(e)}};WatuPRO.completeData=function(e){for(x=0;x<WatuPRO.qArr.length;x++){var t=WatuPRO.qArr[x];var n=".answerof-"+WatuPRO.qArr[x];var r="answer-"+WatuPRO.qArr[x];var i=Array();var s=0;var o=jQuery("#answerType"+t).val();if(o=="textarea"){i[0]=jQuery("#textarea_q_"+WatuPRO.qArr[x]).val()}else{jQuery(n).each(function(){if(jQuery(this).is(":checked")||o=="gaps"){i[s]=this.value;s++}})}e[r+"[]"]=i}if(jQuery("#watuproTakerEmail").length)e["taker_email"]=jQuery("#watuproTakerEmail").val();return e};WatuPRO.success=function(e){if(e.indexOf("WATUPRO_CAPTCHA:::")>-1){parts=e.split(":::");alert(parts[1]);jQuery("#action-button").val(watupro_i18n.try_again);jQuery("#action-button").removeAttr("disabled");return false}if(e.indexOf("WATUPRO_REDIRECT:::")>-1){parts=e.split(":::");window.location=parts[1];return true}jQuery("#watupro_quiz").html(e)};WatuPRO.errHandle=function(e,t){jQuery("#watupro_quiz").html("Error Occured:"+t+" "+e.statusText);jQuery("#action-button").val(watupro_i18n.try_again);jQuery("#action-button").removeAttr("disabled")};WatuPRO.initWatu=function(){jQuery("#question-1").show();WatuPRO.total_questions=jQuery(".watu-question").length;if(WatuPRO.total_questions==1){jQuery("#next-question").hide();jQuery("#prev-question").hide();jQuery("#show-answer").hide()}else{jQuery("#next-question").click(WatuPRO.nextQuestion)}};WatuPRO.takingDetails=function(e,t){t=t||"";tb_show("Taking Details",t+"admin-ajax.php?action=watupro_taking_details&id="+e,t+"admin-ajax.php")};WatuPRO.nextCategory=function(e,t){this.curCatPage=this.curCatPage||1;if(t)this.curCatPage++;else this.curCatPage--;jQuery(".watupro_catpage").hide();jQuery("#catDiv"+this.curCatPage).show();if(this.curCatPage>=e){jQuery("#watuproNextCatButton").hide();jQuery("#action-button").show();if(jQuery("#WTPReCaptcha").length)jQuery("#WTPReCaptcha").show()}else{jQuery("#watuproNextCatButton").show();if(jQuery("#WTPReCaptcha").length)jQuery("#WTPReCaptcha").hide()}if(this.curCatPage<=1)jQuery("#watuproPrevCatButton").hide();else jQuery("#watuproPrevCatButton").show();jQuery("html, body").animate({scrollTop:jQuery("#watupro_quiz").offset().top-50},1e3)};WatuPRO.liveResult=function(){questionID=jQuery("#qID_"+WatuPRO.current_question).val();if(!WatuPRO.isAnswered(questionID)){alert(watupro_i18n.please_answer);return false}jQuery("#questionWrap-"+WatuPRO.current_question).hide();jQuery("#liveResult-"+WatuPRO.current_question).show();jQuery("#liveResultBtn").hide();var e={action:"watupro_liveresult",quiz_id:WatuPRO.exam_id,question_id:questionID,question_num:WatuPRO.current_question,watupro_questions:jQuery("#quiz-"+WatuPRO.exam_id+" input[name=watupro_questions]").val()};e=WatuPRO.completeData(e);jQuery.post(WatuPRO.siteURL,e,function(e){jQuery("#liveResult-"+WatuPRO.current_question).html(e)})};WatuPRO.InitializeTimer=function(e){jQuery("#timeNag").hide();jQuery("#timerDiv").show();jQuery("#watupro_quiz").show();if(jQuery("#timerRuns").length)jQuery("#timerRuns").hide();data={exam_id:WatuPRO.exam_id,action:"watupro_initialize_timer"};jQuery.post(WatuPRO.siteURL,data,function(e){parts=e.split("<!--WATUPRO_TIME-->");jQuery("#startTime").val(parts[1])});WatuPRO.secs=e;WatuPRO.StopTheClock();WatuPRO.StartTheTimer()};WatuPRO.StopTheClock=function(){if(WatuPRO.timerRunning);clearTimeout(WatuPRO.timerID);WatuPRO.timerRunning=false};WatuPRO.StartTheTimer=function(){if(WatuPRO.secs<=0){WatuPRO.StopTheClock();document.getElementById("timerDiv").innerHTML="<h2 style='color:red';>"+watupro_i18n.time_over+"</h2>";WatuPRO.forceSubmit=true;WatuPRO.submitResult()}else{if(WatuPRO.secs<60)secsText=WatuPRO.secs+" "+watupro_i18n.seconds;else{var e=WatuPRO.secs%60;var t=(WatuPRO.secs-e)/60;if(t<60){secsText=t+" "+watupro_i18n.minutes_and+" "+e+" "+watupro_i18n.seconds}else{var n=t%60;var r=(t-n)/60;secsText=r+watupro_i18n.hours+" "+n+" "+watupro_i18n.minutes_and+" "+e+" "+watupro_i18n.seconds}}document.getElementById("timerDiv").innerHTML=watupro_i18n.time_left+" "+secsText;WatuPRO.secs=WatuPRO.secs-1;WatuPRO.timerRunning=true;WatuPRO.timerID=self.setTimeout("WatuPRO.StartTheTimer()",WatuPRO.delay)}};jQuery(document).ready(WatuPRO.initWatu)
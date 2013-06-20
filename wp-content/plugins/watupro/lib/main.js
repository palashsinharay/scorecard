// main-min.js minified by http://jscompress.com/
var WatuPRO={};
WatuPRO.forceSubmit = false; // used in the timer

WatuPRO.changeQCat = function(item) {
	if(item.value=="-1") jQuery("#newCat").show();
	else jQuery("#newCat").hide();
}

// initialize vars
WatuPRO.current_question = 1;
WatuPRO.total_questions = 0;
WatuPRO.mode = "show";

WatuPRO.checkAnswer = function(e, questionID) {
	this.answered = false;
	var questionID = questionID || WatuPRO.qArr[WatuPRO.current_question-1];
    
  this.answered = this.isAnswered(questionID); 
  
	if(!this.answered && e) {
		// if required, don't let go further
		if(jQuery.inArray(questionID, WatuPRO.requiredIDs)!=-1) {
			alert(watupro_i18n.answering_required);
			return false;
		}		
		
		if(!confirm(watupro_i18n.did_not_answer)) {			
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	}
	return true;
}

// checks if a question is answered
WatuPRO.isAnswered = function(questionID) {
	var isAnswered = false;
	if(questionID==0) return true;
	var answerType = jQuery('#answerType'+questionID).val();	
		
	if(answerType=='textarea') {
      // in this case it's answered in the textarea        
      if(jQuery("#textarea_q_"+questionID).val()!="") return true;
  }

	// now browse through these with multiple answers
	jQuery(".answerof-" + questionID).each(function(i) {
		if(answerType=='radio' || answerType=='checkbox') {			
			if(this.checked) isAnswered=true;
		}
		
		if(answerType=='gaps') {
			if(this.value) isAnswered=true;
		}		
	});
	
	return isAnswered;
}

// will serve for next and previous at the same time
WatuPRO.nextQuestion = function(e, dir) {
	var dir = dir || 'next';
	
	if(dir=='next') {
		if(!WatuPRO.checkAnswer(e)) return false;
	}

	jQuery("#question-" + WatuPRO.current_question).hide();

  questionID=jQuery("#qID_"+WatuPRO.current_question).val();	
	
	if(dir=='next') WatuPRO.current_question++;
	else WatuPRO.current_question--;
	
	jQuery("#question-" + WatuPRO.current_question).show();

	// show/hide next/submit button
	if(WatuPRO.total_questions <= WatuPRO.current_question) {
		jQuery("#next-question").hide();		
		jQuery('#action-button').show();
		if(jQuery('#WTPReCaptcha').length) jQuery('#WTPReCaptcha').show(); 
	}
	else {
		jQuery("#next-question").show();		
		if(jQuery('#WTPReCaptcha').length) jQuery('#WTPReCaptcha').hide();
	}
	
	// show/hide previous button
	if(WatuPRO.current_question>1) jQuery('#prev-question').show();
	else jQuery('#prev-question').hide();
	
	// show/hide liveResult toggle if any
	if(jQuery('#questionWrap-'+WatuPRO.current_question).is(':hidden')) {
		jQuery('#liveResultBtn').hide();
	} else {
		if(jQuery('#liveResultBtn').length)  jQuery('#liveResultBtn').show();
	}
	
	// in the backend call ajax to store incomplete taking
	var data = {"exam_id": WatuPRO.exam_id, "question_id": questionID, 'action': 'watupro_store_details'};
	data=WatuPRO.completeData(data);
	jQuery.post(WatuPRO.siteURL, data);
}

// final submit exam method
// examMode - 1 is single page, 2 per category, 0 - per question
WatuPRO.submitResult = function(e) {   
	// if we are on paginated quiz and not on the last page, ask if you are sure to submit
	var okToSubmit = true;	
	this.curCatPage = this.curCatPage || 1;
	if(this.examMode == 0 && this.total_questions > this.current_question) okToSubmit = false;
	if(this.examMode == 2 && this.curCatPage < this.numCats) okToSubmit = false;
	if(!WatuPRO.forceSubmit && !okToSubmit && !confirm(watupro_i18n.not_last_page)) return false;
	
	// check for missed required questions
	for(i=0; i<WatuPRO.requiredIDs.length; i++) {			 	
		 if(!this.isAnswered(WatuPRO.requiredIDs[i])) {
		 		alert(watupro_i18n.missed_required_question);
		 		return false;
		 }
	}  	

	// hide timer when submitting
	if(jQuery('#timerDiv').length>0) {
		jQuery('#timerDiv').hide();
		clearTimeout(WatuPRO.timerID);
	}
	
	// if email is asked for, it shouldn't be empty
	if(jQuery('#watuproTakerEmail').length) {
		if(jQuery('#watuproTakerEmail').val() == '') {
			alert(watupro_i18n.email_required);
			jQuery('#watuproTakerEmail').focus();
			return false;
		}
	}
	
	// all OK, let's hide the form
	jQuery('#quiz-'+WatuPRO.exam_id).hide();
	jQuery('#submittingExam'+WatuPRO.exam_id).show();
	jQuery('html, body').animate({
   		scrollTop: jQuery('#watupro_quiz').offset().top - 50
   	}, 1000);   
	
	// change text and disable submit button
	jQuery("#action-button").val(watupro_i18n.please_wait);
	jQuery("#action-button").attr("disabled", true);
	
	var data = {"action":'watupro_submit', "quiz_id": WatuPRO.exam_id, 'question_id[]': WatuPRO.qArr,		
		"watupro_questions":  jQuery('#quiz-'+WatuPRO.exam_id+' input[name=watupro_questions]').val()};
	data=WatuPRO.completeData(data);
	
	data['start_time']=jQuery('#startTime').val();	
	
	// if captcha is available, add to data
	if(jQuery('#WTPReCaptcha').length>0) {
		jQuery('#quiz-'+WatuPRO.exam_id).show();
		data['recaptcha_challenge_field'] = jQuery('#quiz-' + WatuPRO.exam_id + ' input[name=recaptcha_challenge_field]').val();
		data['recaptcha_response_field'] = jQuery('#quiz-' + WatuPRO.exam_id + ' input[name=recaptcha_response_field]').val();
	}
	
	try{
	    jQuery.ajax({ "type": 'POST', "url": WatuPRO.siteURL, "data": data, "success": WatuPRO.success, "error": WatuPRO.errHandle, "cache": false, dataType: "text"  });
	}catch(e){ alert(e)}
}

// adds the question answers to data
WatuPRO.completeData = function(data) {
   for(x=0; x<WatuPRO.qArr.length; x++) {
    var questionID = WatuPRO.qArr[x];  
		var ansgroup = '.answerof-'+WatuPRO.qArr[x];
		var fieldName = 'answer-'+WatuPRO.qArr[x];
		var ansvalues= Array();
		var i=0;
    var answerType = jQuery('#answerType'+questionID).val();
    
    if(answerType == 'textarea') {
    	ansvalues[0]=jQuery('#textarea_q_'+WatuPRO.qArr[x]).val();
    }    
	  else {
	  	jQuery(ansgroup).each( function(){	  		
				if( jQuery(this).is(':checked') || answerType=='gaps') {
					ansvalues[i] = this.value;
					i++;
				}
			}); 
	  }  
		
		data[fieldName+'[]'] = ansvalues;
	}
	
	// user email if any
	if(jQuery('#watuproTakerEmail').length) data['taker_email'] = jQuery('#watuproTakerEmail').val();
	
	return data;
}

WatuPRO.success = function(r) {  
	 // first check for recaptcha error, if yes, do not replace the HTML
	 // but display the error in alert and return false;
	 if(r.indexOf('WATUPRO_CAPTCHA:::')>-1) {
	 		parts = r.split(":::");
	 		alert(parts[1]);
	 		jQuery("#action-button").val(watupro_i18n.try_again);
			jQuery("#action-button").removeAttr("disabled");
	 		return false;
	 }
	 
	 // redirect?
	 if(r.indexOf('WATUPRO_REDIRECT:::')>-1) {
	 		parts = r.split(":::");
	 		window.location = parts[1];
	 		return true;
	 }

   jQuery('#watupro_quiz').html(r); 
}

WatuPRO.errHandle = function(xhr, msg){ 
	jQuery('#watupro_quiz').html('Error Occured:'+msg+" "+xhr.statusText);
	jQuery("#action-button").val(watupro_i18n.try_again);
	jQuery("#action-button").removeAttr("disabled");
}

// initialization
WatuPRO.initWatu = function() {
	jQuery("#question-1").show();
	WatuPRO.total_questions = jQuery(".watu-question").length;

	if(WatuPRO.total_questions == 1) {		
		jQuery("#next-question").hide();
		jQuery("#prev-question").hide();
		jQuery("#show-answer").hide();

	} else {
		jQuery("#next-question").click(WatuPRO.nextQuestion);
	}
}

WatuPRO.takingDetails = function(id, adminURL) {
	adminURL = adminURL || "";
	tb_show("Taking Details", adminURL + "admin-ajax.php?action=watupro_taking_details&id="+id, adminURL + "admin-ajax.php");
}

// show next page when quiz is paginated per category
WatuPRO.nextCategory = function(numCats, dir) {	
	 this.curCatPage = this.curCatPage || 1;
	 if(dir) this.curCatPage++;
	 else this.curCatPage--;
	 
	 jQuery('.watupro_catpage').hide();	
	 jQuery('#catDiv' + this.curCatPage).show();	 
	 
	 if(this.curCatPage >= numCats) {	 	  
	 	  jQuery('#watuproNextCatButton').hide();
	 	  jQuery('#action-button').show();
	 	  if(jQuery('#WTPReCaptcha').length) jQuery('#WTPReCaptcha').show(); 
	 }	 
	 else {	 		
	 	  jQuery('#watuproNextCatButton').show();
	 	  if(jQuery('#WTPReCaptcha').length) jQuery('#WTPReCaptcha').hide(); 
	 }
	 
	 if(this.curCatPage <= 1) jQuery('#watuproPrevCatButton').hide();
	 else jQuery('#watuproPrevCatButton').show();
	 
	 jQuery('html, body').animate({
   		scrollTop: jQuery('#watupro_quiz').offset().top - 50
   	}, 1000);   
}

// displays result immediatelly after replying
WatuPRO.liveResult = function() {
	questionID=jQuery("#qID_"+WatuPRO.current_question).val();	
	if(!WatuPRO.isAnswered(questionID)) {
		alert(watupro_i18n.please_answer);
		return false;
	}	
	
	jQuery('#questionWrap-'+WatuPRO.current_question).hide();
	jQuery('#liveResult-'+WatuPRO.current_question).show();
	jQuery('#liveResultBtn').hide();
	
	// now send ajax request and load the result
	var data = {"action":'watupro_liveresult', "quiz_id": WatuPRO.exam_id, 'question_id': questionID, 
		'question_num': WatuPRO.current_question, "watupro_questions":  jQuery('#quiz-'+WatuPRO.exam_id+' input[name=watupro_questions]').val() };
	data=WatuPRO.completeData(data);
	
	jQuery.post(WatuPRO.siteURL, data, function(msg){
	 	jQuery('#liveResult-'+WatuPRO.current_question).html(msg);
	});
}

/********************************************************************************/
// Timer related functions
WatuPRO.InitializeTimer = function(timeLimit) {
  // Set the length of the timer, in seconds
  jQuery('#timeNag').hide();
	jQuery('#timerDiv').show();
	jQuery('#watupro_quiz').show();
	if(jQuery('#timerRuns').length) jQuery('#timerRuns').hide();
	
	// make ajax call for two things:
	// 1. to get the server time
	// 2. if the user is logged in, to set it as their variable
	data={exam_id: WatuPRO.exam_id, 'action':'watupro_initialize_timer'};
	jQuery.post(WatuPRO.siteURL, data, function(msg){
		parts=msg.split("<!--WATUPRO_TIME-->");		
		jQuery('#startTime').val(parts[1]);
	});
	
    WatuPRO.secs = timeLimit;
    WatuPRO.StopTheClock();
    WatuPRO.StartTheTimer();	
}

WatuPRO.StopTheClock = function() {
    if(WatuPRO.timerRunning);
    clearTimeout(WatuPRO.timerID);
    WatuPRO.timerRunning = false;
}

WatuPRO.StartTheTimer = function() {
    if (WatuPRO.secs<=0) {
        WatuPRO.StopTheClock();
        document.getElementById('timerDiv').innerHTML="<h2 style='color:red';>" + watupro_i18n.time_over + "</h2>";
        WatuPRO.forceSubmit = true;
				WatuPRO.submitResult();
    }
    else {
		// turn seconds into minutes and seconds
		if(WatuPRO.secs<60) secsText=WatuPRO.secs+" " + watupro_i18n.seconds;
		else {
			var secondsLeft=WatuPRO.secs%60;
			var mins=(WatuPRO.secs-secondsLeft)/60;
		
			if(mins<60)	{
				secsText=mins+" " + watupro_i18n.minutes_and + " "+secondsLeft+" " + watupro_i18n.seconds;
			}
			else {
				var minsLeft=mins%60;
				var hours=(mins-minsLeft)/60;
								
				secsText=hours+watupro_i18n.hours+" "+minsLeft+" " +watupro_i18n.minutes_and+ " "
					+secondsLeft+" "+watupro_i18n.seconds;
			}			
		}

    document.getElementById('timerDiv').innerHTML = watupro_i18n.time_left + " " + secsText;
    WatuPRO.secs = WatuPRO.secs - 1;
    WatuPRO.timerRunning = true;
    WatuPRO.timerID = self.setTimeout("WatuPRO.StartTheTimer()", WatuPRO.delay);
  }
}
// end timer related functions
/**********************************************************************************/

jQuery(document).ready(WatuPRO.initWatu);
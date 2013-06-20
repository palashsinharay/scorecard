WatuPROPractice = {};

WatuPROPractice.submit = function()
{
	// please wait
	jQuery('#watuPracticeFeedback').show();
	jQuery('#watuPracticeFeedback').html("<p>Please wait for the feedback...</p>");	
	// hide this div
	jQuery('#questionDiv'+this.curID).hide();
	
	// submit current question and get answer
	data={"id": this.curID, "answer": this.getAnswer(this.curID), "action": "watupro_practice_submit"};
	var self=this;
	jQuery.post(WatuPRO.siteURL, data, function(msg){
		// get next id and assign to the global curID;
		var nextID=self.allIDs[0];
		var getNext=false;
		for(x in self.allIDs)
		{
			if(getNext) 
			{
				nextID=self.allIDs[x];
				break;
			}
			if(self.allIDs[x]==self.curID) getNext=true;		
		}
		
		self.curID=nextID;
		
		msg += "<p align='center'><input type='button' onclick=\"jQuery('#questionDiv"+self.curID+"').show();jQuery('#watuPracticeFeedback').hide();jQuery('#watuPROCheckButton').show();\" value='&gt;&gt;&gt;'></p>";		
		jQuery('#watuPracticeFeedback').html(msg);
		jQuery('#watuPROCheckButton').hide();
		
		// reset form
		// console.log(jQuery('#watuPROPracticeForm'+self.examID));
		jQuery('#watuPROPracticeForm'+self.examID)[0].reset();
		
	});
}

WatuPROPractice.getAnswer = function(questionID)
{
	var ansvalues=[];
	var ansgroup = '.answerof-'+questionID;   
	var answerType = jQuery('#answerType'+questionID).val();
	
	i=0;
   if(answerType == 'textarea') {
        // open end question            
      ansvalues[0]=jQuery('#textarea_q_'+questionID).val();
   }
   else {
      jQuery(ansgroup).each(function(){
		if( jQuery(this).is(':checked') || answerType=='gaps') {
				ansvalues[i] = this.value;
				i++;
 			}
 		});    
   }
   
   return ansvalues;   
}

WatuPRODep = {};

WatuPRODep.forceNumber = function(item)
{
	if(isNaN(item.value) || item.value=="") item.value = 0;
}

WatuPRODep.lockDetails = function(examID, adminURL)
{
	adminURL = adminURL || "";
	tb_show("Taking Details", adminURL + "admin-ajax.php?action=watupro_lock_details&exam_id="+examID, "admin-ajax.php");
}
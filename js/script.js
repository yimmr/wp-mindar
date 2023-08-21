(function($){
	jQuery(document).ready(function(){
		
	  $('.ar_button').click(function(){
      var ar_loading = '<div class="armodal"></div>'
	  	$("body").append(ar_loading);
	  	$("body").addClass("arloading");
		  console.log('loading');
	    var data = {
	      'action': 'pl_ar_current_option',
	      'security': pl_ar_ajax_params.pl_ar_nonce,
	      'selector':event.target.id,
		  'options': $(this).data(),
		  };
		  
		  jQuery.post(pl_ar_ajax_params.ajaxurl, data, function(response) {
		    if (response){
	        $(location).attr('href',response);   
		    }
		  }); 
	  });
	  
	});
})(jQuery);	

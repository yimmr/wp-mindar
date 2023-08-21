//script for file upload
(function($){

	$(document).ready( function(){

    //handles insert object button file tree
		$('body').on('click', '#pl_ar_insert_object', function(e) {
      $('#pl_wp_container').remove();	//remove container if exists
      var pl_ar_sibling_input = $(e.target).siblings('#pl_ar_object_path');

  		$("body").click(function(e) {
		    if (!(e.target.id == "pl_wp_container" || $(e.target).parents("#pl_wp_container").length) && e.target.id != "pl_ar_insert_object" && e.target.id != "pl_ar_insert_marker") {
		      $('#pl_wp_container').remove();
		    } 
		  });	
    	var pl_ar_div =  '<div id="pl_wp_container" style="position:absolute;top:'+e.pageY+'px;left:'+e.pageX+'px;"></div>';
    	$( pl_ar_div ).appendTo( "body" );//need to be sure that wpwrap exists

      getfilelist( $('#pl_wp_container') , 'file_manager/objects' );
    	$( '#pl_wp_container' ).on('click', 'LI A', function() { /* monitor the click event on links */
		    var entry = $(this).parent(); /* get the parent element of the link */
		    if( entry.hasClass('folder') ) { /* check if it has folder as class name */
		        if( entry.hasClass('collapsed') ) { /* check if it is collapsed */           
	            entry.find('UL').remove(); /* if there is any UL remove it */
	            getfilelist( entry, escape( $(this).attr('rel') )); /* initiate Ajax request */
	            entry.removeClass('collapsed').addClass('expanded'); /* mark it as expanded */
		        }
		        else { /* if it is expanded already */
	            entry.find('UL').slideUp({ duration: 500, easing: null }); /* collapse it */
	            entry.removeClass('expanded').addClass('collapsed'); /* mark it as collapsed */
		        }
		    } 
		    else { /* clicked on file */
	        $( '#selected_file' ).text( "File:  " + $(this).attr( 'rel' )); 
	        $( pl_ar_sibling_input).attr('placeholder',this.rel);
	        $('#pl_wp_container').remove();
		    }
				return false;
			});
	  });

    //handles insert marker button file tree
		$('body').on('click', '#pl_ar_insert_marker', function(e) {
      $('#pl_wp_container').remove();	//remove container if exists
      var pl_ar_sibling_input = $(e.target).siblings('#pl_ar_marker_path');

  		$("body").click(function(e) {
		    if (!(e.target.id == "pl_wp_container" || $(e.target).parents("#pl_wp_container").length) && e.target.id != "pl_ar_insert_marker" && e.target.id != "pl_ar_insert_object") {
		      $('#pl_wp_container').remove();
		    } 
		  });	
    	var pl_ar_div =  '<div id="pl_wp_container" style="position:absolute;top:'+e.pageY+'px;left:'+e.pageX+'px;"></div>';
    	$( pl_ar_div ).appendTo( "body" );//need to be sure that wpwrap exists

      getfilelist( $('#pl_wp_container') , 'file_manager' );
    	$( '#pl_wp_container' ).on('click', 'LI A', function() { /* monitor the click event on links */
		    var entry = $(this).parent(); /* get the parent element of the link */
		    if( entry.hasClass('folder') ) { /* check if it has folder as class name */
		        if( entry.hasClass('collapsed') ) { /* check if it is collapsed */           
	            entry.find('UL').remove(); /* if there is any UL remove it */
	            getfilelist( entry, escape( $(this).attr('rel') )); /* initiate Ajax request */
	            entry.removeClass('collapsed').addClass('expanded'); /* mark it as expanded */
		        }
		        else { /* if it is expanded already */
	            entry.find('UL').slideUp({ duration: 500, easing: null }); /* collapse it */
	            entry.removeClass('expanded').addClass('collapsed'); /* mark it as collapsed */
		        }
		    } 
		    else { /* clicked on file */
	        $( '#selected_file' ).text( "File:  " + $(this).attr( 'rel' )); 
	        $( pl_ar_sibling_input).attr('placeholder',this.rel);
	        $('#pl_wp_container').remove();
		    }
				return false;
			});
	  }); 

    function getfilelist( cont, root ) {
	    /* send an ajax request */
	    $.post( pl_ar_ajax_admin_params.Foldertree_url, { dir: root }, function( data ) {
	    	//alert(data);
        $( cont ).append( data ); /* append the data to the div */
        if( 'Sample' == root ) /* check for the first run */
            $( cont ).find('UL:hidden').show();
        else /* subsequent calls will slide the list with animation */
            $( cont ).find('UL:hidden').slideDown({ duration: 500, easing: null });         
	    });
    }

    //add object box with close button
		$( '#pl_ar_add_button' ).click(function(){
			if($('div.pl_ar_object_box').length>5){
				alert ('No more in this version ');
				return false;
			}
			var ar_temp_html= '<div class="pl_ar_object_box">'
			    ar_temp_html+=  '<div id="pl_wp_close" class="pl_wp_close_div"><p class="pl_wp_closex">x</p></div>'
		    	ar_temp_html+=  '<input type="text" class="pl_ar_object_path_class" id="pl_ar_object_path" placeholder="Object path"  disabled/>'
		      ar_temp_html+=  '<label class="pl_ar_button" id="pl_ar_insert_object" >Insert Object (gltf or image)</label>'
					ar_temp_html+=  '<input type="text" class="pl_ar_marker_path_class" id="pl_ar_marker_path" placeholder="Marker path"  disabled/>'
					ar_temp_html+=  '<label class="pl_ar_button" id="pl_ar_insert_marker" >Insert Marker (patt)</label>'
		      ar_temp_html+='</div>'
			$( ar_temp_html ).appendTo( "#pl_ar_main_box" );
			$('.pl_wp_closex').off('click');
			$('.pl_wp_closex').click(function(e){
				var pl_ar_confirm = confirm("Are you sure you want to remove!");
				if (pl_ar_confirm == true) {
				  $(this).parents('.pl_ar_object_box').remove();
				} else {
				  return false;
				} 	
	    });
		});
		

		//Add shortcode button
		//Gather all paths and create arrays
		$( '#pl_ar_add_shortcode_button' ).click(function(){
      var invalid = false;
			var pl_ar_objects_array = new Array();
			$('.pl_ar_object_path_class').each(function (i) {
				if($(this).attr("placeholder")=='Object path'){
					$(this).css('background-color','#F08080');
          $(this).siblings('#pl_ar_insert_object').click(function(event){
          	$(this).siblings('#pl_ar_object_path').css('background-color','white');
          })
          //rise invalid flag
          invalid = true;
          return true;
				}
				else{
					var _objarritem=$(this).attr("placeholder")
					if (_objarritem) pl_ar_objects_array.push(_objarritem);
				}
        
      });

      var pl_ar_markers_array = new Array();
			$('.pl_ar_marker_path_class').each(function (i) {
				if($(this).attr("placeholder")=='Marker path'){
					$(this).css('background-color','#F08080');
          $(this).siblings('#pl_ar_insert_marker').click(function(event){
          	$(this).siblings('#pl_ar_marker_path').css('background-color','white');
          })
          //rise invalid flag
          invalid = true;
          return true;
				}
				else{
          pl_ar_markers_array.push($(this).attr("placeholder"));
        }  
      });

      //if inputs empty alert
      if (invalid) {
      	alert('please fill all inputs');
		    return;
		  }

      var $dialog = jQuery('<div id="ar_plugin_dialog"></div>')
      .html(function() {
      var output=`<div id="main_dialog">
						        <label id="name_label">Name</label>
						          <div id="name_div_input">
						            <input id="ar_name_input" type="text"></input>
						          </div>
						        <br><label id="name_label">Button colors</label>  
						          <div id="color_options">
						            <input id='ar_back_color' type='color' value='#034f84'/>Background color<br>
						            <input id='ar_text_color' type='color' value='#FFFFFF'/>Text color
						          </div>
						      </div>`;

      jQuery('#back_color').change(function() {
        alert('change');
      });

      return output;
	    })
	    .dialog({
	        open: function(){
	          
	        },
	        autoOpen: true,
	        position: { my: "center", at: "center", of: window },
	        title: 'Button parameters',
	        //autoResize:true,
	        modal: true,
	        width: 400,
	        height: 300,
	        buttons: {
          'Ok': function(){
          	      if (pl_ar_button_name = ""){
          	      	alert('please fill button name');
          	      	return;
          	      }
          	      var pl_ar_button_name = jQuery(this).find("#ar_name_input").val();
          	      var b_background_color=jQuery(this).find("#ar_back_color").val();
          	      var b_text_color=jQuery(this).find("#ar_text_color").val();
				  var type = document.getElementById("pl_ar_type").value;
	 
          	      /* send an ajax request */
						      var data = {
							      'action': 'pl_ar_query',
							      'objects':  JSON.stringify(pl_ar_objects_array),
							      'markers': JSON.stringify(pl_ar_markers_array),
							      'button_name': pl_ar_button_name,
							      'button_back_color': b_background_color,
							      'button_text_color': b_text_color,
							      'security': pl_ar_ajax_admin_params.pl_ar_nonce,
								  'type': type
								  };

							    $.post( pl_ar_ajax_admin_params.ajaxurl, data, function( data ) {
							    	if (data.match("^Entry")){//case of data already exists
							    		alert(data);
							    	}
							    	else{
							    		//show shortcode
							    		$('.pl_ar_txt').contents().first()[0].textContent = 'Your shortcode is: [ar-plugin id="'+ JSON.parse(data.replace(/\\/g,''))+'"'+' type="'+type+'" name="'+pl_ar_button_name+'" color="'+b_background_color+'" text-color="'+b_text_color+'"]';
							    		//show the shortcode text box
						    			$('.pl_ar_shortcode_box').css('opacity','1.0');
							    	} 
	                });
	                //Close the dialog
	                jQuery( this ).dialog( "close" );
                }
          }     
      });
      
		});

    
    //handle copy to clipboard button
    $( '#pl_ar_copy_link' ).click(function(){
    	var temp = $("<input>");
		  $("body").append(temp);
		  temp.val($('#pl_ar_add_shortcode_txt').text().replace('Your shortcode is:','')).select();
		  document.execCommand("copy");
		  temp.remove();
    });

    //handle buttons from database page
    $( '.pl_ar_button_dat' ).click(function(event){
    	//////////////////////////////////////////////////////////
    	if(event.target.innerHTML=='Delete'){
        /* send an ajax request */
	      var data = {
	      	'action': 'pl_ar_ajax_database_delete',
		      'button_id': event.target.id,
		      'security': pl_ar_ajax_admin_params.pl_ar_nonce
			  };

		    $.post( pl_ar_ajax_admin_params.ajaxurl, data, function( data ) {
		    	location.reload();      
		    });
    	}
    	/////////////////////////////////////////////////////////
    	else if(event.target.innerHTML=='Copy shortcode'){
    		var temp = $("<input>");
			  $("body").append(temp);
			  temp.val($(event.target.parentElement).closest('td').prev('td').text()).select();
			  document.execCommand("copy");
			  temp.remove();
	    }
    });

	});

})(jQuery);


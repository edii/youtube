/* 
 * jQuery script loader
 */
jQuery(function() {
    /* ajax */
    if(jQuery('#form_channel_address').length) {
        var options = { 
                        beforeSubmit:  gerSubmit,  // pre-submit callback 
                        success:       setSubmit,  // post-submit callback 
                        type:          'POST',        // 'get' or 'post', override for form's 'method' attribute 
                        dataType:      'json',        // 'xml', 'script', or 'json' (expected server response type) 
                        clearForm:      true        // clear all form fields after successful submit 
                    }; 

        jQuery('#form_channel_address').ajaxForm(options);  //.form-actions input[type=submit]
        
    }
    /* end */
});

/* post submit */
function gerSubmit(formData, jqForm, options) { 
    for (var i=0; i < formData.length; i++) { 
            var fd = formData[i];
            if(!fd) showAlert('Error: ', ' System error!');
            var name = jQuery(fd).attr('name');
            
            if(name == 'url_address') {  
                var val = formData[i].value;
                if(!val) { 
                    showAlert('Error: ', ' Enter url address!'); 
                    return false;
                }
            }
            
            
    }
    
    return true; 
}
function setSubmit(responseText, statusText, xhr, $form) {
    var status_error = 'error try again!';
    
    if(statusText == 'success') {
        if(responseText) {
            if(responseText.result && !responseText.error) {  
                
                var html = "<ul>";
                    jQuery.each(responseText.result, function(key, video){
                        html += "<li>";
                            html += "<div class='item'>";
                                html += "<span class='title'>";
                                    html += "<a href='"+video.link+"'>"+video.title+"</a>";
                                html += "</span>";
                                html += "<p>"+video.description+"</p>";
                                html += "<p>";
                                    html += "<span class='thumbnail'>";
                                         html += video.embed;
                                    html += "</span>";
                                    html += "<span class='attr'>By:</span> "+video.author.name+" <br/>";
                                    html += "<span class='attr'>Duration:</span> "+video.duration+" min. <br/>";
                                    html += "<span class='attr'>Views:</span> "+video.views+" <br/>";
                                    html += "<span class='attr'>Rating:</span> "+video.rating;
                                html += "</p>";
                            html += "</div>";
                        html += "</li>";
                    });
                    html += "</ul>";
                
                if(jQuery('.result-listing').length)
                    jQuery('.result-listing').html( html );
                
                showAlert(' Status: ', 'OK!');
                return true;
            } else if(!responseText.result && responseText.error) {
                showAlert('Error: ', responseText.error);
                return false; 
            }
            else {
                showAlert('Error: ', status_error);
                return false; 
            }
        } else {
            showAlert('Error: ', status_error);
            return false;   
        }
    } else {
        showAlert('Error: ', status_error);
        return false; 
    }
    
}

// =============== #dialog
function showAlert(msgTitle, msg, heihth, width){
    jQuery( "#dialog" ).attr('title', msgTitle).html( msg );
    jQuery( "#dialog" ).dialog();
}

function getAjaxForm(url, params) {
    var res = jQuery.ajax({
                    url: url,
                    dataType: "json",
                    async: false,
                    type: "POST",
                    cache: true,
                    complete: function(){ 
                    },
                    data: {
                        params: params 
                    }
		}).responseText;
    res = eval('['+res+']');
    obj = res[0];
    return obj;	
    
    
}

/*
 * parce url
 **/
function parseUrlQuery() {
    var data = {};
    if(location.search) {
        var pair = (location.search.substr(1)).split('&');
        for(var i = 0; i < pair.length; i ++) {
            var param = pair[i].split('=');
            data[param[0]] = param[1];
        }
    }
    return data;
}

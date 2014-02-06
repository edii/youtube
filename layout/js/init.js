/* 
 * jQuery script loader
 */
jQuery(function() {
    /* ajax */
    /* end */
});

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

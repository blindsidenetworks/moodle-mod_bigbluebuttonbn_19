function YUI() {

    var loadUrl = bigbluebuttonbn.wwwroot + "/mod/bigbluebuttonbn/ping.php?meetingid=" + bigbluebuttonbn.meetingid;
    //var loadUrl = bigbluebuttonbn.wwwroot + "/mod/bigbluebuttonbn/ping.php?";

    var callback = {
        success : function(o) {
            eval('var response = '+ o.responseText);
            console.debug(response);
            //document.getElementById('mydiv').innerHTML = o.responseText;
        },
        failure : function(o) {
            console.debug(o.statusText);
        }
    }
    //YAHOO.util.Connect.setPollingInterval(1000);
    var transaction = YAHOO.util.Connect.asyncRequest('GET', loadUrl, callback, null);
    console.debug(transaction);
    return false;
}


//mod_bigbluebuttonbn = mod_bigbluebuttonbn || {};

mod_bigbluebuttonbn_ping = function() {

    if (bigbluebuttonbn.joining == 'true') {
        if (bigbluebuttonbn.ismoderator == 'true' || bigbluebuttonbn.waitformoderator == 'false') {
            mod_bigbluebuttonbn_joinURL();
        } else {
            
            ////////////////
            /*  */
            var dataSource = new YAHOO.util.DataSource(bigbluebuttonbn.wwwroot + "/mod/bigbluebuttonbn/ping.php?meetingid=" + bigbluebuttonbn.meetingid);
            dataSource.responseType = YAHOO.util.DataSource.TYPE_TEXT;
            dataSource.responseSchema = {status: "status"};
            dataSource.maxCacheEntries = 5;

            var callback = {
                success : function() {
                    console.debug('success');
                    console.debug(dataSource);


                    //console.debug(o);
                    //eval('var data = '+ o.responseText);
                    //console.debug(data.status);
                    //if (data.status == 'true') {
                    //    mod_bigbluebuttonbn_joinURL();
                    //}
                },
                failure : function() {
                    console.debug("Polling failure");
                }
            }
            dataSource.setInterval(5000, null, callback)
            

            
            /*
            var loadUrl = bigbluebuttonbn.wwwroot + "/mod/bigbluebuttonbn/ping.php?meetingid=" + bigbluebuttonbn.meetingid;

            var callback = {
                success : function(o) {
                    eval('var data = '+ o.responseText);
                    console.debug(data.status);
                    if (data.status == 'true') {
                        mod_bigbluebuttonbn_joinURL();
                    }
                },
                failure : function(o) {
                    console.debug(o.statusText);
                }
            }
            //YAHOO.util.Connect.setPollingInterval(1000);
            var transaction = YAHOO.util.Connect.asyncRequest('GET', loadUrl, callback, null);
            */
            //////////////////
            

        }
    }
    
    return false;

}

mod_bigbluebuttonbn_joinURL = function() {
    console.debug(bigbluebuttonbn.joinurl);
    //window.location = bigbluebuttonbn.joinurl;
};

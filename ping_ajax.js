// mod_bigbluebuttonbn = mod_bigbluebuttonbn || {};

mod_bigbluebuttonbn_ping = function() {

    if (bigbluebuttonbn.joining == 'true') {
        if (bigbluebuttonbn.ismoderator == 'true'
                || bigbluebuttonbn.waitformoderator == 'false') {
            mod_bigbluebuttonbn_joinURL();
        } else {

            ////////////////
            var dataSource = new YAHOO.util.XHRDataSource(
                    bigbluebuttonbn.wwwroot + "/mod/bigbluebuttonbn/ping.php?meetingid=" + bigbluebuttonbn.meetingid);
            dataSource.responseType = YAHOO.util.XHRDataSource.TYPE_JSARRAY;
            dataSource.responseSchema = {
                fields : [ 'status' ]
            };
            dataSource.maxCacheEntries = 1;

            var dsRequest = null;
            var dsCallback = {
                argument : null,
                failure : function(oRequest, oParsedResponse, oPayload) {
                    console.debug(oParsedResponse.statusText);
                },
                scope : window,
                success : function(oRequest, oParsedResponse, oPayload) {
                    /// Validation code should be here, implemented with doBeforeParseData to work around a problem pasing the data
                    //var results = oParsedResponse.results;
                    //console.debug(results);
                }
            };

            /// Validation code
            dataSource.doBeforeParseData = function(oRequest, oFullResponse, oCallback) {
                eval('var data = ' + oFullResponse);
                if (data[0] == true) {
                    mod_bigbluebuttonbn_joinURL();
                }
            };

            var txnId = dataSource.setInterval(5000, dsRequest, dsCallback);
            // dataSource.sendRequest(dsRequest, dsCallback);
            // ////////////////
        }
    }

    return false;

}

mod_bigbluebuttonbn_joinURL = function() {
    //console.debug(bigbluebuttonbn.joinurl);
    window.location = bigbluebuttonbn.joinurl;
};

KonnichiwaPay = {}
KonnichiwaPay.payWithMoolaMojo = function(id, url, redirectURL) {
	 
	redirectURL = redirectURL || '';
	
	data = {"id" : id,};
	jQuery.post(url, data, function(msg){
		if(msg == 'SUCCESS') {			
			if(redirectURL) window.location = redirectURL;
			else {
				window.location = window.location + "?paid=1";
				// window.location.reload(); // because of FireFox
			}
		}
		else alert(msg);
	});
}
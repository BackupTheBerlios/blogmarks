function confirmDelete( url ) {
		
		var agree = confirm ("Voulez vous vraiment supprimer ce blogmark ?");
		
		if ( agree )
		{
		url = url + '&from=popupjs';
		params = 'location=no,toolbar=no,scrollbars=yes,width=325,height=400,left=75,top=175,status=no';
			window.open( url ,'BlogMarks', params );
		}

		//return agree;

		return false;

		/* 

		var agree = false;

			if (a) return true;
		else return false;
		
		if (a) document.location=page+"?q=delete&id="+id;
		else i = 0 ;
		*/
}

function Delete( url ) {

	url = url + '&mini=1&from=popupjs';
	params = 'location=no,toolbar=no,scrollbars=yes,width=325,height=400,left=75,top=175,status=no';
		
	window.open( url ,'BlogMarks', params );

	return false;

	//var agree = confirm ("Voulez vous vraiment " + msg + " ?");

	//alert (agree);

	//if (agree == true) {
		
	//	document.mainform.submit();
		
	//}
			
	//return agree;

}

function Edit( url ) {

		url = url + '&mini=1&from=popupjs';
		params = 'location=no,toolbar=no,scrollbars=yes,width=325,height=400,left=75,top=175,status=no';
		
		window.open( url ,'BlogMarks', params );

		return false;

}
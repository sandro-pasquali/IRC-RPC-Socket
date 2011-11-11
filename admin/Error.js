
$Error = 
  {
	  alert: function(e,comment)
		/*
		 * General error handling function.  Pops an alert with information.
		 */
		  {
			  var err = '>> ' + e + '\n';
			  for(p in e)
				  {
					  err += '>> ' + p + ': ' + eval('e.'+p) + '\n';
					}
			  alert('Error >> ' + (comment || '') + '\n\n'+err);
			}
	}

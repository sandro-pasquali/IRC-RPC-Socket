var Validator = 
  {
    _errors: 0,
	
	_errorString: 'required',
	
	_regexes: 
	  {
		allDigits: /^\d+$/,
		allAlpha: /^[a-zA-Z]+$/,
		validEmail: /^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/,
        oneChar: /^[a-zA-Z0-9\@\#\$\.]+$/ // at least 1 char at start of string
	  },
	  
	incErrorCount: function() { this._errors++; },
	
	decErrorCount: function() { this._errors--; },
	
	clearErrorCount: function() { this._errors = 0; },
	
	errorCount: function() { return(this._errors); },
    
    makeFlag: function(el)
	  {
	    var emsg = (el.attributes.getNamedItem('errorString')) 
		           ? el.attributes.getNamedItem('errorString').value
				   : 'required';
				  
		var add = (el.attributes.getNamedItem('additionalInfo')) 
		          ? el.attributes.getNamedItem('additionalInfo').value
				  : '';
				  
		var err = document.createTextNode(emsg);
		
		var container = document.createElement('span');
		container.className = 'check';
		container.additionalInfo = add;
		container.appendChild(err);
		
		if(add) 
		  {
		    var addS = document.createElement('span');
			addS.className = 'additionalErrorInfoQ';
			addS.additionalInfo = add;
			
			addS.onclick = function()
			  {
                alert(this.additionalInfo);
			  }
			
			var addST = document.createTextNode('[?]');
			addS.appendChild(addST);
			container.appendChild(addS);
		  }
		
		return(container);
	  }, 	

	turnOnCheck: function(el,fID)
      {
        this.incErrorCount();
	    var nam = el.name;
        var w = document.getElementById(fID + 'check' + nam);	 
        if(w)
	      {
	        if(w.lastChild) { return; } // already flagged
	  	    w.appendChild(this.makeFlag(el));
	      }
	    else 
	      {
	        var chk = document.createElement('span');
		    chk.id = fID + 'check' + nam;
		    chk.className = 'check';
		    chk.appendChild(this.makeFlag(el));
		    var w = el.parentNode.appendChild(chk);
	      }
      },

    turnOffCheck: function(el,fID)
      {
        var nam = el.name;
        var w = document.getElementById(fID + 'check' + nam);
	
	    // may fail before check element is created, so check for element first
        if(w)
	      {
	        // lose text node containing flag, if any
		    if(w.lastChild)
		      {
	            w.removeChild(w.lastChild);
		      }
	      }
      },
	
	skippedField: function(ee)
	  {
		if(ee.attributes.getNamedItem('validate')) 
		  {
		    return(ee.attributes.getNamedItem('validate').value == 'false');
		  }
		return(false); 
	  },
	  
	getFieldRegex: function(ee)
	  {
		if(ee.attributes.getNamedItem('regex')) 
		  {
		    return(ee.attributes.getNamedItem('regex').value);
		  }
		return(false); 
	  },
	  
	validField: function(el)
	  {
	    var strV = el.value.toString();
		var fRegex = this.getFieldRegex(el);
		
		// is there a regex attribute set?
		if(fRegex)
		  {
		    // check if we've been sent a regex template
			try
			  {
		        var rex = eval('this._regexes.'+fRegex);
			  }
		    catch(e) 
			  {
			    var rex = false;
			  }
			  
			// if so, execute the regex template
			if(rex)
			  {
			    return((rex.exec(strV)) ? true : false);
			  }
			else 
			  {
				try // try what we've been sent as a regex
				  {
				    return((fRegex.exec(strV)) ? true : false);
				  }
				catch(e) // not a proper regex; try as a conditional
				  {
				    try
					  {
					    // replace %% with 'strV'
						fRegex = fRegex.replace(/%%/g,'strV');
					    return(eval(fRegex));
					  }
					catch(e) // no dice
					  {
				        return(false);
					  }
				  }
			  }
		  }
		else 
		  {
		    /* 
			 * if no regex is given, and since the field has been flagged
			 * for validation, assume a valid field would simply be one
			 * with ANY value entered, of at least one character
			 */
		    return((this._regexes.oneChar.exec(strV)) ? true : false);
		  }
	  },

	onChange: function(e)
	  {
	    // get the element ref
		var ns = (e);
		var ev = (ns) ? e : window.event;
		var el = (ns) ? ev.target : ev.srcElement;
		
	    Validator.validateForm(el);
	  },
	  
	findForm: function(el)
      {
        var tmp = el;
	    while(tmp.parentNode)
	      {
	        if(tmp.parentNode.action) 
		      {
		        return(tmp.parentNode)
		      }
		    tmp = tmp.parentNode;
	      }
	    return(false);
      },  
	  
	attemptSubmit: function(el)
	  {
	    this.validateForm(el);
	    if(this.errorCount() < 1)
	      {
		    this.findForm(el).submit(); 
		  }
	  },

    validateForm: function(el)
      {
        this.clearErrorCount();

        var F = this.findForm(el);
		var formName = F.id;
		
        for(x=0; x < F.elements.length; x++)
	      {
		    var E = F.elements[x];
		    var T = E.type.toLowerCase();
		    // ignore hidden fields, buttons, and 2nd address field
		    if((T != 'hidden') && (T != 'button') && (this.skippedField(E)==false))
		      {
			    if(E.onchange != this.onChange) 
				  {
				    E.onchange = this.onChange;
				  }
				
				/*
				 * special case for checkboxes, being that they are on or off; if this
				 * field is supposed to be validated, valid = checked, invalid = unchecked
				 */
		        if(T == 'checkbox')
				  {
                    if(E.checked) 
				      { 
					    //alert(E.attributes.getNamedItem('validate').value);
						
					    this.turnOffCheck(E,formName);
					    continue; 
					  }
					else 
					  { 
					    this.turnOnCheck(E,formName); 
						continue;
					  }
				  }
		        else
				  {
					if(this.validField(E))
					  {
						this.turnOffCheck(E,formName);
					  }
					else
					  {
						this.turnOnCheck(E,formName);
					  }
				  }
			  }
	      }
      },
	  
    CreditCard: 
      { 
		allDigits: /^\d+$/,
		
        validCard: function(cNum,cType)
          {
            return(this.validNumber(cNum) && this.validType(cNum,cType));
          },
		  
        validNumber: function(strNum) 
          {
            var nCheck = 0;
            var nDigit = 0;
            var bEven  = false;
   
            for(n = strNum.length - 1; n >= 0; n--) 
              {
                var cDigit = strNum.charAt (n);
                if(this.allDigits.exec(cDigit))
                  {
                    var nDigit = parseInt(cDigit, 10);
                    if (bEven)
                      {
                        if((nDigit *= 2) > 9)
						  {
                            nDigit -= 9;
						  }
                      }
                    nCheck += nDigit;
                    bEven = ! bEven;
                  }
                else if(cDigit != ' ' && cDigit != '.' && cDigit != '-')
                  {
                    return false;
                  }
              }
            return((nCheck % 10) == 0);
          },
  
        validType: function(strNum, type)
          {
            var nLen = 0;
            for(n = 0; n < strNum.length; n++)
              {
                if (this.allDigits.exec(strNum.substring (n,n+1)))
                ++nLen;
              }
   
            var first = strNum.substring(0,1);
            var second = strNum.substring(1,2);
            var ftwo = strNum.substring(0,2);
            var ffour = strNum.substring(0,4);
   
            switch(type.toLowerCase())
              {
	            case 'visa':
                  return ((first == '4') && (nLen == 13 || nLen == 16));
	            break;
	   
	            case 'amex':
                  return ((ftwo == '34' || ftwo == '37') && (nLen == 15));
	            break;
	   
	            case 'mastercard':
	              return ((ftwo == '51' || ftwo == '52' || ftwo == '53' || ftwo == '54' || ftwo == '55') 
		               && (nLen == 16));
	            break;
	   
	            case 'dinersclub':
	              return ((ftwo == '30' || ftwo == '36' || ftwo == '38') && (nLen == 14));
	            break;
	   
	            case 'carteblanche':
	              return ((ftwo == '30' || ftwo == '36' || ftwo == '38') && (nLen == 14));
	            break;
	   
	            case 'discover':
	              return ((ffour == '6011') && (nLen == 16));
	            break;
	   
	            case 'enroute':
	              return ((ffour == '2014' || ffour == '2149') && (nLen == 15));
	            break;
	   
	            case 'jcb':
	              return ((ffour == '3088' || ffour == '3096' || ffour == '3112'
		                || ffour == '3158' || ffour == '3337' || ffour == '3528') && (nLen == 16));
	            break;
	   
	            default:
	              return(false);
	            break;
              }
          }
      }  	   
  }//alert(Validator.CreditCard.validCard('5191230043035667','mastercard'));

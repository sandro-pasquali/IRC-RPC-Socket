
function XMLHTTP()
  {
		// mozilla
    if(window.XMLHttpRequest) 
		  {
			  this.httpHandle = new XMLHttpRequest();
				this.xmlHandle = new XMLHttpRequest();
				this.microsoft = false;
			}
		else
		  {
	      // create the Microsoft handle
        var prefixes = ["MSXML4","MSXML3","MSXML2","MSXML","Microsoft"];
        var http, xml;
        for (var i = 0; i < prefixes.length; i++) 
		      {
            try 
				      {
                http = new ActiveXObject(prefixes[i] + ".XmlHttp");
                xml = new ActiveXObject(prefixes[i] + ".XmlDom");
                this.httpHandle = http;
					      this.xmlHandle = xml;
					      this.microsoft = true;
							}
            catch(ex) {};
          }
      }

	  this.loadXML = function(file,callback)
		  {
	      XMLHTTP.prototype.lastConnectionRef = this;
        XMLHTTP.prototype.callbackFunction = callback || null;
				XMLHTTP.prototype.lastResult = null;
				
			  this.xmlHandle.onreadystatechange = function()
			    {
					  var ref = XMLHTTP.prototype.lastConnectionRef;
					  if(ref.xmlHandle.readyState == 4)
					    {
							  var result = (window.XMLHttpRequest) ? ref.xmlHandle.responseXML : ref.xmlHandle.documentElement;
                XMLHTTP.prototype.callbackFunction(result);
							} 
					}
					
				if(window.XMLHttpRequest)
				  { 
            this.xmlHandle.open("GET",file,true);
            this.xmlHandle.send(null);
					}
				else
				  {
  		      this.xmlHandle.async = false;
            this.xmlHandle.load(file);
					}
			}
			
	  this.loadHTTP = function(file,callback,method)
		  {
			  var method = method || 'GET';
	      XMLHTTP.prototype.lastConnectionRef = this;
        XMLHTTP.prototype.callbackFunction = callback || null;
				XMLHTTP.prototype.lastResult = null;
			  this.httpHandle.onreadystatechange = function()
			    {
					  var ref = XMLHTTP.prototype.lastConnectionRef;
					  if(ref.httpHandle.readyState == 4)
					    {
                XMLHTTP.prototype.callbackFunction(ref.httpHandle.responseText);
							} 
					}
				this.httpHandle.open(method,file,true);
        this.httpHandle.send(null);
			}
	}
	
// stores a reference to the last connection attempted
XMLHTTP.prototype.lastConnectionRef = null;
// the function that will received the response from connection
XMLHTTP.prototype.callbackFunction = null;



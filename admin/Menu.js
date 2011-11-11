
/*
 * requires XMLHTTP.js
 * requires $Error.js
 */
 
function Menu(spd) 
  {
	  // Animation of the menu runs on a cycle(speed = spd), with each show/hide instruction 
		// placed on a stack.  Every tick of this clock causes one instruction to be executed.
	  this.startClock(spd || 25);
	} 

Menu.prototype.startClock = function(spd)
  {
	  Menu.prototype.instructionStack = new Array();
	  Menu.prototype.timer = setInterval('Menu.prototype.cycle()',spd);
	}	
	
Menu.prototype.cycle = function()
  {
	  try
		  {
		    // fifo
	      var ob = Menu.prototype.instructionStack.shift();
		    var obs = ob.ob.style;

    		// toggle its state
        if(obs.visibility=='inherit')
	        {
            obs.visibility='hidden';
            obs.position='absolute';
          } 
		    else 
		      {
            obs.visibility='inherit';
            obs.position='static';
          }
			}
		catch(e) {;} 
	}
	
Menu.prototype.addInstruction = function(el)
  {
	  Menu.prototype.instructionStack[Menu.prototype.instructionStack.length] =
		  {
			  ob: el
			};
	}	

Menu.prototype.activate = function(el,state)
/* when passed an element reference, will recursively
 * seek children and open them.
 */ 
  {
	  if(el)
	   {
		   // Change open/closed icon. Will only fire on originally 
		   // clicked control item (which sends the state argument).
		   if(!state)
			   {
				   try
					   {
					   /*
						   el.childNodes.item(0).src 
							 = (el.state == 'open') ? this.getIcon('closed') : this.getIcon('open');
						*/
						   el.childNodes.item(0).src 
							 = (el.state == 'open') ? ('images/closed.gif') : ('images/open.gif');
						
							 el.state = (el.state == 'open') ? 'closed' : 'open';
						 }
					 catch(e) { $Error.alert(e); }
				 }
				 
	     tempEl=el.nextSibling;
       if(tempEl)
		     {
    	     if(tempEl.nodeType==1)
				     {
						   // add the animation of this element (open/close) to the stack
               this.addInstruction(tempEl);
        	   } 
						this.activate(tempEl,1);
    	    }
    	}
  }	
	
Menu.prototype.serialize = function(obj)
  {
	  try
		  {
			  var s = new XMLSerializer();
				var ser = s.serializeToString(obj);
			}
		catch(e)
			{
				var ser = obj.xml;
			}	
		return(ser);
	}

Menu.prototype.load = function(dataFile,container,waitTime)
/*
 * loads the menu's data from dataFile
 * NOTE: assumes dataFile is an XML file.
 * NOTE: container is a string representing an element id
 *
 * Since we cannot load another menu until previous loads are complete,
 * load() will wait until a previous load is finished.
 */
	{
	  var wt = waitTime || 0;
	  if(this.menuLoaded)
		  {
		    // Announce that the menu is loading
		    Menu.prototype.menuLoaded = false; 
		
	      Menu.prototype.container = document.getElementById(container) || document.body;
        // Since the menu will arrive unformatted, lets hide it for now
        Menu.prototype.hideMenu();
		
        this.connection = new XMLHTTP();
        this.connection.loadXML(dataFile,this.buildMenus);
			}
		else if(wt < Menu.prototype.timeout)
		  {
			  setTimeout('Menu.prototype.load("' + dataFile + '","' + container + '",' + (wt+25) + ')',25);
			}
		else // timeout
		  {
			  $Error.alert(new Object(),'The menu has not loaded properly and may behave strangely.');
			}		
  }
	
Menu.prototype.hideMenu = function()
  {
	  Menu.prototype.container.style.visibility = 'hidden';
	}
	
Menu.prototype.showMenu = function()
  {
	  Menu.prototype.container.style.visibility = 'visible';
	}

Menu.prototype.buildMenus = function(re)
/* the callback function which fires when this menu's XML
 * description has been loaded (via this.load).  passes the xml doc ref.
 */
  {		
	  // store the xml representation
		Menu.prototype.tree = re;
		
		/* The XML is constructed with html compatible tags, we need only to serialize
		 * it and attach it to the body.  
		 */
    Menu.prototype.container.innerHTML = Menu.prototype.serialize(Menu.prototype.tree);

		// Make sure the menu insertion was successful and can be referenced 
	  try
		  {
			  /* containerID is the menu's containing xml tag (the id of the root
				 * element of this menu's XML representation.  Icon set and
				 * style sheet properties may be stored here.
				 */
		    var doc = document.getElementById(Menu.prototype.containerID);
			}
		catch(e)
		  {
			  $Error.alert(e,'The menu has not loaded properly and may behave strangely, or not at all.');
				return;
			}
				
		// Announce that the menu has loaded properly
		Menu.prototype.menuLoaded = true; 
		
		// First, see if there is a stylesheet to apply 
	  try
		  {
		    // load the style sheet definition, if any
		    Menu.prototype.loadStyleSheet(doc.attributes.getNamedItem('styleSheet').value);
			}
		catch(e) { ; } // no default style sheet
		
		// Try for a default icon set
		try
		  {
		    // load the icon set definition, if any
		    Menu.prototype.loadIconSet(doc.attributes.getNamedItem('iconSet').value);
			}
		catch(e) { ; } // no default iconset for menu
		
		// show the menu
    Menu.prototype.showMenu();
  }	
	
Menu.prototype.loadStyleSheet = function(file)
  {
		try
		  {
			  var style = document.getElementById(this.styleID);
				style.href = file;
			}
		catch(e)
		  {
        // style object doesn't exist.  create it.
				var head = document.createElement("link");
        head.setAttribute("id",this.styleID);
        head.setAttribute("rel","stylesheet");
        head.setAttribute("type","text/css");
        head.setAttribute("href",file);
        document.getElementsByTagName("head").item(0).appendChild(head);
			}
	}	
	
Menu.prototype.loadIconSet = function(file,waitTime)
/*
 * Load an icon set.  Because the icons cannot be attached until the menu
 * structure exists, we need to continuously call this function until
 * the menu is fully loaded.  
 *
 */
  {
	  var wt = waitTime || 0;
	  if(this.menuLoaded)
		  {
			  // hold further menu actions until icon set is loaded
			  this.menuLoaded = false;
				
        this.connection = new XMLHTTP();
        this.connection.loadXML(file,this.parseIconSet);
			}
		else if(wt < Menu.prototype.timeout)
		  {
			  setTimeout('Menu.prototype.loadIconSet("' + file + '",' + (wt+25) + ')',25);
			}
		else // timeout
		  {
			  $Error.alert(new Object(),'The menu has not loaded properly and may behave strangely.');
			}
	}
	
Menu.prototype.getIcon = function(id)
  {
	  var ret = null;
	  for(i=0; i<this.iconSet.length; i++)
		  {
			  el = this.iconSet;
				if(el[i].id == id)
					{
						ret = el[i].location;
						ret += el[i].fileName;
						ret += '.' + el[i].extension;
						break;
					}
			}
		return(ret);
  }
	
Menu.prototype.parseIconSet = function(re)
/*
 * Callback from loadIconSet(). Take the icon set that has been loaded and 
 * create a local representation used by getIcon() to fetch icon definitions
 */
  {
	  var els = re.getElementsByTagName('icon');
		Menu.prototype.iconSet = new Array();
		for(q=0; q<els.length; q++)
			{
			  Menu.prototype.iconSet[Menu.prototype.iconSet.length] = 
				  {
					  id: els.item(q).attributes.getNamedItem('id').value,
						location: els.item(q).attributes.getNamedItem('location').value,
						fileName: els.item(q).attributes.getNamedItem('fileName').value,
						extension: els.item(q).attributes.getNamedItem('extension').value
					};
			}
    Menu.prototype.attachIcons();
		
		// ok now to continue building menus
		Menu.prototype.menuLoaded = true;
  }	

Menu.prototype.attachIcons = function()
  {
		var d = Menu.prototype.container.getElementsByTagName('div');
		var st,ic,control,icon;
		for(x=0; x<d.length; x++)
		  {
			  try
				  {
			      st = d.item(x).attributes.getNamedItem('state').value;
						control = '<img src="' + this.getIcon(st) + '" hspace=2 vspace=4>';
					}
				catch(e) { control = ''; }
				
			  try
				  {
				    ic = d.item(x).attributes.getNamedItem('icon').value;
				    icon = '<img src="' + this.getIcon(ic) + '" hspace=2>';
					}
				catch(e) { icon = ''; }
				
				d[x].innerHTML = control + icon + d[x].innerHTML;
      }
  }

// Flagged when the callback function for load() has executed.
// initialized to true so that the first load call is immediately accepted
Menu.prototype.menuLoaded = true;
// The id of the link anchor which contains this menu's style sheet
// This link is dynamically created whenever a stylesheet load is tried.
Menu.prototype.styleID = 'menuStyle';
// This will contain a reference to the menu tree upon successful menu load
Menu.prototype.container = null;
// Set to the id of the root element of this menu's XML representation
Menu.prototype.containerID = 'menuContainer';
// Set to the maximum number of milliseconds any queued load 
// requests will wait before failing
Menu.prototype.timeout = 20000;

	
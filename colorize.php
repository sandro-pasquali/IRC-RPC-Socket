<?php 

ShowSource("Socket.php",true);

/* The function...  */

function ShowSource ($filename="",$wrap=true) {

if (file_exists($filename)) {
   ob_start();
   show_source( $filename );

   $t = ob_get_contents();
   ob_end_clean();
   
   $source=explode("<br />",$t);
   $counter=1;
   $aa = 'seashell';
   $bb = 'mintCream';
   if ($wrap==false)
           $wrap=" nowrap='nowrap'";
             else $wrap="";
   print "Source File: $filename (".count($source)." lines)<br /><br />";
   print "<table cellspacing='2' cellpadding='0'>";
           foreach ($source as $line) {
		   
		   $cc = ($counter%2) ? $aa : $bb;
		   
           print "<tr><td bgcolor='#f0f0f0'>".sprintf("%05d",$counter++)."&nbsp;&nbsp;</td>";
           print "<td$wrap style=\"background-color:".$cc.";\">$line</td></tr>\n";
       }
   print "</table>";
}
else
   print "Sorry, $filename was not located on this server...<br />";
}
 
?>
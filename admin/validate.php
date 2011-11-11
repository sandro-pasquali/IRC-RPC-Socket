<?
// 000712, steven@haryan.to
// 010207, carinridge@hotmail.com (Added file validity check)
// 010725, carinridge@hotmail.com (Updated regex, & errors)  Thanx to Luis Gonzaga for the heads up...

/*

an example of usage:

<? require "/path/to/this/file"; ?>
My validated bookmarks:
<ul>
<li><? disp_link("http://slashdot.org", "Slashdot"); ?>
<li><? disp_link("http://www.zope.org", "Zope"); ?>
<li><? disp_link("http://www.python.org", "Python"); ?>
<li><? disp_link("http://www.perl.com", "Perl"); ?>
<li><? disp_link("http://www.php.net", "Zend"); ?>
<li><? disp_link("http://www.perl.com/CPAN-local/", ""); ?>
</ul>

  btw, yup, of course this code is under GPL.

*/

$debug = 0;
$timeout = 30; // give up if can't connect within 30 secs.
$check_freq = 5*60; // cache for 5 minutes.

session_register('statuses');
session_register('hostnames');

function flush_disp_link_cache() {
    global $statuses;
    global $hostnames;
    $statuses = array();
    $hostnames = array();
}

function disp_link($url, $text) {
    global $debug;
    global $timeout;
    global $statuses;
    global $hostnames;
    global $check_freq;

    $now = time();
    $e = error_reporting(); error_reporting($e & (255-E_WARNING));

    if (!preg_match('/^(http|https|ftp):\/\/((?:[a-zA-Z0-9_-]+\.?)+):?(\d*)/', $url, $m)) {

        // it's a relative link, or absolute link with unsupported protocol
        // so there's no need to check
        $sstatus = '';
        if ($debug) { $sstatus='DEBUG:UNCHECKED'; }

    } else {

        $proto=$m[1];
        $hostname=strtolower($m[2]);
        $port=$m[3];

        // is the host an IP address?
        if (preg_match('/^\d+\.\d+\.\d+\.\d+/', $hostname)) {

            $ip = $hostname;

        } else { // it's not, so we have to resolve it first

            // have we tried to resolve it not so long ago?
            $from_cache=0;
            if (isset($hostnames[$hostname])) {

                if ($debug) {
                    echo "DEBUG: last resolve = ", $hostnames[$hostname][1], "<br>";
                }

                if ($now - $hostnames[$hostname][1] <= $check_freq) {
                    $ip = $hostnames[$hostname];
                    $from_cache=1;
                }

            }

            if (!$from_cache) {
                // we haven't, so resolve it
                $ip = gethostbyname($hostname);

                // if the hostname was not resolvable, gethostbyname returns  
                // its argument unchanged
                if ($ip === $hostname) { $ip=''; }

                // cache this resolve
                $hostnames[$hostname]=array($ip,$now);
            }
             
        }

        if (!$ip) { // was the hostname unresolvable?

            $sstatus = 'HOST NOT FOUND';

        } else {

            // get universal port number defaults, if not specified
            if (!$port) {  
                if ($proto == 'http') { $port = 80; }
                elseif ($proto == 'https') { $port = 443; }
                elseif ($proto == 'ftp') { $port = 21; }
            }

            $key = "$ip:$port";

            // have we checked the site not so long ago?
            $from_cache=0;
            if (isset($statuses[$key])) {

                if ($debug) {
                    echo "DEBUG: last check = ",$statuses[$key][2], "<br>";
                }

                if ($now-$statuses[$key][2] <= $check_freq) {
                    $sstatus = $statuses[$key][0];
                    $from_cache=1;
                }

            }

            if (!$from_cache || ($from_cache && ($sstatus == "OK"))) {
                // we haven't, so check it
                // CR:  or we have and the host is ok, so check the file

                if ($debug) {
                    echo "DEBUG: checking: proto=$proto, hostname=$hostname, ",
                         "ip=$ip, port=$port...<br>";
                }
                $fp = fsockopen($hostname, $port, &$errno, &$errstr, $timeout);

                if ($debug) {
                    echo "DEBUG: connect result: fp=$fp, errno=$errno, errstr=$errstr<br>";  
                }

                if ($fp) {
                    $sstatus = "OK";
                    fputs( $fp, sprintf( "GET %s HTTP/1.0\n\n", $url ) );
                    for( $try = 1; $try <= 3; $try++ )
                    {
                        $fstatus = "CHECKING";
                        if( ($got = fgets( $fp, 256 )) == NULL )
                          break;
                        if( eregi( "HTTP/1.(.) (.*) (.*)", $got, $parts ) )
                        {
                            echo "<!-- Found on try $try -->";
                            if( $parts[2] == "200" )
                                $fstatus = "FOUND";
                            else if( $parts[2] == "300" )
                                $fstatus = "MOVED";
                            else if( $parts[2] == "403" )
                                $fstatus = "RESTRICTED";
                            else if( $parts[2] == "404" )
                                $fstatus = "NOT FOUND";
                            else
                                $fstatus = "ERR ".$parts[2]." - ".$parts[3];
                            break;
                        }
                        $fstatus = "Bad Comms";
                    }
                } else {
                    if (preg_match('/timed?[\- ]?out/i', $errstr)) {
                        $sstatus = "TIMEOUT";
                    } elseif (preg_match('/refused/i', $errstr)) {
                        $sstatus = "OFF";
                    } else {
                        $sstatus = "DOWN?";
                    }
                }

                // cache this check
                $statuses[$key] = array($sstatus, $fstatus, $now);
            }

        }

    }

    echo "<A HREF=\"$url\">", ($text ? $text : htmlentities($url)), "</A>";

    if ($sstatus) { echo " (S:$sstatus"; }
    /* If the server is up, tell how the file is doing... */
    if ($sstatus == "OK") { echo " F:$fstatus"; }
    else { echo ")"; }

    error_reporting($e);
}

?> 

 My validated bookmarks:
<ul>
<li><? disp_link("http://slashdot.org", "Slashdot"); ?>
<li><? disp_link("http://www.zope.org", "Zope"); ?>
<li><? disp_link("http://www.python.org", "Python"); ?>
<li><? disp_link("http://www.perl.com", "Perl"); ?>
<li><? disp_link("http://www.php.net", "Zend"); ?>
<li><? disp_link("http://www.perl.com/CPAN-local/", ""); ?>
</ul> 

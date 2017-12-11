<?php
/* Version/reload checker, informs other processes to halt */

require_once "inc/fileIO.php";
require_once "inc/dnstrace.php";

$currVersion = intval(basicRead(getcwd() . "/version"));
$doReload = intval(basicRead(getcwd() . "/status/reload"));

if($doReload != 0) {
	exit;
}

$getRemoteVer = dnstLoadVersion();

if($getRemoteVer[0]) {
	$reVersion = intval($getRemoteVer[1]);
	if($reVersion > $currVersion) {
		basicWrite(getcwd() . "/status/reload", "1");
		echo "Initiating upgrade, stopping worker gracefully";
		
		while(true) {
			$rdyEnqueue = intval(basicRead(getcwd() . "/status/enqueue"));
			$rdyDequeue = intval(basicRead(getcwd() . "/status/dequeue"));
			
			if($rdyEnqueue == 1 && $rdyDequeue == 1) {
				exec("nohup bash >> /tmp/dnsb-init.log 2>&1 &");
				echo "System restarting";
				exit;
			} else {
				sleep(15);
			}
		}
	}
}
?>
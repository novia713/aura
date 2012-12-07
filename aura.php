#!/usr/bin/php
<?php

function check_args(){
	global $argv, $argc;
	$url = null;
	
	if (!@$argv[1]) {
		  print "provide an URL as argument. Example: ruby aura.rb http://drupal.org \n";
			print "don't use https \n";
			die();
	}
	
	if (preg_match('/^https:/', @$argv[1], $res)) {
		 print "use http, don't use https \n";
		 die();
	}
	
	if (preg_match('/^http:/', @$argv[1])) {
		  $url = "http://" . $argv[1];
	}
	
	if (!$url){ 
		$url = (@$argv[1]) ? "http://" . @$argv[1] : null;
	}
	
	print "scanning $url ...";
	return $url;
}


$checked_url = check_args();

#first we try changelog.txt
try {
	file_get_contents($checked_url . "/CHANGELOG.txt");
	foreach ($http_response_header as $item_header){
		if (preg_match('/Content-Type/', $item_header)){
			echo $item_header;
		}
	}

} catch (Exception $e) {
    echo 'Excepción capturada: ',  $e->getMessage(), "\n";
}

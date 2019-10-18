# Cereal hacker 1 - Points: 450
Challenge Description: 
`Login as admin. https://2019shell1.picoctf.com/problem/32256/ or http://2019shell1.picoctf.com:32256`

First thoughts about this challenge was sql injection or cookie manipulation. When we go to given url it was redirecting to login page. So I have tried sqlmap but it wasn't SQLi. Url structure was suspicious so i tried lfi but it wasn't LFI too. About my other thought we need to find valid credentials or maybe a page disclosuring cookie name.

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
We found admin page and my thought was correct. Its really cookie manupilation challenge

    <button onclick="document.cookie='user_info=; expires=Thu, 01 Jan 1970 00:00:18 GMT; domain=; path=/;'">Go back to login</button>
After this discovery we need to figure out how its working, how we can leverage it? With given hint i tried all sql injection strings and sqlmap but it didn't work. Thanks to my teammate who tried to login as guest and it worked :)
Finally we got user_info cookie as guest user.

    TzoxMToicGVybWlzc2lvbnMiOjI6e3M6ODoidXNlcm5hbWUiO3M6NToiZ3Vlc3QiO3M6ODoicGFzc3dvcmQiO3M6NToiZ3Vlc3QiO30%253D
Its basic urlencoded base64 string. 

    O:11:"permissions":2:{s:8:"username";s:5:"guest";s:8:"password";s:5:"guest";}
Thanks to given hint we know how to leverage this object. Trying SQLi strings manually was really boring and I wrote a script for it. You can find it named as `cereal.php`. Simply its getting  **[PayloadsAllTheThings](https://github.com/swisskyrepo/PayloadsAllTheThings)**'s Auth Bypass wordlist and using that payload on password field then encoding back to cookie and try to bypass the login.

    Flag : picoCTF{2eb6a9439bfa7cb1fc489b237de59dbf}


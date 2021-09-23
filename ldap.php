<?php

// config
$ldapserver = '10.72.0.109';
$ldapuser      = 'cn=Directory Manager'; 
$ldappass     = 'Evncpc@2021#';
$ldaptree    = "dc=evncpc,dc=vn";

// connect
$ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");

if(isset($_POST['username'])){
    if($ldapconn) {
        $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));
        // verify binding
        if ($ldapbind) {
            echo "<h2>Step1: LDAP bind successful...</h2>";
            $result = ldap_search($ldapconn,$ldaptree, "(uid=".$_POST['username'].")") or die ("Error in search query: ".ldap_error($ldapconn));
            $data = ldap_get_entries($ldapconn, $result);
            echo '<h2>Step2: Search the user dn</h2>';
            if ($data["count"]==1) {
                $dnuser = "";
                echo "Dn:". $data[0]["dn"] ."<br />";
                $dnuser = $data[0]["dn"];
                echo "User: ". $data[0]["cn"][0] ."<br />";
                echo '<h2>Step3: Validate input user credentials with ldap and return the result</h2>';
                $userbind = ldap_bind($ldapconn,$dnuser,$_POST['password']);
                if ($userbind) {
                    echo("Login correct");
                } else {
                    echo "Login Failed: Please check your username or password <br>";
                    echo "Error: ".ldap_error($ldapconn)."<br>";
                }
            }
            else {
                echo "User not available <br>";
            }

        } else {
            echo "LDAP bind failed...";
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
  <title></title>
</head>
<body>
<form action="" method="post">
<input name="username">
<input type="password" name="password">
<input type="submit" value="Submit">
</form>
</body>
</html>

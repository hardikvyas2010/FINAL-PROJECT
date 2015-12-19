<?php

?>
<html lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <meta charset="UTF-8">
        <style>
            table {width:50%;margin:5px;border-collapse:collapse;}
            table, th, td {border: 1px solid black;}
            th, td {padding: 20px;}
            h5 {color:red;}
        </style>
    </head>
    <body>
        <h3>{Title}</h3>
        Welcome.... {Firstname} {Lastname}. Please use these credentials to <a href="{BaseURL}">login to the system</a> :
        <table border="0">
            <tr>
                <td>Login</td><td>{Login}</td>
            </tr>
            <tr>
                <?php if ($this->config->item('ldap_enabled') == FALSE) { ?>
                <td>Password</td><td>{Password}</td>
                <?php } else { ?>
                <td>Password</td><td><i>The password you use in order to open a session on your operating system (Windows, Linux, etc.).</i></td>
                <?php } ?>
            </tr>            
        </table>
       
        <hr>
      
    </body>
</html>

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
        Please use these credentials to <a href="{BaseURL}">login to the system</a> :
        <table border="0">
            <tr>
                <td>Login</td><td>{Login}</td>
            </tr>
            <tr>
                <td>Password</td><td>{Password}</td>
            </tr>            
        </table>
        Once connected, you can change your password, as explained <a href="http://www.leave-management-system.org/how-to-change-my-password.html" title="Link to documentation" target="_blank">here</a>.
        <hr>
        
    </body>
</html>

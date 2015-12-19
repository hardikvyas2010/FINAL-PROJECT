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
        Dear {Firstname} {Lastname}, <br />
        <br />
        The overtime you've requested has been accepted. Below, the details :
        <table border="0">
            <tr>
                <td>Date &nbsp;</td><td>{Date}</td>
            </tr>
            <tr>
                <td>Duration &nbsp;</td><td>{Duration}</td>
            </tr>
            <tr>
                <td>Reason &nbsp;</td><td>{Cause}</td>
            </tr>
        </table>
        <hr>
     
    </body>
</html>

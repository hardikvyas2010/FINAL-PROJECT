<?php

?>
<html lang="ru">
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
        {Firstname} {Lastname}, <br />
        <br />
        <p>Ваше заявление на сверхурочные было отклонено. Детали ниже:</p>
        <table border="0">
            <tr>
                <td>Дата &nbsp;</td><td>{Date}</td>
            </tr>
            <tr>
                <td>Продолжительность &nbsp;</td><td>{Duration}</td>
            </tr>
            <tr>
                <td>Причина &nbsp;</td><td>{Cause}</td>
            </tr>
        </table>
        <hr>
        <h5>*** Это сообщение создано автоматически, пожалуйста, не отвечайте на него ***</h5>
    </body>
</html>

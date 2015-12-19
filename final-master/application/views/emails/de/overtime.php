<?php

?>
<html lang="en">
    <body>
        <h3>{Title}</h3>
        {Firstname} {Lastname} beantragt Überstunden. Hier die Details dazu:
        <table border="0">
            <tr>
                <td>Datum &nbsp;</td><td>{Date}</td>
            </tr>
            <tr>
                <td>Dauer &nbsp;</td><td>{Duration}</td>
            </tr>
            <tr>
                <td>Begründung &nbsp;</td><td>{Cause}</td>
            </tr>
        </table>
        <a href="{UrlAccept}">Akzeptieren</a>
        <a href="{UrlReject}">Ablehnen</a>
    </body>
</html>

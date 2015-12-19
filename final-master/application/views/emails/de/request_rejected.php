<?php

?>
<html lang="en">
    <body>
        <h3>{Title}</h3>
        Dear {Firstname} {Lastname}, <br />
        <br />
        Der beantragte Urlaub wurde abgelehnt. Hierzu die Details :
        <table border="0">
            <tr>
                <td>Von &nbsp;</td><td>{StartDate}</td>
            </tr>
            <tr>
                <td>Bis &nbsp;</td><td>{EndDate}</td>
            </tr>
            <tr>
                <td>Art &nbsp;</td><td>{Type}</td>
            </tr>
            <tr>
                <td>Begründung &nbsp;</td><td>{Reason}</td>
            </tr>
        </table>
    </body>
</html>

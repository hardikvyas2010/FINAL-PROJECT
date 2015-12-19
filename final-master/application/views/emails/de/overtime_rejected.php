<?php

?>
<html lang="en">
    <body>
        <h3>{Title}</h3>
        Dear {Firstname} {Lastname}, <br />
        <br />
        Die beantragten Überstunden wurden abgelehnt, hier die Details:
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
    </body>
</html>

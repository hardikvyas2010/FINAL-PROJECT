<?php

?>
<html lang="es">
    <body>
        <h3>{Title}</h3>
        {Firstname} {Lastname} solicita horas extras. A continuación, los detalles:
        <table border="0">
            <tr>
                <td>Fecha &nbsp;</td><td>{Date}</td>
            </tr>
            <tr>
                <td>Duracion &nbsp;</td><td>{Duration}</td>
            </tr>
            <tr>
                <td>Motivo &nbsp;</td><td>{Cause}</td>
            </tr>
        </table>
        <a href="{UrlAccept}">Aceptar</a>
        <a href="{UrlReject}">Rechazar</a>
    </body>
</html>

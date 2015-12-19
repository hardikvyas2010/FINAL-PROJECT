<?php

?>
<html lang="es">
    <body>
        <h3>{Title}</h3>
        {Firstname} {Lastname} solicitud de una licencia. A continuación, el <a href="{BaseUrl}leaves/{LeaveId}">detalle</a> :
        <table border="0">
            <tr>
                <td>Desde &nbsp;</td><td>{StartDate}&nbsp;({StartDateType})</td>
            </tr>
            <tr>
                <td>Hasta &nbsp;</td><td>{EndDate}&nbsp;({EndDateType})</td>
            </tr>
            <tr>
                <td>Tipo &nbsp;</td><td>{Type}</td>
            </tr>
            <tr>
                <td>Duration &nbsp;</td><td>{Duration}</td>
            </tr>
            <tr>
                <td>Balance &nbsp;</td><td>{Balance}</td>
            </tr>
            <tr>
                <td>Motivo &nbsp;</td><td>{Reason}</td>
            </tr>
            <tr>
                <td><a href="{BaseUrl}requests/accept/{LeaveId}">Aceptar</a> &nbsp;</td><td><a href="{BaseUrl}requests/reject/{LeaveId}">Rechazar</a></td>
            </tr>
        </table>
        <br />
        Puede comprobar el <a href="{BaseUrl}hr/counters/collaborators/{UserId}">balance de permisos</a> antes de la solicitud del permiso.

    </body>
</html>

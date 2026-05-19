<?php
$db = new PDO('sqlite:C:/Users/Manu/Desktop/ProyectDBDLE/Desarrollo-de-aplicaciones-web/Proyecto/var/app.db');
$res = $db->query("SELECT * FROM user");
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

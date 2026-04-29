<?php
$db = new PDO('sqlite:var/app.db');
foreach ($db->query("SELECT name FROM sqlite_master WHERE type='table'") as $row) {
    echo $row['name'] . "\n";
}

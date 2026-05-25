<?php
$mysqli = new mysqli('localhost', 'root', '', 'ordering_system', 3306);
if ($mysqli->connect_error) {
    echo 'ERROR: ' . $mysqli->connect_error;
    exit(1);
}
$result = $mysqli->query('SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status ORDER BY cnt DESC');
if (!$result) {
    echo 'ERROR: ' . $mysqli->error;
    exit(1);
}
while ($row = $result->fetch_assoc()) {
    echo $row['status'] . ' | ' . $row['cnt'] . "\n";
}
$mysqli->close();

<?php
require_once "db.php";

$data = array();
$query = "SELECT * FROM events ORDER BY id";
$statement = $connect->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
foreach ($result as $row) {
    $data[] = array(
        'id' => $row["id"],
        'title' => $row["title"],
        'start' => $row["event_date"]
    );
}
echo json_encode($data);

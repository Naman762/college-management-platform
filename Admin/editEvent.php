<?php
require_once "db.php";

if (isset($_POST["event_id"])) {
    $query = "UPDATE events SET event_date =:event_date WHERE id =:event_id";
    $statement = $connect->prepare($query);
    $statement->execute(array(
        ':event_id' => $_POST['event_id'],
        ':event_date' => $_POST['start']
    ));
}

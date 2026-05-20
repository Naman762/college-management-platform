<?php
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $updatedTitle = $_POST['updatedTitle'];
    $updatedStartDate = $_POST['updatedStartDate'];

    // Update the event in the database
    $query = "UPDATE events SET title = :updatedTitle, event_date = :updatedStartDate WHERE id = :event_id";
    $statement = $connect->prepare($query);
    $statement->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $statement->bindParam(':updatedTitle', $updatedTitle, PDO::PARAM_STR);
    $statement->bindParam(':updatedStartDate', $updatedStartDate, PDO::PARAM_STR);

    if ($statement->execute()) {
        echo "Event updated successfully.";
    } else {
        echo "Failed to update event.";
    }
}

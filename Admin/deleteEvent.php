<?php
  
  require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Perform the necessary database query to delete the event
    // For example: DELETE FROM events WHERE id = $event_id

    // Replace the following line with your actual database query
    $query = "DELETE FROM events WHERE id = $event_id";
    $statement = $connect->prepare($query);
    if ($statement->execute()) {
        echo "deleted";
    } else {
        echo "Failed to delete event.";
    }
}

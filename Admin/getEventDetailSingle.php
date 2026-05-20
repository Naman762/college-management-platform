<?php
require_once "db.php";

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Fetch event details from the database
    $query = "SELECT * FROM events WHERE id = :event_id";
    $statement = $connect->prepare($query);
    $statement->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $statement->execute();
    $event = $statement->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        // Display event details and update form
        echo "<input type='hidden' id='event_id' value='{$event_id}'>";
        echo "Event Title: <input type='text' id='editTitle' value='{$event['title']}' class='form-control'><br>";
        echo "Start Date: <input type='date' id='editStartDate' value='{$event['event_date']}' class='form-control'><br>";
    } else {
        echo "Event not found.";
    }
}

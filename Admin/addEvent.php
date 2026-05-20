<?php
  
  require_once "db.php";

if (isset($_POST["title"])) {
    $query = "INSERT INTO events (title, event_date) VALUES (:title, :event_date)";
    $statement = $connect->prepare($query);
    $statement->execute(array(
        ':title' => $_POST['title'],
        ':event_date' => $_POST['start']
    ));
}

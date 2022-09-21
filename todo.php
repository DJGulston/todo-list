<?php

// Connect server to database.
require 'secrets.php';

$mysqli = new mysqli('localhost', DB_UID, DB_PWD, 'example_db');

if($mysqli === false) {
    die('Error: Could not connect. ' . $mysqli->connect_error);
}

echo 'Successfully connected! Host info: ' . $mysqli->host_info;

// Determines whether we must show completed items or not.
// If $show_completed is 0, we hide completed items.
// If $show_completed is 1, we show completed items and incompleted items.
$show_completed = 0;

// If the POST request variable show_completed has been set to 1, we set the $show_completed
// variable to 1 as well. Otherwise, $show_completed remains 0.
if(isset($_POST['show_completed'])) {

    if((int) $_POST['show_completed'] === 1) {
        $show_completed = 1;
    }

}

?>

<br><br>

<!-- Form to enter to-do item. -->
<form method="post" action="todo.php">
    <label for="title">Enter an item:</label>
    <input type="text" id="title" name="title" placeholder="To-do item...">
    <!-- We post the $show_completed variable as a hidden input. -->
    <input type="hidden" name="show_completed" value="<?php echo $show_completed?>">
    <button type="submit">Submit</button>
</form>

<?php


if($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(isset($_POST['id'])) {
        // If an id has been posted, we mark the to-do item with that specific id as done/completed.

        // The id that has been posted.
        $clicked_id = (int) $_POST['id'];

        // Updates the completed column value for that specific id to 1 to indicate
        // that it has been done/completed.
        $sql_update = "UPDATE todos SET completed = ? WHERE id = ?";

        $is_completed = 1;

        if($stmt = $mysqli->prepare($sql_update)) {
            // UPDATE statement is prepared and executed.
            $stmt->bind_param('ii', $is_completed, $clicked_id);
            $stmt->execute();
            echo 'To-do item marked as complete.';
        }
        else {
            // Error message printed if SQL statement cannot be prepared.
            echo "Error: Could not prepare SQL query: $sql_update. " . $mysqli->error;
        }
    }
    else if(isset($_POST['title'])) {
        // If a title (i.e. a to-do item) has been posted, we insert a to-do item into the todos table.

        // The title/to-do item that has been posted.
        $new_title = $_POST['title'];

        // Inserts the title/to-do item into the todos table.
        $sql_insert = "INSERT INTO todos (title) VALUES (?)";

        if($stmt = $mysqli->prepare($sql_insert)) {
            // INSERT statement is prepared and executed.
            $stmt->bind_param('s', $new_title);
            $stmt->execute();
            echo 'Record inserted successfully.';
        }
        else {
            // Error message printed if SQL statement cannot be prepared.
            echo "Error: Could not prepare SQL query: $sql_insert. " . $mysqli->error;
        }
    }

}

?>

<h2>To-do List Items</h2>

<table><tbody>
    <!-- First row of table containing table headings for the to-do items. -->
    <tr>
        <th>Item</th>
        <th>Added on</th>
        <th>Complete</th>
    </tr>

<?php

// Select statement that retrieves all records from the todos table.
$sql_select = "SELECT id, title, created, completed FROM todos";

// If $show_completed is 0 (i.e. we do not want to show completed to-do items), then we
// alter the select statement to retrieve all records where the completed column value is 0.
if($show_completed === 0) {
    $sql_select = "SELECT id, title, created, completed FROM todos WHERE completed = 0";
}

// Checks if the query has executed.
if($result = $mysqli->query($sql_select)) {

    // Checks if there are 1 or more records in retrieved from the query.
    if($result->num_rows > 0) {

        // Iterates through each row/record.
        while($row = $result->fetch_array()) {

            echo '<tr>';

                // If the completed column value is 0 (i.e. the to-do item is incomplete), we show
                // the title as normal. Otherwise, we show the title with a line crossed through it
                // to indicate that it has been completed.
                if((int) $row['completed'] === 0) {
                    echo '<td>' . $row['title'] . '</td>';
                }
                else {
                    echo '<td><del>' . $row['title'] . '</del></td>';
                }
                
                echo '<td>' . $row['created'] . '</td>';

                echo '<td>';
                    // Form containig the "Done" button used to mark a to-do item as complete/done.
                    echo '<form action="todo.php" method="post">';

                        // We post the id of the to-do item as a hidden input.
                        echo '<input type="hidden" name="id" value="' . $row['id'] . '">';

                        // We post the $show_completed variable as a hidden input.
                        echo '<input type="hidden" name="show_completed" value="' . $show_completed . '">';

                        // If the completed column value is 0 (i.e. incomplete), we display the button
                        // as normal. Otherwise, we disable the button.
                        if((int) $row['completed'] === 0) {
                            echo '<button type="submit">Done</button>';
                        }
                        else {
                            echo '<button type="submit" disabled>Done</button>';
                        }
                        
                    echo '</form>';
                echo '</td>';
            echo '</tr>';
            
        }

        // Close off table.
        echo '</tbody></table>';

        // Frees up the result from memory.
        $result->free();

    }
    else {
        // Close off table and print error message if 0 records were retrieved from the query.
        echo '</tbody></table>';
        echo 'No records matching your query were found.';
    }

}
else {
    // Close off table and print error message if the query could not execute.
    echo '</tbody></table>';
    echo "Error: Could not execute $sql. " . $mysqli->error;
}

// Show/Hide Completed form.
echo '<form action="todo.php" method="post">';

    if($show_completed === 0) {
        // If the $show_completed variable is 0 when the Show Completed button is clicked,
        // we post the show_completed variable with a new value of 1.
        echo '<input type="hidden" name="show_completed" value="1">'; // We post the $show_completed variable as a hidden input.
        echo '<button type="submit">Show Completed</button>';
    }
    else {
        // If the $show_completed variable is 1 when the Hide Completed button is clicked,
        // we post the show_completed variable with a new value of 0.
        echo '<input type="hidden" name="show_completed" value="0">'; // We post the $show_completed variable as a hidden input.
        echo '<button type="submit">Hide Completed</button>';
    }

echo '</form>';

/*
 * References:
 * 
 * Adding an integer as a parameter using the MySQLi function bind_param() in PHP:
 * - https://www.w3schools.com/php/php_mysql_prepared_statements.asp
 */
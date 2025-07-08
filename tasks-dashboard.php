<?php
// Start the session to manage user data across requests.
session_start();

// Check if the user is logged in. If not, display a message and terminate the script.
if (!isset($_SESSION['user_id'])) {
    echo "<p>User not logged in. Please <a href='login.php'>log in</a>.</p>";
    exit();
}

// Retrieve the user ID from the session.
$userId = $_SESSION['user_id'];

// Define the path to the JSON file where task data is stored.
$taskDbFile = __DIR__ . '/task_db.json';
// Initialize an empty array to store the current user's tasks.
$userTasks = [];

// Check if the task database file exists.
if (file_exists($taskDbFile)) {
    // Read the content of the file and decode it from JSON into a PHP array.
    $tasksData = json_decode(file_get_contents($taskDbFile), true);
    // If tasks exist for the current user ID, assign them to $userTasks.
    if (isset($tasksData[$userId])) {
        $userTasks = $tasksData[$userId];
    }
}

// Define a helper function to safely escape HTML special characters.
// This prevents Cross-Site Scripting (XSS) vulnerabilities.
function htmlspecialchars_utf8($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<div class="task-dashboard-container">
    <a class="tasks-display-dashboard">Your Tasks Today:</a>
    <div id="task-list">
        <?php
        // Check if there are any tasks for the user.
        if (!empty($userTasks)):
            // Loop through each task and display it.
            foreach ($userTasks as $index => $task): ?>
                <div class="task-placeholder">
                    <input type="checkbox" id="task-<?php echo $index; ?>"
                           data-task-index="<?php echo $index; ?>"
                           <?php echo $task['completed'] ? 'checked' : ''; /* Set checkbox as checked if task is completed */ ?>
                           onchange="toggleTaskCompletion(this)">
                    <label for="task-<?php echo $index; ?>"
                           class="<?php echo $task['completed'] ? 'completed' : ''; /* Add 'completed' class if task is completed */ ?>">
                        <?php echo htmlspecialchars_utf8($task['description']); /* Display sanitized task description */ ?>
                    </label>
                </div>
            <?php endforeach;
        else: ?>
            <p>No tasks yet.</p>
        <?php endif; ?>
    </div>

    <div class="add-task-container">
        <input type="text" id="newTask" placeholder="Add a new Task...">
        <button class="add-task-button" onclick="addTask()">Add Task</button>
    </div>
</div>

<script>
// Function to add a new task via AJAX.
function addTask() {
    const newTaskInput = document.getElementById('newTask');
    const taskText = newTaskInput.value.trim(); // Get task text and remove leading/trailing whitespace.

    if (taskText !== "") {
        // Send a POST request to 'add_task.php'.
        fetch('add_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded' // Specify content type for form data.
            },
            body: 'task=' + encodeURIComponent(taskText) // Encode task text for URL safety.
        })
        .then(response => {
            // Check if the response was successful. If not, parse error message and throw.
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                });
            }
            return response.json(); // Parse the JSON response.
        })
        .then(data => {
            if (data.success) {
                console.log('Task added successfully:', data.task);
                const taskList = document.getElementById('task-list');
                const newTaskDiv = document.createElement('div');
                newTaskDiv.classList.add('task-placeholder');
                // Generate a unique ID for the new task checkbox.
                const taskId = 'task-' + taskList.children.length;
                // Add the checkbox to the new task div.
                newTaskDiv.innerHTML = `<input type="checkbox" id="${taskId}" data-task-index="${taskList.children.length}" onchange="toggleTaskCompletion(this)">`;
                const newTaskLabel = document.createElement('label');
                newTaskLabel.setAttribute('for', taskId);
                newTaskLabel.textContent = taskText; // Set the task description as label text.
                newTaskDiv.appendChild(newTaskLabel);
                taskList.appendChild(newTaskDiv); // Add the new task to the list.
                newTaskInput.value = ""; // Clear the input field.
            } else if (data.error) {
                alert('Error adding task: ' + data.error); // Display server-side error.
            } else {
                console.error('Unexpected response from server:', data);
                alert('Unexpected error adding task.');
            }
        })
        .catch(error => {
            console.error('Error adding task:', error);
            alert('Error adding task: ' + error.message); // Display network/fetch error.
        });
    } else {
        alert("Please enter a task description."); // Alert if input is empty.
    }
}

// Function to toggle task completion status via AJAX.
function toggleTaskCompletion(checkbox) {
    const taskIndex = checkbox.dataset.taskIndex; // Get the index of the task.
    const label = checkbox.nextElementSibling; // Get the associated label element.
    const isChecked = checkbox.checked; // Get the current checked status of the checkbox.

    // Apply/remove 'completed' class for visual feedback (strike-through).
    if (isChecked) {
        label.classList.add('completed');
    } else {
        label.classList.remove('completed');
    }

    // Send a POST request to 'update_task_status.php'.
    fetch('update_task_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'index=' + encodeURIComponent(taskIndex) + '&completed=' + (isChecked ? 'yes' : 'no') // Send task index and completion status.
    })
    .then(response => response.json()) // Parse the JSON response.
    .then(data => {
        if (data.error) {
            alert('Error updating task status: ' + data.error);
            // Revert checkbox state and class if there's a server error.
            checkbox.checked = !isChecked;
            if (!isChecked) {
                label.classList.add('completed');
            } else {
                label.classList.remove('completed');
            }
        } else if (data.xp_updated) {
            console.log('Task status updated successfully, XP updated:', data.new_xp);
            // If XP was updated, trigger a level update check.
            fetch('update_level.php')
                .then(levelResponse => levelResponse.json())
                .then(levelData => {
                    if (levelData.level_updated) {
                        console.log('Level updated:', levelData.new_level);
                        // Update the level display in the header if available.
                        const levelPlaceholderHeader = document.querySelector('.header .level-placeholder');
                        if (levelPlaceholderHeader) {
                            levelPlaceholderHeader.textContent = 'Level ' + levelData.new_level;
                        }
                        // Reload the page to fully reflect level and XP changes in the UI.
                        window.location.reload();
                    } else if (levelData.error) {
                        console.error('Error updating level:', levelData.error);
                    }
                })
                .catch(error => {
                    console.error('Error fetching level update:', error);
                });
        } else {
            console.log('Task status updated successfully (no XP change):', data);
        }
    })
    .catch(error => {
        console.error('Network error updating task status:', error);
        alert('Network error updating task status.');
        // Revert checkbox state and class if there's a network error.
        checkbox.checked = !isChecked;
        if (!isChecked) {
            label.classList.add('completed');
        } else {
            label.classList.remove('completed');
        }
    });
}

// Client-side HTML special characters escaping function (for robust display).
function htmlspecialchars(str) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return str.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

<style>
    .completed {
        text-decoration: line-through; /* Adds a strike-through line */
        color: #888; /* Dims the color of completed tasks */
    }
</style>
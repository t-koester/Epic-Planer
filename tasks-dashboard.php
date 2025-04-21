<?php

session_start();


if (!isset($_SESSION['user_id'])) {
    echo "<p>User not logged in. Please <a href='login.php'>log in</a>.</p>";
    exit();
}

$userId = $_SESSION['user_id'];


$taskDbFile = __DIR__ . '/task_db.json';
$userTasks = [];


if (file_exists($taskDbFile)) {
    $tasksData = json_decode(file_get_contents($taskDbFile), true);
    if (isset($tasksData[$userId])) {
        $userTasks = $tasksData[$userId];
    }
}

function htmlspecialchars_utf8($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<div class="task-dashboard-container">
    <a class="tasks-display-dashboard">Your Tasks Today:</a>
    <div id="task-list">
        <?php if (!empty($userTasks)): ?>
            <?php foreach ($userTasks as $index => $task): ?>
                <div class="task-placeholder">
                    <input type="checkbox" id="task-<?php echo $index; ?>"
                           data-task-index="<?php echo $index; ?>"
                           <?php echo $task['completed'] ? 'checked' : ''; ?>
                           onchange="toggleTaskCompletion(this)">
                    <label for="task-<?php echo $index; ?>"
                           class="<?php echo $task['completed'] ? 'completed' : ''; ?>">
                        <?php echo htmlspecialchars_utf8($task['description']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tasks yet.</p>
        <?php endif; ?>
    </div>

    <div class="add-task-container">
        <input type="text" id="newTask" placeholder="Add a new Task...">
        <button class="add-task-button" onclick="addTask()">Add Task</button>
    </div>
</div>

<script>
function addTask() {
    const newTaskInput = document.getElementById('newTask');
    const taskText = newTaskInput.value.trim();

    if (taskText !== "") {
        fetch('add_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'task=' + encodeURIComponent(taskText)
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Task added successfully:', data.task);
                const taskList = document.getElementById('task-list');
                const newTaskDiv = document.createElement('div');
                newTaskDiv.classList.add('task-placeholder');
                const taskId = 'task-' + taskList.children.length;
                newTaskDiv.innerHTML = `<input type="checkbox" id="${taskId}" data-task-index="${taskList.children.length}" onchange="toggleTaskCompletion(this)">`;
                const newTaskLabel = document.createElement('label');
                newTaskLabel.setAttribute('for', taskId);
                newTaskLabel.textContent = taskText;
                newTaskDiv.appendChild(newTaskLabel);
                taskList.appendChild(newTaskDiv);
                newTaskInput.value = "";
            } else if (data.error) {
                alert('Error adding task: ' + data.error);
            } else {
                console.error('Unexpected response from server:', data);
                alert('Unexpected error adding task.');
            }
        })
        .catch(error => {
            console.error('Error adding task:', error);
            alert('Error adding task: ' + error.message);
        });
    } else {
        alert("Please enter a task description.");
    }
}

function toggleTaskCompletion(checkbox) {
    const taskIndex = checkbox.dataset.taskIndex;
    const label = checkbox.nextElementSibling;
    const isChecked = checkbox.checked;

    if (isChecked) {
        label.classList.add('completed');
    } else {
        label.classList.remove('completed');
    }

    fetch('update_task_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'index=' + encodeURIComponent(taskIndex) + '&completed=' + (isChecked ? 'yes' : 'no')
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error updating task status: ' + data.error);
            checkbox.checked = !isChecked;
            if (!isChecked) {
                label.classList.add('completed');
            } else {
                label.classList.remove('completed');
            }
        } else {
            console.log('Task status updated successfully:', taskIndex, isChecked);
        }
    })
    .catch(error => {
        console.error('Network error updating task status:', error);
        alert('Network error updating task status.');
        checkbox.checked = !isChecked;
        if (!isChecked) {
            label.classList.add('completed');
        } else {
            label.classList.remove('completed');
        }
    });
}

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
        text-decoration: line-through;
        color: #888;
    }
</style>
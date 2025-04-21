<?php
session_start();


$localUsersFile = __DIR__ . '/users_local.json';
$defaultLoginUserId = 'user1';
$defaultLoginPassword = 'user1';
$taskDbFile = __DIR__ . '/task_db.json';
$lastCleanupFile = __DIR__ . '/last_cleanup.txt'; 
$userTasks = [];



function cleanupCompletedTasks($userId, $taskDbFile, $lastCleanupFile) {
    if (file_exists($lastCleanupFile)) {
        $lastCleanupDate = trim(file_get_contents($lastCleanupFile));
    } else {
        $lastCleanupDate = null;
    }

    $currentDate = date('Y-m-d');

    if ($currentDate !== $lastCleanupDate) {
        $tasksData = [];
        if (file_exists($taskDbFile)) {
            $tasksData = json_decode(file_get_contents($taskDbFile), true);
        }

        if (isset($tasksData[$userId])) {
            $tasksData[$userId] = array_filter($tasksData[$userId], function ($task) {
                return !$task['completed'];
            });


            file_put_contents($taskDbFile, json_encode($tasksData, JSON_PRETTY_PRINT));


            file_put_contents($lastCleanupFile, $currentDate);
        }
    }
}



if (isset($usersData[$defaultLoginUserId]) && isset($usersData[$defaultLoginUserId]['password']) && $usersData[$defaultLoginUserId]['password'] === $defaultLoginPassword) {
    $_SESSION['user_id'] = $defaultLoginUserId;
    $_SESSION['is_logged_in'] = true;
    if (isset($usersData[$defaultLoginUserId]['username'])) {
        $_SESSION['username'] = $usersData[$defaultLoginUserId]['username'];
    } else {
        $_SESSION['username'] = $defaultLoginUserId;
    }
    if (isset($usersData[$defaultLoginUserId]['level'])) {
        $_SESSION['level'] = $usersData[$defaultLoginUserId]['level'];
    }


    cleanupCompletedTasks($_SESSION['user_id'], $taskDbFile, $lastCleanupFile);

} else {
    unset($_SESSION['user_id']);
    unset($_SESSION['is_logged_in']);
    unset($_SESSION['username']);
    unset($_SESSION['level']);
}

if (isset($_SESSION['user_id']) && file_exists($taskDbFile)) {
    $tasksData = json_decode(file_get_contents($taskDbFile), true);
    if (isset($tasksData[$_SESSION['user_id']])) {
        $userTasks = $tasksData[$_SESSION['user_id']];
    }
}
?>

<div class="task-dashboard-container">
    <a class="tasks-display-dashboard">Your Tasks today are:</a>
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
                        <?php echo htmlspecialchars($task['description']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tasks yet.</p>
        <?php endif; ?>
    </div>

    <div class="add-task-container">
        <input type="text" id="newTask" placeholder="Enter new task...">
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
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Aufgabe erfolgreich hinzugefügt:', taskText);
                const taskList = document.getElementById('task-list');
                const newTaskDiv = document.createElement('div');
                newTaskDiv.classList.add('task-placeholder');
                const taskId = 'task-' + taskList.children.length;
                newTaskDiv.innerHTML = `
                    <input type="checkbox" id="${taskId}" data-task-index="${taskList.children.length}" onchange="toggleTaskCompletion(this)">
                    <label for="${taskId}">${htmlspecialchars(taskText)}</label>
                `;
                taskList.appendChild(newTaskDiv);
                newTaskInput.value = "";
            } else if (data.error) {
                alert('Fehler beim Hinzufügen der Aufgabe: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Netzwerkfehler beim Hinzufügen der Aufgabe:', error);
            alert('Netzwerkfehler beim Hinzufügen der Aufgabe.');
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
            alert('Fehler beim Aktualisieren des Aufgabenstatus: ' + data.error);
            checkbox.checked = !isChecked;
            if (!isChecked) {
                label.classList.add('completed');
            } else {
                label.classList.remove('completed');
            }
        } else {
            console.log('Aufgabenstatus erfolgreich aktualisiert:', taskIndex, isChecked);
        }
    })
    .catch(error => {
        console.error('Netzwerkfehler beim Aktualisieren des Aufgabenstatus:', error);
        alert('Netzwerkfehler beim Aktualisieren des Aufgabenstatus.');
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
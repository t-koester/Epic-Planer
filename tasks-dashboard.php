<?php
// tasks-dashboard.php

// Starte die Session (sollte bereits in index.php erfolgt sein, aber zur Sicherheit)
session_start();

// Überprüfe, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    echo "<p>Benutzer nicht eingeloggt. Bitte <a href='login.php'>einloggen</a>.</p>";
    exit();
}

// Benutzer-ID aus der Session holen
$userId = $_SESSION['user_id'];

// Pfad zur Aufgaben-Datenbank-Datei
$taskDbFile = __DIR__ . '/task_db.json';
$userTasks = [];

// Lese die vorhandenen Aufgaben aus der Datenbank
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
    <a class="tasks-display-dashboard">Deine heutigen Aufgaben:</a>
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
            <p>Noch keine Aufgaben.</p>
        <?php endif; ?>
    </div>

    <div class="add-task-container">
        <input type="text" id="newTask" placeholder="Neue Aufgabe eingeben...">
        <button class="add-task-button" onclick="addTask()">Aufgabe hinzufügen</button>
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
                console.log('Aufgabe erfolgreich hinzugefügt:', data.task);
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
                alert('Fehler beim Hinzufügen der Aufgabe: ' + data.error);
            } else {
                console.error('Unerwartete Antwort vom Server:', data);
                alert('Unerwarteter Fehler beim Hinzufügen der Aufgabe.');
            }
        })
        .catch(error => {
            console.error('Fehler beim Hinzufügen der Aufgabe:', error);
            alert('Fehler beim Hinzufügen der Aufgabe: ' + error.message);
        });
    } else {
        alert("Bitte gib eine Aufgabenbeschreibung ein.");
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
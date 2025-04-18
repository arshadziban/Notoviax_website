// Store lists data
let lists = JSON.parse(localStorage.getItem('todoLists')) || [];
let currentListId = null;
let currentTaskId = null;

// Generate unique IDs
function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Save lists to localStorage
function saveLists() {
    localStorage.setItem('todoLists', JSON.stringify(lists));
}

// Create a new list card
function createListCard(list) {
    const listCard = document.createElement('div');
    listCard.className = 'glassmorphism p-4 relative flex flex-col h-96'; // Added height and flex column
    listCard.id = `list-${list.id}`;
    
    // Create list header with title and actions
    const listHeader = document.createElement('div');
    listHeader.className = 'flex justify-between items-center mb-4 flex-shrink-0';
    
    const listTitle = document.createElement('h2');
    listTitle.className = 'text-xl font-bold truncate max-w-[80%]';
    listTitle.textContent = list.title;
    
    const actionsDiv = document.createElement('div');
    actionsDiv.className = 'flex space-x-2';
    
    // Edit button
    const editBtn = document.createElement('button');
    editBtn.className = 'text-gray-400 hover:text-white';
    editBtn.innerHTML = '<i class="fas fa-edit"></i>';
    editBtn.onclick = () => openEditTitleModal(list.id);
    
    // Delete button
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'text-gray-400 hover:text-red-500';
    deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
    deleteBtn.onclick = () => openDeleteConfirmModal('list', list.id);
    
    actionsDiv.appendChild(editBtn);
    actionsDiv.appendChild(deleteBtn);
    
    listHeader.appendChild(listTitle);
    listHeader.appendChild(actionsDiv);
    
    // Create tasks container with scrolling
    const tasksContainer = document.createElement('div');
    tasksContainer.className = 'space-y-2 mb-4 overflow-y-auto flex-grow custom-scrollbar';
    tasksContainer.id = `tasks-${list.id}`;
    tasksContainer.style.maxHeight = 'calc(100% - 80px)'; // Leave space for header and add button
    
    // Render tasks
    list.tasks.forEach(task => {
        const taskElement = createTaskElement(task, list.id);
        tasksContainer.appendChild(taskElement);
    });
    
    // Add task button
    const addTaskBtn = document.createElement('button');
    addTaskBtn.className = 'w-full py-2 bg-gray-800 bg-opacity-50 rounded flex items-center justify-center hover:bg-opacity-70 transition-colors flex-shrink-0';
    addTaskBtn.innerHTML = '<i class="fas fa-plus mr-2"></i> Add Task';
    addTaskBtn.onclick = () => openAddTaskModal(list.id);
    
    // Assemble list card
    listCard.appendChild(listHeader);
    listCard.appendChild(tasksContainer);
    listCard.appendChild(addTaskBtn);
    
    return listCard;
}

// Create a task element
function createTaskElement(task, listId) {
    const taskElement = document.createElement('div');
    taskElement.className = 'flex items-center bg-gray-800 bg-opacity-50 p-2 rounded';
    taskElement.id = `task-${task.id}`;
    
    // Checkbox
    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.className = 'mr-2 flex-shrink-0';
    checkbox.checked = task.completed;
    checkbox.onchange = () => toggleTaskCompletion(listId, task.id);
    
    // Task text with controlled height
    const taskText = document.createElement('span');
    taskText.className = task.completed ? 
        'line-through text-gray-500 flex-grow task-text' : 
        'flex-grow task-text';
    taskText.textContent = task.text;
    taskText.title = task.text; // Add tooltip for full text on hover
    
    // Actions container
    const actionsDiv = document.createElement('div');
    actionsDiv.className = 'flex space-x-2 flex-shrink-0';
    
    // Edit button
    const editBtn = document.createElement('button');
    editBtn.className = 'text-gray-400 hover:text-white';
    editBtn.innerHTML = '<i class="fas fa-edit"></i>';
    editBtn.onclick = () => openEditTaskModal(listId, task.id);
    
    // Delete button
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'text-gray-400 hover:text-red-500';
    deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
    deleteBtn.onclick = () => openDeleteConfirmModal('task', listId, task.id);
    
    actionsDiv.appendChild(editBtn);
    actionsDiv.appendChild(deleteBtn);
    
    taskElement.appendChild(checkbox);
    taskElement.appendChild(taskText);
    taskElement.appendChild(actionsDiv);
    
    return taskElement;
}

// Render all lists
function renderLists() {
    const listsContainer = document.querySelector('.grid');
    listsContainer.innerHTML = '';
    
    lists.forEach(list => {
        const listCard = createListCard(list);
        listsContainer.appendChild(listCard);
    });
}

// Add a new list
function addList(title) {
    const newList = {
        id: generateId(),
        title: title,
        tasks: []
    };
    
    lists.push(newList);
    saveLists();
    renderLists();
}

// Delete a list
function deleteList(listId) {
    lists = lists.filter(list => list.id !== listId);
    saveLists();
    renderLists();
}

// Edit list title
function editListTitle(listId, newTitle) {
    const list = lists.find(list => list.id === listId);
    if (list) {
        list.title = newTitle;
        saveLists();
        renderLists();
    }
}

// Add a task to a list
function addTask(listId, taskText) {
    const list = lists.find(list => list.id === listId);
    if (list) {
        const newTask = {
            id: generateId(),
            text: taskText,
            completed: false
        };
        
        list.tasks.push(newTask);
        saveLists();
        renderLists();
    }
}

// Delete a task
function deleteTask(listId, taskId) {
    const list = lists.find(list => list.id === listId);
    if (list) {
        list.tasks = list.tasks.filter(task => task.id !== taskId);
        saveLists();
        renderLists();
    }
}

// Edit a task
function editTask(listId, taskId, newText) {
    const list = lists.find(list => list.id === listId);
    if (list) {
        const task = list.tasks.find(task => task.id === taskId);
        if (task) {
            task.text = newText;
            saveLists();
            renderLists();
        }
    }
}

// Toggle task completion
function toggleTaskCompletion(listId, taskId) {
    const list = lists.find(list => list.id === listId);
    if (list) {
        const task = list.tasks.find(task => task.id === taskId);
        if (task) {
            task.completed = !task.completed;
            saveLists();
            renderLists();
        }
    }
}

// Modal functions
function openAddTaskModal(listId) {
    currentListId = listId;
    document.getElementById('taskInput').value = '';
    document.getElementById('addTaskModal').classList.remove('hidden');
}

function closeAddTaskModal() {
    document.getElementById('addTaskModal').classList.add('hidden');
    currentListId = null;
}

function openAddListModal() {
    document.getElementById('listTitleInput').value = '';
    document.getElementById('addListModal').classList.remove('hidden');
}

function closeAddListModal() {
    document.getElementById('addListModal').classList.add('hidden');
}

function openEditTitleModal(listId) {
    currentListId = listId;
    const list = lists.find(list => list.id === listId);
    if (list) {
        document.getElementById('editTitleInput').value = list.title;
        document.getElementById('editTitleModal').classList.remove('hidden');
    }
}

function closeEditTitleModal() {
    document.getElementById('editTitleModal').classList.add('hidden');
    currentListId = null;
}

function openEditTaskModal(listId, taskId) {
    currentListId = listId;
    currentTaskId = taskId;
    
    const list = lists.find(list => list.id === listId);
    if (list) {
        const task = list.tasks.find(task => task.id === taskId);
        if (task) {
            document.getElementById('editTaskInput').value = task.text;
            document.getElementById('editTaskModal').classList.remove('hidden');
        }
    }
}

function closeEditTaskModal() {
    document.getElementById('editTaskModal').classList.add('hidden');
    currentListId = null;
    currentTaskId = null;
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Render initial lists
    renderLists();
    
    // Add List button
    document.getElementById('addListBtn').addEventListener('click', openAddListModal);
    
    // Save List button
    document.getElementById('saveListBtn').addEventListener('click', () => {
        const title = document.getElementById('listTitleInput').value.trim();
        if (title) {
            addList(title);
            closeAddListModal();
        }
    });
    
    // Cancel List button
    document.getElementById('cancelListBtn').addEventListener('click', closeAddListModal);
    
    // Save Task button
    document.getElementById('saveTaskBtn').addEventListener('click', () => {
        const taskText = document.getElementById('taskInput').value.trim();
        if (taskText && currentListId) {
            addTask(currentListId, taskText);
            closeAddTaskModal();
        }
    });
    
    // Cancel Task button
    document.getElementById('cancelTaskBtn').addEventListener('click', closeAddTaskModal);
    
    // Save Edit Title button
    document.getElementById('saveEditBtn').addEventListener('click', () => {
        const newTitle = document.getElementById('editTitleInput').value.trim();
        if (newTitle && currentListId) {
            editListTitle(currentListId, newTitle);
            closeEditTitleModal();
        }
    });
    
    // Cancel Edit Title button
    document.getElementById('cancelEditBtn').addEventListener('click', closeEditTitleModal);
    
    // Save Edit Task button
    document.getElementById('saveTaskEditBtn').addEventListener('click', () => {
        const newText = document.getElementById('editTaskInput').value.trim();
        if (newText && currentListId && currentTaskId) {
            editTask(currentListId, currentTaskId, newText);
            closeEditTaskModal();
        }
    });
    
    // Cancel Edit Task button
    document.getElementById('cancelTaskEditBtn').addEventListener('click', closeEditTaskModal);
});

// Variables to track what's being deleted
let deleteType = null; // 'list' or 'task'
let deleteListId = null;
let deleteTaskId = null;

// Open delete confirmation modal
function openDeleteConfirmModal(type, listId, taskId = null) {
    deleteType = type;
    deleteListId = listId;
    deleteTaskId = taskId;
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}

// Close delete confirmation modal
function closeDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    deleteType = null;
    deleteListId = null;
    deleteTaskId = null;
}

// Perform the actual deletion
function performDelete() {
    if (deleteType === 'list') {
        // Delete list
        lists = lists.filter(list => list.id !== deleteListId);
    } else if (deleteType === 'task') {
        // Delete task
        const list = lists.find(list => list.id === deleteListId);
        if (list) {
            list.tasks = list.tasks.filter(task => task.id !== deleteTaskId);
        }
    }
    
    saveLists();
    renderLists();
    closeDeleteConfirmModal();
}

// Add event listeners for the delete confirmation modal
document.addEventListener('DOMContentLoaded', () => {
    // Cancel Delete button
    document.getElementById('cancelDeleteBtn').addEventListener('click', closeDeleteConfirmModal);
    
    // Confirm Delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', performDelete);
});

// User Profile Dropdown functionality
const userProfileBtn = document.getElementById('userProfileBtn');
const userDropdown = document.getElementById('userDropdown');
const logoutBtn = document.getElementById('logoutBtn');

// Toggle dropdown when clicking profile button
userProfileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    userDropdown.classList.toggle('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!userProfileBtn.contains(e.target)) {
        userDropdown.classList.add('hidden');
    }
});

// Handle logout
logoutBtn.addEventListener('click', () => {
    // Add your logout logic here
    alert('Logged out successfully!');
    // You can redirect to login page or perform other logout actions
});

// Add event listener for logout button
document.getElementById('logoutBtn').addEventListener('click', function() {
    // Send request to logout endpoint
    fetch('auth/logout.php')
        .then(response => {
            if (response.ok) {
                // Redirect to login page
                window.location.href = 'login.html';
            }
        })
        .catch(error => {
            console.error('Logout failed:', error);
        });
});

// Todo API Client
const API_BASE = 'todos';
let todos = [];

// Utility: Log API responses
function logAPI(method, url, data, response) {
    const log = document.getElementById('apiLog');
    const timestamp = new Date().toLocaleTimeString();
    const entry = `[${timestamp}] ${method} ${url}\n${JSON.stringify(response, null, 2)}\n\n`;
    log.textContent = entry + log.textContent;
}

// Utility: API Request
async function apiRequest(method, endpoint, body = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    // Add method override header for PUT/DELETE (some servers need this)
    if (method === 'PUT' || method === 'DELETE') {
        options.headers['X-HTTP-Method-Override'] = method;
    }
    
    if (body) {
        options.body = JSON.stringify(body);
    }
    
    try {
        const url = API_BASE + endpoint;
        const response = await fetch(url, options);
        const data = await response.json();
        logAPI(method, endpoint, body, data);
        return data;
    } catch (error) {
        console.error('API Error:', error);
        logAPI(method, endpoint, body, { error: error.message });
        return null;
    }
}

// Load todos
async function loadTodos() {
    const response = await apiRequest('GET', '');
    if (response && response.success) {
        todos = response.data;
        renderTodos();
    }
}

// Render todos
function renderTodos() {
    const container = document.getElementById('todoList');
    
    if (todos.length === 0) {
        container.innerHTML = '<p class="text-muted">No todos yet. Create one above!</p>';
        return;
    }
    
    container.innerHTML = todos.map(todo => `
        <div class="todo-item ${todo.completed ? 'completed' : ''}">
            <div class="todo-content">
                <div class="todo-title">${escapeHtml(todo.title)}</div>
                ${todo.description ? `<div class="text-muted text-sm">${escapeHtml(todo.description)}</div>` : ''}
                <div class="todo-meta">
                    <span class="priority-badge priority-${todo.priority}">${todo.priority}</span>
                    ${todo.due_date ? `<span class="text-muted">Due: ${todo.due_date}</span>` : ''}
                </div>
            </div>
            <div class="todo-actions">
                <button class="btn btn-sm" onclick="toggleTodo('${todo.id}', ${!todo.completed})">
                    ${todo.completed ? 'Undo' : 'Complete'}
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteTodo('${todo.id}')">
                    Delete
                </button>
            </div>
        </div>
    `).join('');
}

// Create todo
async function createTodo(data) {
    const response = await apiRequest('POST', '', data);
    if (response && response.success) {
        await loadTodos();
        return true;
    }
    return false;
}

// Toggle todo completion
async function toggleTodo(id, completed) {
    const response = await apiRequest('PUT', `/${id}`, { completed });
    if (response && response.success) {
        await loadTodos();
    }
}

// Delete todo
async function deleteTodo(id) {
    if (!confirm('Are you sure you want to delete this todo?')) return;
    
    const response = await apiRequest('DELETE', `/${id}`);
    if (response && response.success) {
        await loadTodos();
    }
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Form submission
document.getElementById('todoForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        title: document.getElementById('todoTitle').value,
        description: document.getElementById('todoDescription').value,
        priority: document.getElementById('todoPriority').value,
        due_date: document.getElementById('todoDueDate').value || null
    };
    
    const success = await createTodo(data);
    if (success) {
        e.target.reset();
    }
});

// Refresh button
document.getElementById('refreshBtn').addEventListener('click', loadTodos);

// Initial load
loadTodos();

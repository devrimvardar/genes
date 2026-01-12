// Built-in API Client
const API_BASE = 'api/items';
let todos = [];

// Log API calls
function logAPI(method, url, data, response) {
    const log = document.getElementById('apiLog');
    const timestamp = new Date().toLocaleTimeString();
    const entry = `[${timestamp}] ${method} ${url}\n${JSON.stringify(response, null, 2)}\n\n`;
    log.textContent = entry + log.textContent;
}

// Make API request
async function apiRequest(method, endpoint, body = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (body) {
        options.body = JSON.stringify(body);
    }
    
    try {
        const url = API_BASE + endpoint;
        const response = await fetch(url, options);
        const data = await response.json();
        logAPI(method, url, body, data);
        return data;
    } catch (error) {
        console.error('API Error:', error);
        logAPI(method, endpoint, body, { error: error.message });
        return null;
    }
}

// Load todos using built-in API with filters
async function loadTodos() {
    const response = await apiRequest('GET', ';filters[type]=todo');
    if (response && response.success) {
        todos = response.data || [];
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
    
    container.innerHTML = todos.map(todo => {
        // Built-in API auto-decodes JSON fields, so meta is already an object
        const meta = (typeof todo.meta === 'string') ? JSON.parse(todo.meta) : (todo.meta || {});
        const priority = meta.priority || 'normal';
        const completed = todo.state === 'completed';
        
        return `
            <div class="todo-item ${completed ? 'completed' : ''}">
                <div class="todo-content">
                    <div class="todo-title">${escapeHtml(todo.title)}</div>
                    ${todo.text ? `<div class="text-muted text-sm">${escapeHtml(todo.text)}</div>` : ''}
                    <div class="todo-meta">
                        <span class="priority-badge priority-${priority}">${priority}</span>
                        <span class="text-muted text-sm">${todo.created_at}</span>
                    </div>
                </div>
                <div class="todo-actions">
                    <button class="btn btn-sm" onclick="toggleTodo('${todo.hash}', ${!completed})">
                        ${completed ? 'Undo' : 'Complete'}
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteTodo('${todo.hash}')">
                        Delete
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Create todo using built-in API
async function createTodo(data) {
    // Built-in API expects database field names
    const payload = {
        type: 'todo',
        state: 'pending',
        title: data.title,
        text: data.description || '',
        meta: JSON.stringify({ priority: data.priority })
    };
    
    const response = await apiRequest('POST', '', payload);
    if (response && response.success) {
        await loadTodos();
        return true;
    }
    return false;
}

// Toggle todo completion
async function toggleTodo(hash, completed) {
    const response = await apiRequest('PUT', `/${hash}`, {
        state: completed ? 'completed' : 'pending'
    });
    
    if (response && response.success) {
        await loadTodos();
    }
}

// Delete todo
async function deleteTodo(hash) {
    if (!confirm('Are you sure you want to delete this todo?')) return;
    
    const response = await apiRequest('DELETE', `/${hash}`, { hard: true });
    
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
        priority: document.getElementById('todoPriority').value
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

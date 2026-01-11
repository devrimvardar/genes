# Example 5: Single Page Application (SPA)

A frontend-only todo list application demonstrating Genes JS capabilities.

## What This Demonstrates

- Frontend-only app (no PHP backend needed)
- State management with `g.set()` / `g.get()`
- Event delegation with `g.on()`
- DOM manipulation with Genes JS utilities
- LocalStorage persistence (`g.ls`)
- Function registry pattern
- Reactive rendering
- Modern SPA architecture

## How to Run

```bash
cd examples/5-spa
php -S localhost:8000
```

OR open `index.html` directly in your browser (no server needed!)

Visit: http://localhost:8000

## Features

- ✅ Add tasks
- ✅ Mark tasks as complete
- ✅ Delete tasks
- ✅ Statistics (total, completed, pending)
- ✅ LocalStorage persistence (survives page refresh)
- ✅ Responsive UI
- ✅ No backend required

## Code Highlights

### State Management
```javascript
// Initialize state
g.set("tasks", []);

// Get state
var tasks = g.get("tasks");

// Update state
tasks.push(newTask);
g.set("tasks", tasks);
```

### LocalStorage
```javascript
// Save to localStorage
g.ls.set("genes_tasks", tasks);

// Load from localStorage
var saved = g.ls.get("genes_tasks");
```

### Event Delegation
```javascript
// Form submission
g.on("submit", "#addTaskForm", function(form, e) {
    e.preventDefault();
    // Handle form
});

// Click events on dynamic elements
g.on("click", ".task-delete", function(button) {
    var id = button.getAttribute("data-id");
    // Delete task
});
```

### DOM Manipulation
```javascript
// Create element
var div = g.create("div", {
    className: "task",
    innerHTML: "<span>Task</span>"
});

// Select element
var container = g.el("#tasksList");

// Append
container.appendChild(div);
```

### Function Registry
```javascript
// Define functions
g.def("app.init", function() { /* ... */ });
g.def("app.addTask", function(title) { /* ... */ });
g.def("app.render", function() { /* ... */ });

// Run functions
g.run("app.init");
g.run("app.addTask", "New task");
```

## Architecture

```
User Interaction
    ↓
Event Handler
    ↓
Update State (g.set)
    ↓
Save to LocalStorage
    ↓
Re-render UI
```

## No Backend Required

This example runs entirely in the browser:
- No database
- No PHP processing
- No API calls
- Uses LocalStorage for data persistence

## Extending This Example

Ideas for enhancements:

1. **Add due dates**
```javascript
task.dueDate = "2026-01-15";
```

2. **Add priorities**
```javascript
task.priority = "high"; // high, medium, low
```

3. **Add categories**
```javascript
task.category = "work"; // work, personal, shopping
```

4. **Add backend sync**
```javascript
g.api.create("clones", task, function(response) {
    // Task saved to server
});
```

5. **Add search/filter**
```javascript
g.def("app.filterTasks", function(query) {
    return tasks.filter(function(t) {
        return t.title.toLowerCase().includes(query.toLowerCase());
    });
});
```

## Genes JS Features Used

- `g.set()` / `g.get()` - State management
- `g.def()` / `g.run()` - Function registry
- `g.el()` - querySelector wrapper
- `g.create()` - Create elements
- `g.on()` - Event delegation
- `g.ls.set()` / `g.ls.get()` - LocalStorage
- `g.encode()` - HTML encoding
- `g.now()` - Current timestamp
- `g.que()` - Lifecycle hooks

## Browser Support

Works in all modern browsers that support:
- ES5 JavaScript
- LocalStorage
- addEventListener

## Performance

- Lightweight (~23KB for genes.js)
- No dependencies
- Instant rendering
- Efficient event delegation
- Minimal memory footprint

---

**This is the power of Genes Framework - build complete applications with minimal code!**

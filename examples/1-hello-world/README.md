# Example 1: Hello World

The simplest possible Genes Framework application.

## What This Demonstrates

- Basic framework inclusion
- Minimal setup required
- Performance tracking

## How to Run

```bash
cd examples/1-hello-world
php -S localhost:8000
```

Visit: http://localhost:8000

## Code Explanation

```php
<?php
require_once '../../genes.php';  // Include framework

echo "Hello, Genes Framework!";  // Output content

$perf = g::run("log.performance", true);  // Get performance stats
```

## Expected Output

You should see:
- "Hello, Genes Framework!" message
- Performance metrics showing initialization time

## What's Next

Check out [Example 2: Database CRUD](../2-database-crud/) to see database operations.

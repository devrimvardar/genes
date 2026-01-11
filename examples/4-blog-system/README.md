# Example 4: Blog System

A complete blog application with user authentication, posts, and a clean UI.

## What This Demonstrates

- User registration and authentication
- Session management
- Creating blog posts
- Displaying posts with author information
- Form handling
- Security (password hashing, XSS prevention)
- Database relationships (posts → authors)
- Flash messages
- Responsive UI

## Prerequisites

- MySQL or MariaDB database

## Setup

1. Create database:
```sql
CREATE DATABASE genes_blog;
```

2. Update credentials in `index.php` if needed

## How to Run

```bash
cd examples/4-blog-system
php -S localhost:8000
```

Visit: http://localhost:8000

## Features

### User Authentication
- Register new accounts
- Login/logout
- Session persistence
- Secure password hashing (bcrypt + HMAC pepper)

### Blog Posts
- Create posts (logged in users)
- View all posts
- See author information
- Timestamp display

### Security
- XSS prevention (`htmlspecialchars`)
- SQL injection prevention (automatic with PDO)
- Password hashing (bcrypt + pepper)
- CSRF protection ready

## Usage

1. **Register** a new account
2. **Login** with your credentials
3. **Create a post** using the form
4. **View posts** from all users
5. **Logout** when done

## Database Structure

### persons table
- Stores user accounts
- Email, password hash, name
- Created via `auth.register()`

### clones table
- Stores blog posts
- Links to author via `person_hash`
- Includes title, content, meta (views, comments)

## Code Highlights

### Registration
```php
$hash = g::run("auth.register", array(
    "email" => $_POST["email"],
    "password" => $_POST["password"],
    "name" => $_POST["name"]
));
```

### Login
```php
$success = g::run("auth.login", $email, $password);
if ($success) {
    $user = g::run("auth.user");
}
```

### Create Post
```php
$hash = g::run("db.insert", "clones", array(
    "type" => "post",
    "person_hash" => $user["hash"],
    "title" => $_POST["title"],
    "content" => $_POST["content"]
));
```

### List Posts with Authors
```php
$posts = g::run("db.select", "clones", array("type" => "post"));

foreach ($posts as &$post) {
    $authors = g::run("db.select", "persons", array(
        "hash" => $post["person_hash"]
    ));
    $post["author"] = $authors[0];
}
```

## Customization Ideas

- Add comments system
- Implement post editing/deletion
- Add categories or tags
- Implement markdown support
- Add user profiles
- Implement post likes/votes
- Add pagination
- Implement search

## What's Next

Check out [Example 5: Single Page App](../5-spa/) for a frontend-driven application.

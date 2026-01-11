# Security Policy

## Supported Versions

We release patches for security vulnerabilities. Currently supported versions:

| Version | Supported          |
| ------- | ------------------ |
| 2.0.x   | :white_check_mark: |
| < 2.0   | :x:                |

---

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to: **security@genes.one**

Include the following information:

- Type of vulnerability
- Full paths of source file(s) related to the vulnerability
- Location of the affected source code (tag/branch/commit or direct URL)
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the vulnerability, including how an attacker might exploit it

### What to Expect

1. **Acknowledgment** - We'll acknowledge your email within 48 hours
2. **Investigation** - We'll investigate and validate the issue
3. **Updates** - We'll keep you informed of our progress
4. **Resolution** - We'll release a fix and notify you
5. **Credit** - We'll credit you in the security advisory (if desired)

### Timeline

- **48 hours**: Initial response
- **7 days**: Validation and assessment
- **30 days**: Patch development and release (for critical issues)

---

## Security Best Practices

When using Genes Framework, follow these security practices:

### 1. Password Security

```php
// ✅ Good: Use built-in password hashing
$hash = g::run("auth.hash", $password);
$valid = g::run("auth.verify", $password, $hash);

// ❌ Bad: Plain text or weak hashing
$hash = md5($password); // Never do this!
```

### 2. SQL Injection Prevention

```php
// ✅ Good: Use framework functions (automatic prepared statements)
$users = g::run("db.select", "persons", array(
    "email" => $_POST["email"]
));

// ❌ Bad: Direct SQL with user input
$query = "SELECT * FROM persons WHERE email = '" . $_POST["email"] . "'";
```

### 3. XSS Prevention

```php
// ✅ Good: Use template rendering with auto-escaping
echo g::run("tpl.render", "<p>{{userContent}}</p>", $data);

// ✅ Good: Manual escaping
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// ❌ Bad: Direct output of user input
echo $_POST["content"]; // Never do this!
```

### 4. CSRF Protection

```php
// ✅ Good: Use CSRF tokens
session_start();
$token = g::run("auth.token");
$_SESSION["csrf_token"] = $token;

// In form:
// <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

// On submit:
if ($_POST["csrf_token"] === $_SESSION["csrf_token"]) {
    // Process form
}
```

### 5. File Upload Security

```php
// ✅ Good: Validate and sanitize uploads
$allowedTypes = array("image/jpeg", "image/png");
$maxSize = 5 * 1024 * 1024; // 5MB

if (in_array($_FILES["file"]["type"], $allowedTypes) && 
    $_FILES["file"]["size"] <= $maxSize) {
    // Process upload
}

// ❌ Bad: Accept any file
move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/" . $_FILES["file"]["name"]);
```

### 6. Session Security

```php
// ✅ Good: Initialize sessions securely
session_start();
g::run("auth.init");

// Regenerate session ID on login
session_regenerate_id(true);

// ❌ Bad: No session regeneration
// Sessions become vulnerable to fixation attacks
```

### 7. Input Validation

```php
// ✅ Good: Validate all input
$email = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);
$age = filter_var($_POST["age"], FILTER_VALIDATE_INT);

if ($email && $age) {
    // Process data
}

// ❌ Bad: Trust user input
$data = $_POST; // Never assume input is safe
```

### 8. Error Handling

```php
// ✅ Good: Don't expose sensitive information
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ❌ Bad: Display errors in production
ini_set('display_errors', 1); // Exposes system info
```

### 9. Database Credentials

```php
// ✅ Good: Use environment variables or config files outside web root
$config = json_decode(file_get_contents("../data/config.json"), true);
g::run("db.connect", $config["database"]);

// ❌ Bad: Hard-code credentials in PHP files
g::run("db.connect", array(
    "user" => "root",
    "pass" => "password123" // Never do this!
));
```

### 10. API Security

```php
// ✅ Good: Implement authentication for API endpoints
g::run("route.parseUrl");
$request = g::get("request");

if ($request["segments"][0] === "api") {
    if (!g::run("api.checkAuth")) {
        g::run("api.respond", array(
            "success" => false,
            "error" => "Unauthorized"
        ), 401);
        exit;
    }
    
    // Handle authenticated API request
}

// ❌ Bad: Open API without authentication
```

---

## Known Security Considerations

### 1. File-Based Configuration

- Store `config.json` outside web root when possible
- Set proper file permissions (600 or 640)
- Never commit config files with credentials to git

### 2. Database Schema

- The universal schema stores metadata as JSON
- Validate JSON data before use
- Be careful with `eval()` or `unserialize()` on meta fields

### 3. HMAC Pepper

- The framework uses an HMAC pepper for password hashing
- Generate a strong random pepper on first run
- Store it securely in config (never hard-code)
- Don't change the pepper or all passwords become invalid

### 4. Session Storage

- Default session storage is file-based
- Consider Redis or database storage for production
- Implement session timeout policies

### 5. Rate Limiting

- The framework doesn't include built-in rate limiting
- Implement rate limiting for production APIs
- Use reverse proxy (nginx) or application-level limits

---

## Security Features

### Built-in Protections

✅ **Password Hashing**: Bcrypt + HMAC pepper  
✅ **SQL Injection**: PDO prepared statements (automatic)  
✅ **XSS**: Template auto-escaping  
✅ **CSRF**: Token generation helpers  
✅ **Session Security**: Secure defaults  

### Your Responsibility

⚠️ Input validation  
⚠️ Output encoding  
⚠️ Access control  
⚠️ Rate limiting  
⚠️ File upload validation  
⚠️ HTTPS in production  

---

## Security Checklist

Before deploying to production:

- [ ] Use HTTPS (TLS/SSL) for all connections
- [ ] Set `display_errors = 0` in PHP
- [ ] Use strong database passwords
- [ ] Store credentials outside web root
- [ ] Validate and sanitize all user input
- [ ] Implement rate limiting on APIs
- [ ] Set proper file permissions (files: 644, directories: 755)
- [ ] Use CSRF tokens on all forms
- [ ] Regenerate session IDs on login
- [ ] Set up regular backups
- [ ] Keep PHP and database up to date
- [ ] Monitor logs for suspicious activity
- [ ] Implement proper error handling

---

## Disclosure Policy

We follow a **coordinated disclosure** policy:

1. Security researchers report vulnerabilities privately
2. We work together to understand and fix the issue
3. We release a patch
4. We publish a security advisory
5. We credit the researcher (if desired)

We aim to release patches within 30 days for critical issues.

---

## Security Updates

Subscribe to security announcements:

- Watch the repository for releases
- Check CHANGELOG.md for security fixes
- Follow project announcements

---

## Hall of Fame

Security researchers who have helped improve Genes Framework:

*(No vulnerabilities reported yet)*

---

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [Web Security Academy](https://portswigger.net/web-security)

---

**Remember: Security is a shared responsibility. Stay vigilant! 🔒**

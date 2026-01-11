# Contributing to Genes Framework

Thank you for considering contributing to Genes Framework! We welcome contributions from everyone.

---

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Guidelines](#coding-guidelines)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)

---

## Code of Conduct

This project follows a simple code of conduct:

- **Be respectful** - Treat everyone with respect
- **Be constructive** - Provide helpful feedback
- **Be collaborative** - Work together toward common goals
- **Be patient** - Remember we're all volunteers

---

## How Can I Contribute?

### 1. Reporting Bugs

Found a bug? Help us fix it!

- Check if the bug has already been reported in [Issues](https://github.com/devrimvardar/genes/issues)
- If not, create a new issue with:
  - Clear title and description
  - Steps to reproduce
  - Expected vs actual behavior
  - PHP version, OS, and environment details
  - Code samples if applicable

### 2. Suggesting Features

Have an idea for improvement?

- Check [Discussions](https://github.com/devrimvardar/genes/discussions) first
- Open a new discussion describing:
  - The problem you're trying to solve
  - Your proposed solution
  - Any alternatives you've considered
  - Examples of how it would work

### 3. Writing Documentation

Documentation improvements are always welcome:

- Fix typos or clarify existing docs
- Add examples to function documentation
- Create tutorials or guides
- Improve README or other markdown files

### 4. Contributing Code

Ready to write some code?

- Pick an issue labeled `good first issue` or `help wanted`
- Comment on the issue to claim it
- Fork the repository and create a branch
- Write your code following our guidelines
- Submit a pull request

---

## Development Setup

### Prerequisites

- PHP 5.6 or higher (recommended: PHP 7.4+)
- MySQL/MariaDB (for database testing)
- Git
- Text editor or IDE

### Setup Steps

```bash
# Fork the repository on GitHub, then:

# Clone your fork
git clone https://github.com/YOUR-USERNAME/genes.git
cd genes

# Create a branch for your feature
git checkout -b feature/your-feature-name

# Make your changes
# Test your changes

# Commit and push
git add .
git commit -m "Add your descriptive commit message"
git push origin feature/your-feature-name
```

### Testing

```bash
# Test with PHP built-in server
cd examples/1-hello-world
php -S localhost:8000

# Test database functionality
cd examples/2-database-crud
# Edit config.json with your database credentials
php -S localhost:8000

# Test REST API
cd examples/3-rest-api
php -S localhost:8000
```

---

## Coding Guidelines

### PHP Code Style

- Follow **PSR-12** coding standard
- Use **4 spaces** for indentation (not tabs)
- Use meaningful variable and function names
- Add PHPDoc comments for all functions
- Keep functions focused and single-purpose

**Example:**

```php
/**
 * Select records from database
 * 
 * @param string $table Table name
 * @param array $where Where conditions
 * @param string $schema Schema to use
 * @param array $options Query options (limit, offset, order)
 * @return array|false Array of records or false on error
 * 
 * @example
 * $users = g::run("db.select", "persons", array("state" => "active"));
 */
g::def("db.select", function($table, $where = array(), $schema = "default", $options = array()) {
    // Implementation
});
```

### JavaScript Code Style

- Use **4 spaces** for indentation
- Use modern ES5+ syntax (but maintain compatibility)
- Add JSDoc comments for all functions
- Follow existing patterns in `genes.js`

**Example:**

```javascript
/**
 * Select element by CSS selector
 * @param {string} selector - CSS selector
 * @return {Element|null} Element or null
 * 
 * @example
 * const btn = g.el("#myButton");
 */
g.el = function(selector) {
    return document.querySelector(selector);
};
```

### CSS Code Style

- Use **4 spaces** for indentation
- Follow BEM-like naming for custom classes
- Keep specificity low
- Add comments for sections
- Mobile-first approach

**Example:**

```css
/* Button Component */
.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    cursor: pointer;
}

.btn-primary {
    background: var(--primary);
    color: var(--primary-text);
}
```

### General Guidelines

- **Backward Compatibility**: Don't break existing APIs without major version bump
- **Dependencies**: Keep zero-dependency promise (no Composer, npm packages)
- **File Size**: Be mindful of adding to file size
- **Performance**: Consider performance impact of changes
- **Documentation**: Update docs with code changes
- **Examples**: Add examples for new features

---

## Pull Request Process

### Before Submitting

1. **Test your changes** thoroughly
2. **Update documentation** if needed
3. **Add examples** for new features
4. **Check code style** matches guidelines
5. **Write clear commit messages**

### Commit Message Format

```
Type: Short description (50 chars or less)

Longer explanation if needed (wrap at 72 chars).
Explain what and why, not how.

- Bullet points are fine
- Use present tense ("Add feature" not "Added feature")
- Reference issues: Fixes #123
```

**Types:**
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `style:` Code style changes (formatting, etc.)
- `refactor:` Code refactoring
- `test:` Adding tests
- `chore:` Maintenance tasks

**Examples:**
```
feat: Add API rate limiting support

Implements configurable rate limiting for API endpoints.
Supports both IP-based and user-based limits.

Fixes #42
```

### Submitting Pull Request

1. Push your branch to GitHub
2. Open a pull request against `main` branch
3. Fill out the PR template completely
4. Link related issues
5. Wait for review
6. Address any requested changes
7. Celebrate when merged! 🎉

### PR Review Process

- Maintainers will review within 1-7 days
- You may be asked to make changes
- Once approved, a maintainer will merge
- Your contribution will be in the next release!

---

## Reporting Bugs

### Bug Report Template

```markdown
**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce:
1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What you expected to happen.

**Actual behavior**
What actually happened.

**Code sample**
```php
// Minimal code to reproduce the issue
```

**Environment**
- PHP Version: [e.g. 7.4.0]
- Database: [e.g. MySQL 8.0]
- OS: [e.g. Ubuntu 20.04]
- Browser: [e.g. Chrome 95] (if relevant)

**Additional context**
Any other relevant information.
```

---

## Suggesting Features

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
A clear description of the problem. Ex. I'm always frustrated when [...]

**Describe the solution you'd like**
A clear description of what you want to happen.

**Describe alternatives you've considered**
Other solutions or features you've considered.

**Example usage**
```php
// Show how the feature would be used
$result = g::run("your.newFeature", $args);
```

**Additional context**
Any other context or screenshots.
```

---

## Recognition

Contributors will be recognized in:

- `README.md` contributors section
- Release notes
- Project website (if applicable)

---

## Questions?

- Open a [Discussion](https://github.com/devrimvardar/genes/discussions)
- Check existing [Issues](https://github.com/devrimvardar/genes/issues)
- Read the [Documentation](docs/)

---

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for making Genes Framework better! 🙏

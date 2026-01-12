# Example 2: Multi-Language Blog System

This example demonstrates how to build a complete multi-language blog system using the Genes Framework with SQLite database integration.

## What This Example Teaches

### 1. **Genes Standard Database Schema**
- Using the 5-table schema: `clones`, `persons`, `items`, `labels`, `events`
- Blog posts stored in `items` table with `type="post"` and `state="published"`
- Categories/tags using the `labels` table
- Flexible JSON fields (`labels`, `meta`, `media`, `data`) for extensibility

### 2. **Database Operations**
- `db.createSchema()` - Creates standard Genes tables
- `db.insert()` - Adds records with auto-generated hash, timestamps, clone_id
- `db.select()` - Queries with conditions
- Using `clone_id` for multi-tenant isolation

### 3. **Multi-Language Content**
- Language stored in `labels` JSON field alongside categories
- Same routing structure for all languages
- Language-filtered queries in clone functions
- Translations in config.json

### 4. **Template Features**
- `data-g-if` for conditional rendering (pagination, related posts)
- `data-g-for` for loops (post lists)
- `data-g-bind` for text content
- `data-g-html` for HTML content (post body)
- `data-g-attr` for dynamic attributes (links)

### 5. **Advanced Features**
- Pagination with page query parameter
- Related posts by category
- JSON field parsing (labels, meta)
- Auto-setup on first run

## File Structure

```
2-blog-system/
├── index.php              # Main application with database setup
├── data/
│   ├── config.json        # Routes, views, translations
│   └── blog.db            # SQLite database (auto-created)
├── ui/
│   ├── index.html         # Blog post list template
│   ├── post.html          # Single post template
│   └── assets/
│       └── app.css        # Blog-specific styles
├── cache/                 # Template cache
└── README.md             # This file
```

## Database Schema Usage

### Items Table (Blog Posts)
```php
array(
    "type" => "post",                    // Content type
    "state" => "published",              // Visibility state
    "title" => "Post Title",             // Post title
    "safe_url" => "post-slug",           // URL slug (unique)
    "blurb" => "Excerpt...",             // Short description
    "text" => "<p>Content...</p>",       // Full HTML content
    "labels" => json_encode(array(       // Language + category
        "en", "tutorial"
    )),
    "meta" => json_encode(array(         // Metadata
        "author" => "John Doe",
        "reading_time" => "5 min"
    ))
)
```

### Labels Table (Categories/Languages)
```php
array(
    "type" => "category",                // Label type
    "key" => "tutorial",                 // Machine-readable key
    "name" => "Tutorial"                 // Display name
)
```

## How It Works

### 1. Setup (First Run)
- Creates SQLite database at `data/blog.db`
- Runs `db.createSchema()` to create 5 standard tables
- Creates blog clone in `clones` table
- Seeds categories in `labels` table
- Seeds sample posts in `items` table

### 2. Routing
- `/blog` → Shows paginated post list
- `/blog/post-slug` → Shows single post
- Works for all languages: `/`, `/tr`, `/de`

### 3. Clone Functions

**Index** - Post List:
- Selects all posts with `type="post"` and `state="published"`
- Filters by language from `labels` JSON field
- Sorts by `created_at` descending
- Paginates results (5 per page)

**Post** - Single Post:
- Finds post by `safe_url` slug
- Parses `labels` and `meta` JSON fields
- Queries related posts (same category + language)
- Renders single post template

### 4. Template Rendering
- `data-g-for="post in posts"` loops through results
- `data-g-bind="post.title"` displays text
- `data-g-html="post.text"` displays HTML content
- `data-g-attr="href:/blog/{{post.safe_url}}"` creates dynamic links

## Running the Example

1. **Access the blog:**
   ```
   http://localhost/examples/2-blog-system/
   ```

2. **Switch languages:**
   - English: `/`
   - Turkish: `/tr`
   - German: `/de`

3. **Navigate:**
   - Click post titles to view full content
   - Use pagination to browse posts
   - View related posts at bottom of single post page

## Key Takeaways

### ✅ DO:
- Use `items` table for ALL content (posts, pages, products, etc.)
- Set `type` field to categorize content
- Store language in `labels` JSON field
- Use `safe_url` for URL slugs
- Use `blurb` for excerpts, `text` for full content
- Store metadata in `meta` JSON field
- Configure database in `config.json` (auto-connects and creates schema)
- Use `db.select()`, `db.insert()`, `db.update()`, `db.delete()` for queries

### ❌ DON'T:
- Create custom database tables (e.g., `posts`, `articles`)
- Store language in separate columns
- Write raw SQL queries
- Mix languages in the same route

## Adapting for Your Project

### Adding Comments
Store comments as `items` with `type="comment"` and reference post in `meta`:
```php
array(
    "type" => "comment",
    "state" => "approved",
    "text" => "Great post!",
    "meta" => json_encode(array(
        "post_hash" => $postHash,
        "author_name" => "Jane"
    ))
)
```

### Adding Authors
Use the `persons` table:
```php
$person = g::run("db.insert", "persons", array(
    "type" => "author",
    "alias" => "johndoe",
    "name" => "John Doe"
));
```

Reference in post:
```php
"created_by" => $personHash
```

### Adding Tags
Store in `labels` table with `type="tag"`:
```php
g::run("db.insert", "labels", array(
    "type" => "tag",
    "key" => "php",
    "name" => "PHP"
));
```

Reference in post labels:
```php
"labels" => json_encode(array("en", "tutorial", "php", "database"))
```

## Learn More

- See `GENES-V2-CAPABILITIES.md` for complete template engine reference
- See `DATABASE-SCHEMA.md` for full schema documentation
- Check Example 1 for multi-language routing basics
- Check Example 3 for REST API implementation

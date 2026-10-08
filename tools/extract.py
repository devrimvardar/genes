"""Builds the COPY EXACTLY files of examples/corporate from genes.md.

Run from the repository root: python tools/extract.py
"""
import re
import pathlib

root = pathlib.Path(__file__).resolve().parent.parent
spec = (root / 'genes.md').read_text(encoding='utf-8')
out = root / 'examples' / 'corporate'


def section(title):
    start = spec.index(title)
    return spec[start:]


def first_block(text, lang):
    return re.search(r'```' + lang + r'\n(.*?)```', text, re.S).group(1)


def write(path, text):
    target = out / path
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(text, encoding='utf-8', newline='\n')


def replace_once(text, old, new):
    assert text.count(old) == 1, old
    return text.replace(old, new)


# Core runtime with the module lines that genes.md tells us to add.
index = first_block(section('## 4. Runtime: index.php'), 'php')
index = replace_once(index, "    // Module A adds one line here.\n    if ($slug === null)",
    "    if (isset($data['collection'])) return load_collection($data, $slug);\n"
    "    $data = with_latest($data);\n    if ($slug === null)")
index = replace_once(index, "            // Module A adds one line here.\n",
    "            if (isset($data['collection'])) $slugs = collection_slugs((string) $data['collection']);\n")
index = replace_once(index, "    // Module C adds one line here.\n",
    "    if ($page === 'contact') $view['form'] = form_state();\n")
modules = (
    "require ROOT . '/app/db.php';\n"
    + first_block(section('Connect the admin in `index.php`'), 'php')
    + first_block(section('### C.2 Connect Module C'), 'php')
)
index = replace_once(index, "// Modules add their requires and routes here.\n",
    "// Modules add their requires and routes here.\n" + modules)
write('index.php', index)

write('.htaccess', first_block(section('## 3. .htaccess'), 'apache'))
write('assets/items/.htaccess', first_block(section('Every folder that receives uploads'), 'apache'))
write('app/schema.sql', first_block(section('### A.2 app/schema.sql'), 'sql'))
write('app/db.php', first_block(section('### A.3 app/db.php'), 'php'))
write('app/auth.php', first_block(section('### B.2 app/auth.php'), 'php'))
write('app/forms.php', first_block(section('### C.3 app/forms.php'), 'php'))
write('app/slugify.php', "<?php\ndeclare(strict_types=1);\n\n" + first_block(section('Create slugs only with `slugify()`'), 'php'))
print('extracted into', out)

# layout.php is COPY EXACTLY; the example only extends the footer markup.
layout = first_block(section('`templates/layout.php` — COPY EXACTLY'), 'php')
layout = replace_once(layout, """    <footer class="footer">
        <p><?= e($common['footer']) ?></p>
    </footer>
""", """    <?php require ROOT . '/templates/partials/cta.php'; ?>
    <footer class="footer">
        <div class="footer-about">
            <a class="brand" href="<?= e(url($locale . '/')) ?>"><?= e($site['name']) ?></a>
            <p><?= e($common['footer']['about']) ?></p>
        </div>
        <div>
            <h2><?= e($common['footer']['offices']) ?></h2>
<?php foreach ($common['offices'] as $office): ?>
            <p><strong><?= e($office['city']) ?></strong><br><?= e($office['address']) ?></p>
<?php endforeach; ?>
        </div>
        <div>
            <h2><?= e($common['footer']['contact']) ?></h2>
            <p><a href="mailto:<?= e($common['email']) ?>"><?= e($common['email']) ?></a><br><?= e($common['phone']) ?></p>
        </div>
        <p class="footer-rights"><?= e($common['footer']['rights']) ?></p>
    </footer>
""")
write('templates/layout.php', layout)
print('layout written')

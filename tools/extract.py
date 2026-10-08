"""Writes the COPY EXACTLY files of genes.md into examples/corporate and website.

Both projects therefore always run the code that genes.md prescribes. Only the
markup that genes.md allows a project to add (extra header and footer markup) and
the module lines that genes.md tells us to insert are changed here.

Run from the repository root: python tools/extract.py
"""
import pathlib
import re

root = pathlib.Path(__file__).resolve().parent.parent
spec = (root / 'genes.md').read_text(encoding='utf-8')


def section(title):
    return spec[spec.index(title):]


def first_block(text, lang):
    return re.search(r'```' + lang + r'\n(.*?)```', text, re.S).group(1)


def replace_once(text, old, new):
    assert text.count(old) == 1, old
    return text.replace(old, new)


def write(base, path, text):
    target = base / path
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(text, encoding='utf-8', newline='\n')


core_index = first_block(section('## 4. Runtime: index.php'), 'php')
core_htaccess = first_block(section('## 3. .htaccess'), 'apache')
upload_htaccess = first_block(section('Every folder that receives uploads'), 'apache')
core_layout = first_block(section('`templates/layout.php` — COPY EXACTLY'), 'php')
default_footer = """    <footer class="footer">
        <p><?= e($common['footer']) ?></p>
    </footer>
"""


def build_corporate():
    out = root / 'examples' / 'corporate'
    index = core_index
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
    write(out, 'index.php', index)
    write(out, '.htaccess', core_htaccess)
    write(out, 'assets/items/.htaccess', upload_htaccess)
    write(out, 'app/schema.sql', first_block(section('### A.2 app/schema.sql'), 'sql'))
    write(out, 'app/db.php', first_block(section('### A.3 app/db.php'), 'php'))
    write(out, 'app/auth.php', first_block(section('### B.2 app/auth.php'), 'php'))
    write(out, 'app/forms.php', first_block(section('### C.3 app/forms.php'), 'php'))
    write(out, 'app/slugify.php', "<?php\ndeclare(strict_types=1);\n\n"
        + first_block(section('Create slugs only with `slugify()`'), 'php'))

    layout = replace_once(core_layout, default_footer, """    <?php require ROOT . '/templates/partials/cta.php'; ?>
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
    write(out, 'templates/layout.php', layout)
    print('extracted', out.relative_to(root))


def build_website():
    out = root / 'website'
    write(out, 'index.php', core_index)
    write(out, '.htaccess', core_htaccess)

    layout = replace_once(core_layout, "</head>\n", """<?php if (!empty($site['analytics'])): ?>
    <script src="<?= e(url('assets/js/consent.js')) ?>?v=<?= filemtime(ROOT . '/assets/js/consent.js') ?>" data-ga4="<?= e($site['analytics']) ?>" defer></script>
<?php endif; ?>
</head>
""")
    layout = replace_once(layout, """<?php endforeach; ?>
        </nav>
<?php if (count($locales) > 1): ?>""", """<?php endforeach; ?>
            <a href="https://github.com/devrimvardar/genes"><?= e($common['github']) ?></a>
        </nav>
<?php if (count($locales) > 1): ?>""")
    layout = replace_once(layout, default_footer, """    <footer class="footer">
        <p><?= e($common['footer']) ?></p>
        <ul class="footer-links">
<?php foreach ($common['footer_links'] as $link): ?>
            <li><a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a></li>
<?php endforeach; ?>
        </ul>
    </footer>
""")
    layout = replace_once(layout, "</body>\n", """<?php if (!empty($site['analytics'])): ?>
<div class="consent" id="consent" hidden>
    <p><?= e($common['consent']['text']) ?></p>
    <div class="consent-actions">
        <button class="button" type="button" data-consent="yes"><?= e($common['consent']['accept']) ?></button>
        <button class="button button-ghost" type="button" data-consent="no"><?= e($common['consent']['decline']) ?></button>
    </div>
</div>
<?php endif; ?>
</body>
""")
    write(out, 'templates/layout.php', layout)
    print('extracted', out.relative_to(root))


build_corporate()
build_website()

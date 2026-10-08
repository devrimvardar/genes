"""Rebuilds examples/corporate from genes.md and packs the download zip.

Run from anywhere: python tools/build.py
Output: website/downloads/genes-corporate-example.zip
"""
import pathlib
import runpy
import zipfile

root = pathlib.Path(__file__).resolve().parent.parent
example = root / 'examples' / 'corporate'
target = root / 'website' / 'downloads' / 'genes-corporate-example.zip'

# Never ship runtime files that only exist on a running installation.
excluded = {'data/content.sqlite', 'data/error.log', 'data/setup-token.txt'}

runpy.run_path(str(root / 'tools' / 'extract.py'))

target.parent.mkdir(parents=True, exist_ok=True)
with zipfile.ZipFile(target, 'w', zipfile.ZIP_DEFLATED) as archive:
    files = sorted(path for path in example.rglob('*') if path.is_file())
    for path in files:
        relative = path.relative_to(example).as_posix()
        if relative in excluded or relative.startswith('assets/items/') and relative != 'assets/items/.htaccess':
            continue
        info = zipfile.ZipInfo('genes-corporate-example/' + relative, date_time=(2026, 1, 1, 0, 0, 0))
        info.compress_type = zipfile.ZIP_DEFLATED
        info.external_attr = 0o644 << 16
        archive.writestr(info, path.read_bytes())
    spec = zipfile.ZipInfo('genes-corporate-example/genes.md', date_time=(2026, 1, 1, 0, 0, 0))
    spec.compress_type = zipfile.ZIP_DEFLATED
    spec.external_attr = 0o644 << 16
    archive.writestr(spec, (root / 'genes.md').read_bytes())

print('built', target.relative_to(root), target.stat().st_size, 'bytes')

# Live demo on genes.one: same example, but not indexed, without admin or form posts.
import json
import shutil

demo = root / 'website' / 'examples' / 'corporate'
demo_url = 'https://genes.one/examples/corporate'
if demo.exists():
    shutil.rmtree(demo)
for path in sorted(p for p in example.rglob('*') if p.is_file()):
    relative = path.relative_to(example).as_posix()
    if relative in excluded or relative == 'README.md' or (relative.startswith('assets/items/') and relative != 'assets/items/.htaccess'):
        continue
    (demo / relative).parent.mkdir(parents=True, exist_ok=True)
    shutil.copyfile(path, demo / relative)

config = json.loads((demo / 'data/config.json').read_text(encoding='utf-8'))
config['site']['url'] = demo_url
(demo / 'data/config.json').write_text(json.dumps(config, ensure_ascii=False, indent=4) + '\n', encoding='utf-8', newline='\n')

content = json.loads((demo / 'data/content.json').read_text(encoding='utf-8'))
content['contact']['intro'] = {
    'en': 'This is a live demo of the Genes corporate example. The form is shown but cannot be sent here.',
    'tr': 'Bu, Genes kurumsal örneğinin canlı demosudur. Form gösterilir ama burada gönderilemez.',
    'fi': 'Tämä on Genes-yritysesimerkin live-demo. Lomake näytetään, mutta sitä ei voi lähettää täällä.',
}
(demo / 'data/content.json').write_text(json.dumps(content, ensure_ascii=False, indent=4) + '\n', encoding='utf-8', newline='\n')

htaccess = (demo / '.htaccess').read_text(encoding='utf-8')
htaccess = htaccess.replace('RewriteEngine On\n', """RewriteEngine On

# Live demo on genes.one: not indexed, no admin, no form submissions.
<IfModule mod_headers.c>
    Header always set X-Robots-Tag "noindex"
</IfModule>
RewriteRule ^admin(/|$) - [F,L]
RewriteCond %{REQUEST_METHOD} POST
RewriteRule ^ - [F,L]
""", 1)
(demo / '.htaccess').write_text(htaccess, encoding='utf-8', newline='\n')

for name in ('robots.txt', 'llms.txt'):
    text = (demo / name).read_text(encoding='utf-8').replace('https://arkka.example', demo_url)
    (demo / name).write_text(text, encoding='utf-8', newline='\n')

print('built demo', demo.relative_to(root))

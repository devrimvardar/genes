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

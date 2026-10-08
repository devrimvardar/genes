# Genes

Genes is an AI-readable specification for generating small, dependency-free PHP websites.
It is an AI-friendly PHP website starter for ChatGPT, Claude, Cursor, Copilot and other
coding LLMs.

Give `genes.md` to an LLM, describe the site you want, and let it generate the project.

## Use

1. Download [`genes.md`](genes.md).
2. Put it in your project folder.
3. Ask your coding LLM to read it.
4. Describe the website you want it to create.

Example:

```text
Read genes.md and create a two-language corporate website with a SQLite-managed news section.
```

Genes is a specification, not a runtime framework. The generated project is the application.

Website: https://genes.one/

Documentation: https://genes.one/en/docs/

Examples: https://genes.one/en/examples/

Download: https://raw.githubusercontent.com/devrimvardar/genes/main/genes.md

## Example

[`examples/corporate`](examples/corporate) is a complete multilingual corporate site
generated from `genes.md`: three languages, an admin-managed news section (SQLite),
and a contact form. Download it as a zip from
[genes.one/en/examples](https://genes.one/en/examples).

The files marked COPY EXACTLY in `genes.md` are extracted into the example by
`tools/extract.py`, so the example always matches the specification. Rebuild the
example and the zip with:

```text
python tools/build.py
```

## Links

- [Genes website](https://genes.one/)
- [Genes specification](genes.md)
- [GitHub repository](https://github.com/devrimvardar/genes)
- [Rxions](https://rxions.com/)
- [Rxions on LinkedIn](https://www.linkedin.com/company/rxions-oy/)
- [Devrim Vardar](https://devrimvardar.com/)
- [Devrim Vardar on X](https://x.com/DevrimVardar)

## Website deployment

The genes.one website in `website/` is generated from `genes.md` and doubles as a
reference implementation. Upload the contents of that folder to the web root with
FTP, or serve it from a subdirectory such as `/genes/`. The `.htaccess` computes the
base path from the request, so the same files work at the domain root, in a
subdirectory, and behind a local Apache alias without any configuration change.

Run it locally without Apache:

```text
cd website
php -S localhost:8000 index.php
```

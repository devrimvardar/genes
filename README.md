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

## Links

- [Genes website](https://genes.one/)
- [Genes specification](genes.md)
- [GitHub repository](https://github.com/devrimvardar/genes)
- [Rxions](https://rxions.com/)
- [Rxions on LinkedIn](https://www.linkedin.com/company/rxions-oy/)
- [Devrim Vardar](https://devrimvardar.com/)
- [Devrim Vardar on X](https://x.com/DevrimVardar)

## Website deployment

The example site is in `website/`. Upload the contents of that folder to the
web root with FTP, or serve it from a subdirectory such as `/genes/`. The site
detects its base path automatically and keeps localized routes working in both
locations.

# AGENTS.md

This file provides guidance to coding agents when working with code in this repository.

## Project Overview

MCP4Joomla is an MCP (Model Context Protocol) server for Joomla 5+, written in PHP. It allows LLMs to interact with Joomla installations via the Joomla Web Services API. The server communicates over stdio transport using the `php-mcp/server` library.

## Development Commands

```bash
# Install dependencies
composer install

# Run the MCP server (stdio transport)
php mcp4joomla.php server

# Run with debug logging
php mcp4joomla.php server --debug

# Custom log file location
php mcp4joomla.php server --log=/path/to/file.log
```

Tests use PHPUnit with two suites, Unit (mocked HTTP clients, no Joomla needed) and Integration (needs a configured Joomla site, see `tests/integration.config.php.dist`): `composer test` runs both, `composer test:unit` and `composer test:integration` run one. See `TESTING.md`. There are no linters or CI pipelines configured.

## Required Environment Variables

- `JOOMLA_BASE_URL` — Base URL of the Joomla site (no trailing `/api` or `/index.php`)
- `BEARER_TOKEN` — Base64-encoded Joomla Super User API token

## Always-on rules

- Tools live in `src/Server/` and are auto-discovered through `#[McpTool]` attributes — no registration file.
- Call `$this->autologMCPTool()` at the start of every tool method.
- PHP 8.1+, `declare(strict_types=1)`, PSR-4 namespace `Dionysopoulos\Mcp4Joomla\`, Allman braces, tab indentation (see `code-style.md`).

## Git: commit and tag outside the sandbox

Commits and tags are always signed, with a key held in 1Password. The 1Password signing agent is reached
over a local socket that agent sandboxes do not expose, so a sandboxed `git commit` or `git tag` **always**
fails (e.g. `error: 1Password: Could not connect to socket. Is the agent running?`).

Run every `git commit` and `git tag` **outside the sandbox from the first attempt** — in Claude Code with
`dangerouslyDisableSandbox: true`, in other harnesses with their equivalent unsandboxed / escalated
execution. Do not try the sandboxed form first, do not diagnose the failure, and never work around it
with `--no-gpg-sign`, `-c commit.gpgsign=false` or unsigned tags.

## Project memory

Project memory lives in `.claude/memory/`, committed with the code, so that it is shared across machines
and across agentic harnesses (Claude Code, Codex, Qwen Code, Kimi Code, Junie, …). Read the matching file before starting that kind of work.

| File | Read it when… |
|------|---------------|
| [`architecture-and-di.md`](.claude/memory/architecture-and-di.md) | Before you change the entry point, the DI container/service providers, or the utility traits in `src/Utility/` |
| [`adding-tools-and-api-pattern.md`](.claude/memory/adding-tools-and-api-pattern.md) | Before you add or change an MCP tool in `src/Server/`, or make a Joomla Web Services API call |
| [`code-style.md`](.claude/memory/code-style.md) | Before you write or reformat PHP code in this repository |

### Recording new memories

This is the **default and only** place for project memory. Do not write memories for this project to a
harness's private memory store (such as Claude Code's auto-memory under `~/.claude/projects/`); write
them here instead:

- Add to the existing topic file when one fits; otherwise create a new kebab-case `.md` file named after
  the topic, and add a row for it to the table above with a concrete trigger.
- Plain Markdown, no frontmatter. State the rule, then **Why:** (the reason or incident behind it) and
  **How to apply:**. Link related files with relative Markdown links.
- Don't record what the code, Git history or an existing `AGENTS.md` already says — update that
  `AGENTS.md` instead when the rule belongs there. Remove or correct entries that turn out wrong.
- These files are committed: no secrets, credentials, customer data or personal details.

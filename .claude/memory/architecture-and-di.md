# Architecture, dependency injection and utility traits

Moved from `AGENTS.md`.

## Architecture

### Entry Point

`mcp4joomla.php` — Bootstraps Composer autoloading, creates the DI container, configures the MCP server, auto-discovers tools in `src/Server/`, and listens on stdio.

### Dependency Injection

Uses **Pimple** with service providers registered in `src/Container/Container.php`:

- `EnvironmentProvider` — Validates and exposes `JOOMLA_BASE_URL` and `BEARER_TOKEN`
- `OptionsParserProvider` — CLI argument parsing (`server`, `--debug`, `--log`)
- `InputProvider` — Parsed CLI input
- `LogProvider` — Monolog with rotating file handler (`log/debug.log`)
- `HttpProvider` — `HttpDecorator` wrapping Joomla HTTP client with API auth headers

Services are accessed via `Factory::getContainer()->get('serviceName')`. The container is a singleton managed by `src/Container/Factory.php`.

### MCP Tool Classes

Located in `src/Server/`. The `php-mcp/server` library auto-discovers tools by scanning this directory for `#[McpTool]` PHP attributes. Each public method annotated with `#[McpTool]` becomes an MCP tool.

Tool annotations use `ToolAnnotations` to declare hints: `readOnlyHint`, `destructiveHint`, `idempotentHint`.

Currently implemented: `src/Server/Content/Articles.php` (CRUD operations for Joomla articles).

### Utility Traits (`src/Utility/`)

Tool classes compose behavior via traits:

- `AutoLoggingTrait` — Call `$this->autologMCPTool()` at the start of each tool method; uses `debug_backtrace` + reflection to log the call with all arguments
- `ArticleTextTrait` — `toHtml()` converts Markdown to HTML via CommonMark
- `TitleToAliasTrait` — `titleToAlias()` generates URL slugs from titles
- `HandleJoomlaAPIErrorTrait` — Parses Joomla API error responses and throws exceptions
- `GetDataFromResponseTrait` — Extracts and validates JSON API response data
- `HttpDecorator` — Wraps `Joomla\Http\Http` with automatic auth headers (`Authorization: Bearer` + `X-Joomla-Token`) and request/response logging; provides `getUri()` for building API endpoint URIs

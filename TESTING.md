# Testing

MCP4Joomla uses PHPUnit 10.5 with two test suites: **Unit** and **Integration**.

## Requirements

- PHP 8.1+
- Composer dependencies installed (`composer install`)

## Running Tests

```bash
# Run all tests (unit + integration)
composer test

# Unit tests only
composer test:unit

# Integration tests only (requires configuration)
composer test:integration
```

## Unit Tests

Unit tests use mocked HTTP clients and do not require a Joomla installation. They cover:

- **Utility traits** — `VarToLogTrait`, `TitleToAliasTrait`, `ArticleTextTrait`, `HandleJoomlaAPIErrorTrait`, `GetDataFromResponseTrait`, `AutoLoggingTrait`
- **HttpDecorator** — URL building, auth header injection, HTTP method delegation
- **Container** — `EnvironmentProvider` validation, `Factory` singleton management
- **Tool classes** — `Articles` and `Tags` CRUD operations with mocked HTTP responses

### Test Helpers

- **`tests/bootstrap.php`** — Autoloading and the `createJoomlaResponse()` helper function
- **`tests/Unit/Stubs/TestContainerTrait.php`** — Sets up `Factory` with a mock `Http` wrapped in a real `HttpDecorator`, a `SpyLogger`, and dummy environment variables
- **`tests/Unit/Stubs/SpyLogger.php`** — PSR-3 logger that records all log calls for assertions
- **Trait stubs** (`VarToLogStub`, `TitleToAliasStub`, etc.) — Expose private trait methods as public for direct testing

### How Mocking Works

Tool classes call `Factory::getContainer()->get('http')` to get an `HttpDecorator`. Since `HttpDecorator` is `final`, we cannot mock it directly. Instead, we:

1. Create a PHPUnit mock of `Joomla\Http\Http` (which is not final)
2. Wrap it in a real `HttpDecorator` with logging disabled
3. Inject the whole setup via `Factory::setContainer()`

This lets us set expectations on the mock `Http` while the tool classes use the real `HttpDecorator` code path.

## Integration Tests

Integration tests run against a real Joomla installation via the Web Services API.

### Setup

1. Copy `tests/integration.config.php.dist` to `tests/integration.config.php`
2. Fill in your Joomla site URL and API token:

```php
return [
    'JOOMLA_BASE_URL' => 'https://your-joomla-site.com',
    'BEARER_TOKEN'    => 'your-base64-encoded-api-token',
];
```

The `integration.config.php` file is gitignored and must not be committed.

### What They Test

- **Articles** — List articles, full CRUD lifecycle with cleanup
- **Tags** — List tags, full CRUD lifecycle with cleanup
- **Users** — List users (read-only)
- **Config** — Read application and component configuration

### Cleanup

Integration tests track the IDs of any resources they create and delete them in `tearDown()`, even if the test fails. Created resources are trashed first (state = -2) then deleted.

### Skipping

If `tests/integration.config.php` does not exist or is incomplete, integration tests are automatically skipped.

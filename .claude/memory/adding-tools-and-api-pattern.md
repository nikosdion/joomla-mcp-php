# Adding MCP tools and the Joomla API integration pattern

Moved from `AGENTS.md`. The server lives under `src/Server/`; tools are auto-discovered.

### Adding New MCP Tools

1. Create a class under `src/Server/` (mirroring Joomla component structure, e.g., `Content/Articles.php`)
2. Add `#[McpTool]` attributes to public methods
3. Use the utility traits for common functionality
4. Access the DI container via `Factory::getContainer()` to get `http` and `log` services
5. Tools are auto-discovered — no registration needed

### Joomla API Integration Pattern

All Joomla API calls follow this pattern:
```php
$http = Factory::getContainer()->get('http');
$uri = $http->getUri('v1/content/articles');  // builds full API URL
$response = $http->get($uri);
$this->handlePossibleJoomlaAPIError($response);
return $this->getDataFromResponse($response, 'articles');
```

The API uses JSON:API format (`application/vnd.api+json`).

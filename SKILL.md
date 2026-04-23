# SKILL.md

This file documents the MCP4Joomla MCP server for use by agentic AI systems.

## What is MCP4Joomla?

MCP4Joomla is a Model Context Protocol (MCP) server that provides comprehensive programmatic access to Joomla 5+ installations via the Joomla Web Services API. It exposes 249 tools across 22 categories for managing all aspects of a Joomla site, from content creation to extension management.

## When to Use This MCP Server

Use MCP4Joomla when you need to:

- Create, read, update, or delete Joomla articles and other content
- Manage Joomla site configuration and structure (menus, modules, categories)
- Administer users, groups, and access levels
- Install, update, or configure extensions (plugins, modules, templates)
- Manage media files and assets
- Handle privacy requests and GDPR compliance
- Monitor and apply Joomla core updates
- Configure language settings and translations
- Manage tags, custom fields, banners, newsfeeds, and redirects
- Integrate with Panopticon for site monitoring and backups
- Manage Akeeba Ticket System (ATS) support tickets, replies, and attachments

**When NOT to use this**: For content research, web scraping, or accessing public Joomla content (use standard HTTP requests instead). This server requires authenticated API access to a Joomla backend.

## Prerequisites

Before using MCP4Joomla, ensure:

1. **Joomla 5+ installed** — The target site must be running Joomla 5.0 or later
2. **Web Services API enabled** — Joomla's API must be accessible
3. **Super User API token** — You need a base64-encoded API token from a Joomla Super User account
4. **Network access** — The MCP server must be able to reach the Joomla site's API endpoints

## Configuration

MCP4Joomla requires two environment variables:

```bash
# Base URL of the Joomla site (no trailing /api or /index.php)
export JOOMLA_BASE_URL="https://example.com"

# Base64-encoded Joomla Super User API token
export BEARER_TOKEN="your-base64-encoded-token"
```

### How to Get the API Token

1. Log into Joomla backend as a Super User
2. Go to **System → Manage → API Tokens**
3. Create a new token or view an existing one
4. The token shown is already base64-encoded — use it directly as `BEARER_TOKEN`

## Running the MCP Server

### Standard Mode

```bash
php mcp4joomla.php server
```

### With Debug Logging

```bash
php mcp4joomla.php server --debug
```

### Custom Log Location

```bash
php mcp4joomla.php server --log=/path/to/custom.log
```

## Available Tools

MCP4Joomla auto-discovers tools from PHP classes with `#[McpTool]` attributes. Tools are organized by Joomla component:

### Content Management
- `content_articles_*` — Create, read, update, delete, list articles
- `content_categories_*` — Manage article categories
- `fields_*` — Custom fields and field groups
- `tags_*` — Tag management

### Site Structure
- `menus_*` — Site and admin menus, menu items
- `modules_*` — Site and admin module management
- `templates_*` — Template style configuration

### Users & Access
- `users_*` — User CRUD operations
- `users_groups_*` — User group management
- `users_levels_*` — Access level configuration

### Extensions
- `installer_extensions_*` — Install, update, uninstall extensions
- `plugins_*` — Plugin management and configuration
- `joomla_update_*` — Joomla core updates

### Media & Assets
- `media_*` — File and folder operations in media manager

### Privacy & Compliance
- `privacy_requests_*` — GDPR privacy requests
- `privacy_consents_*` — User consent management

### Advanced Features
- `config_*` — Site configuration
- `panopticon_*` — Integration with Panopticon monitoring (backups, updates, overrides)
- `content_history_*` — Content versioning and history
- `redirects_*` — URL redirect management
- `languages_*` — Language packages and overrides

### Support (Akeeba Ticket System)

> Requires the [Akeeba Ticket System](https://www.akeeba.com/products/akeeba-ticket-system.html) (ATS) Joomla extension. Attachment download and manager notes require **ATS Pro**.

- `tickets_categories_*` — List and read ATS ticket categories
- `tickets_tickets_*` — Full CRUD for support tickets (list, read, create, update, delete); `tickets_tickets_read` accepts `includePosts: true` to embed the conversation in one call
- `tickets_posts_*` — Read and manage ticket replies (posts); create a reply with `tickets_posts_create`
- `tickets_attachments_*` — List, read metadata, and download attachments; download returns images as `ImageContent` (ready for vision models), text/code files as `TextContent`, and ZIP archives expanded into individual file contents
- `tickets_notes_*` — Manager-only internal notes on tickets (ATS Pro)

### Other Components
- `contact_*` — Contact management
- `banners_*` — Banner advertising
- `newsfeeds_*` — RSS/Atom newsfeeds
- `messages_*` — Private messages

## Usage Patterns

### Creating Content

Tools accept **Markdown for text fields**, which is automatically converted to HTML:

```json
{
  "title": "My New Article",
  "catId": 2,
  "introText": "# Welcome\n\nThis is **markdown** content",
  "fullText": "More content here...",
  "state": 1
}
```

### Listing with Filters

Most list operations support filtering:

```json
{
  "filterCategory": 2,
  "filterState": 1,
  "filterFeatured": 1,
  "filterLanguage": "en-GB"
}
```

### Updating Resources

Update operations use **read-merge-write** pattern — only provide fields you want to change:

```json
{
  "articleId": 42,
  "state": 2,
  "featured": true
}
```

### State Values

Joomla uses numeric states:
- `1` — Published
- `0` — Unpublished
- `2` — Archived
- `-2` — Trashed

**Important**: Items must be trashed (`state: -2`) before permanent deletion.

### Language Codes

- `"*"` — All languages
- `"en-GB"` — British English
- `"en-US"` — American English
- `"fr-FR"` — French (France)

## Common Workflows

### Publish a New Article

```javascript
// 1. List categories to find the right catId
const categories = await use_mcp_tool({
  server_name: "mcp4joomla",
  tool_name: "content_categories_list"
});

// 2. Create the article
const article = await use_mcp_tool({
  server_name: "mcp4joomla",
  tool_name: "content_articles_create",
  arguments: {
    title: "Getting Started with Joomla",
    catId: 2,
    introText: "Learn the basics of Joomla CMS",
    fullText: "## Installation\n\nFollow these steps...",
    state: 1,
    featured: true
  }
});
```

### Update Site Configuration

```javascript
const config = await use_mcp_tool({
  server_name: "mcp4joomla",
  tool_name: "config_update",
  arguments: {
    sitename: "My Awesome Site",
    MetaDesc: "A description for SEO",
    offline: false
  }
});
```

### Install an Extension

```javascript
const result = await use_mcp_tool({
  server_name: "mcp4joomla",
  tool_name: "installer_extensions_install",
  arguments: {
    url: "https://example.com/extension.zip"
  }
});
```

### Read an ATS Support Ticket with Its Conversation

```javascript
// Read ticket 42 including all reply posts in a single call
const ticket = await use_mcp_tool({
  server_name: "mcp4joomla",
  tool_name: "tickets_tickets_read",
  arguments: { id: 42, includePosts: true }
});

// Download an attachment and let a vision model inspect it
const image = await use_mcp_tool({
  server_name: "mcp4joomla",
  tool_name: "tickets_attachments_download",
  arguments: { id: 7 }
  // Returns ImageContent for images, TextContent for text/code,
  // or an array of Content objects when the attachment is a ZIP
});
```

### Manage User Access

```javascript
// Create a new user
const user = await use_mcp_tool({
  server_name: "mcp4joomla",
  tool_name: "users_create",
  arguments: {
    name: "John Doe",
    username: "johndoe",
    email: "john@example.com",
    password: "SecurePass123!",
    groups: [2] // Registered group
  }
});
```

## Tool Annotations

Tools are annotated with hints about their behavior:

- **readOnlyHint** — Safe to call repeatedly, no modifications
- **idempotentHint** — Can be called multiple times with same result
- **destructiveHint** — Permanently deletes data (requires confirmation)

## Error Handling

The server automatically:
- Parses Joomla API error responses
- Throws descriptive exceptions with error details
- Logs all requests and responses when `--debug` is enabled

Common errors:
- `401 Unauthorized` — Invalid `BEARER_TOKEN`
- `403 Forbidden` — Insufficient permissions
- `404 Not Found` — Resource doesn't exist
- `422 Unprocessable Entity` — Validation failed

## Best Practices

1. **Always list before create** — Check if resources exist to avoid duplicates
2. **Read before update** — Verify current state before modifications
3. **Trash before delete** — Set `state: -2` before permanent deletion
4. **Use filters** — Narrow results with category, state, language filters
5. **Handle errors gracefully** — Check for API errors and provide fallbacks
6. **Enable debug logging** — Use `--debug` when troubleshooting
7. **Respect state transitions** — Follow Joomla's publish/unpublish/archive/trash workflow

## Limitations

- **Requires Super User token** — No granular permission support
- **No batch operations** — Each item requires a separate API call
- **No transaction support** — Operations are not atomic
- **Media upload limits** — Respects Joomla's PHP upload_max_filesize
- **Rate limiting** — Subject to web server and PHP-FPM limits
- **ATS requires separate extension** — `tickets_*` tools only work if Akeeba Ticket System is installed; use `--no-ats` to exclude them if not applicable
- **ATS attachment upload not supported** — Multipart file upload is impractical for AI agents; use the Joomla backend to upload attachments

## Troubleshooting

### Connection Issues
- Verify `JOOMLA_BASE_URL` is accessible
- Check firewall and network connectivity
- Ensure API endpoints are not blocked by .htaccess

### Authentication Failures
- Confirm token is base64-encoded
- Verify token belongs to a Super User account
- Check token hasn't expired (if expiration is set)

### Unexpected Responses
- Enable `--debug` logging
- Check `log/debug.log` for full request/response details
- Verify Joomla API is functioning (test with curl)

## Additional Resources

- **MCP Specification**: https://modelcontextprotocol.io/
- **Joomla Web Services API**: https://docs.joomla.org/J4.x:Joomla_Core_APIs
- **Source Code**: https://github.com/nikosdion/joomla-mcp-php (if applicable)

## Integration Example

### Claude Desktop Configuration

Add to `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "mcp4joomla": {
      "command": "php",
      "args": ["/path/to/mcp4joomla.php", "server"],
      "env": {
        "JOOMLA_BASE_URL": "https://example.com",
        "BEARER_TOKEN": "your-base64-token"
      }
    }
  }
}
```

### Cline/Agentic AI Configuration

Set environment variables before launching:

```bash
export JOOMLA_BASE_URL="https://example.com"
export BEARER_TOKEN="your-base64-token"
php mcp4joomla.php server
```

Then connect to the stdio transport in your MCP client configuration.

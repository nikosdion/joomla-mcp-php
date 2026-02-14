# MCP4Joomla

An MCP server for Joomla 5 and later, written in PHP.

## What is this?

An [MCP (Model Context Protocol)](https://en.wikipedia.org/wiki/Model_Context_Protocol) server allows a large language model (LLM, also known as “AI”) to interact with external software.

This MCP server is an implementation of the MCP protocol for Joomla 5 and later. It sits between your LLM and your Joomla installation, allowing the LLM to interact with your Joomla installation. The ultimate goal is to make Joomla a first-class citizen in LLMs, allowing you to build and manage your Joomla sites using LLMs without having to write any code or take any manual steps.

## Requirements

* PHP 8.1 or later; PHP 8.4 recommended.
* A Joomla 5.2 or later installation, including 6.x
* A Joomla API token for a Super User account.

## Usage

Clone this repository.

Go into the repository and run `composer install` to install the dependencies.

Edit your MCP configuration file and add this MCP server (the object you see under the `MCP4Joomla` key in the sample MCP config file below):

```json
{
	"mcpServers": {
		"MCP4Joomla": {
			"command": "/usr/bin/php",
			"args": [
				"/path/to/joomla-mcp-php/mcp4joomla.php",
                "server"
			],
			"env": {
				"JOOMLA_BASE_URL": "https://www.example.com",
				"BEARER_TOKEN": "your_joomla_api_token"
			}
		}
	}
}
```

Where:

* `/usr/bin/php` is the path to your PHP CLI executable.
* `/path/to/joomla-mcp-php/mcp4joomla.php` is the path to the `mcp4joomla.php` file.
* `https://www.example.com` is the base URL of your Joomla installation, _without_ the trailing `/api`, _without_ the trailing `/index.php`.
* `your_joomla_api_token` is the Joomla API token for any active Super User account of your Joomla installation, something that looks like `c2hhMjU2OjI5NDoxNmI5NzU3NWY1YTFhYTBmYWViNjUyMTRlZThmYzc1NTBiYWNkNmM4MjQ5N2ExYzllM2FjY2I5ODYxZjMxOGMx`.

### Command line options

The `server` command accepts the following optional arguments:

| Option | Description |
|--------|-------------|
| `--debug` / `-d` | Enable debug mode (verbose logging of all parameters and API requests/responses). |
| `--log=PATH` / `-l PATH` | Specify a custom log file path. |
| `--no-panopticon` | Exclude all Panopticon tools. Use this if your Joomla site does not have the Panopticon Connector component installed. |
| `--no-schema` | Strip `#[Schema]` descriptions and constraints from tool input schemas, keeping only parameter types and defaults. Reduces context size for LLMs with limited context windows. |
| `--categories=LIST` / `-c LIST` | Comma-separated list of category names to include (case-insensitive). Only tools from these categories will be exposed. |
| `--tools=LIST` / `-t LIST` | Comma-separated list of tool names to include. Only these exact tools will be exposed. |
| `--non-destructive` / `-r` | Only expose read-only tools (no create, update, or delete). |

**Precedence:** `--non-destructive` is applied last, after all other filters. `--tools` overrides `--categories`, which overrides `--no-panopticon`. If `--tools` is given, only those exact tools are exposed. If `--categories` is given, only tools from those categories are discovered. `--no-panopticon` excludes the Panopticon category from discovery.

For example, to start the server with only the Content and Tags categories and debug logging:

```json
{
	"mcpServers": {
		"MCP4Joomla": {
			"command": "/usr/bin/php",
			"args": [
				"/path/to/joomla-mcp-php/mcp4joomla.php",
				"server",
				"--categories=Content,Tags",
				"--debug"
			],
			"env": {
				"JOOMLA_BASE_URL": "https://www.example.com",
				"BEARER_TOKEN": "your_joomla_api_token"
			}
		}
	}
}
```

### Listing available tools

To see all available tool categories and their tools, run:

```bash
php mcp4joomla.php list-tools
```

This command does not require the `JOOMLA_BASE_URL` or `BEARER_TOKEN` environment variables. It prints each category name followed by its tools, indented with two spaces.

### Using with small context window models

> [!IMPORTANT]
> If you are using LM Studio you **MUST** set the **Context Overflow** to a "Rolling Window" instead of "Truncate Middle". Failure to do so will result in the LLM going into an infinite loop calling the same MCP tool over and over.

The MCP server provides 212 tools across 21 categories. The JSON schema for these tools is quite large. LLMs with small context windows may not be able to use all tools, or may fail as their context is overrun. You can reduce context usage in several ways:

* **`--categories`** — Only expose the categories you need (e.g. `--categories=Content,Tags`).
* **`--tools`** — Only expose specific tools by name (e.g. `--tools=content_articles_list,tags_list`).
* **`--no-panopticon`** — Exclude all Panopticon tools if your site doesn't use the Panopticon Connector.
* **`--non-destructive`** — Only expose read-only tools, preventing any data modification. Useful as a safety measure.
* **`--no-schema`** — Strip parameter descriptions and constraints from the tool schemas, significantly reducing context size.

Furthermore, Joomla produces fairly large JSON responses, which can easily overrun the context window of the LLM. This is especially true if you are asking the LLM to work on large datasets, like dozens or hundreds of articles.

If you are using LM Studio I strongly advise you to set the **Context Overflow** option (right hand menu, Model tab, Settings, click on All to expose it) to **Rolling Window** instead of the default "Truncate Middle". Moreover, you should only choose the MCP tools you need to use for your request (again, right hand menu, Program tab, click on the Tools of your MCP definition and uncheck the ones you don't need).

## Using the PHAR archive

Instead of cloning the repository, you can use a pre-built PHAR archive for easier deployment.

### Building the PHAR

```bash
composer compile
```

This creates `build/mcp4joomla.phar`, a self-extracting PHAR archive (~4–8 MB).

### MCP configuration with the PHAR

```json
{
	"mcpServers": {
		"MCP4Joomla": {
			"command": "/usr/bin/php",
			"args": [
				"/path/to/mcp4joomla.phar",
				"server"
			],
			"env": {
				"JOOMLA_BASE_URL": "https://www.example.com",
				"BEARER_TOKEN": "your_joomla_api_token"
			}
		}
	}
}
```

All command line options (`--debug`, `--categories`, `--non-destructive`, etc.) work the same as with the source installation.

### Log file location

When running from the PHAR, logs are written to `log/debug-DATE.log` in your **current working directory** (instead of relative to the project source). You can override this with `--log=/path/to/file.log`.

### Extraction cache

On first run, the PHAR extracts its contents to a temporary directory under `sys_get_temp_dir()` (e.g. `/tmp/mcp4joomla-<hash>`). Subsequent runs reuse the cached extraction as long as the PHAR file hasn't changed. To force a fresh extraction, delete the cached directory.

## Using with Docker

You can run MCP4Joomla in a Docker container without installing PHP or Composer locally.

### Building the image

```bash
docker build -t mcp4joomla .
```

### MCP configuration with Docker

```json
{
	"mcpServers": {
		"MCP4Joomla": {
			"command": "docker",
			"args": [
				"run", "-i", "--rm",
				"-e", "JOOMLA_BASE_URL=https://www.example.com",
				"-e", "BEARER_TOKEN=your_joomla_api_token",
				"mcp4joomla",
				"server"
			]
		}
	}
}
```

The `-i` flag is required for stdio transport. `--rm` removes the container when it exits.

All command line options (`--debug`, `--categories`, `--non-destructive`, etc.) can be appended after `server`:

```json
"args": ["run", "-i", "--rm", "-e", "JOOMLA_BASE_URL=...", "-e", "BEARER_TOKEN=...", "mcp4joomla", "server", "--categories=Content,Tags", "--debug"]
```

### Listing tools

The `list-tools` command does not require environment variables:

```bash
docker run --rm mcp4joomla list-tools
```

### Persisting logs

By default, logs are written inside the container and lost when it exits. To persist them, mount a volume:

```json
"args": ["run", "-i", "--rm", "-v", "/path/to/logs:/app/log", "-e", "JOOMLA_BASE_URL=...", "-e", "BEARER_TOKEN=...", "mcp4joomla", "server"]
```

To disable logging entirely, pass `--log=/dev/null`.

## Extending with custom tools

You can add your own MCP tools by placing PHP files in the `user_code/` directory. Any class with `#[McpTool]` attributes will be auto-discovered alongside the built-in tools — no Composer changes or registration needed.

### Creating a custom tool

1. Create a PHP file in the `user_code/` directory (subdirectories are supported).
2. Add `declare(strict_types=1)` and any `use` statements you need.
3. Create a class with one or more public methods annotated with `#[McpTool]`.
4. Access the DI container via `Factory::getContainer()` for services like `http`, `log`, `env`, and `input`.
5. Optionally use the utility traits (`AutoLoggingTrait`, `HandleJoomlaAPIErrorTrait`, etc.).

### Example tool

```php
<?php
declare(strict_types=1);

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;

class MyCustomTool
{
    use AutoLoggingTrait;

    #[McpTool(
        name: 'my_custom_tool',
        description: 'Does something useful',
        annotations: new ToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
        ),
    )]
    public function doSomething(string $param = 'default'): string
    {
        $this->autologMCPTool();

        $log = Factory::getContainer()->get('log');
        $log->debug("My custom tool called with: {$param}");

        return "Result: {$param}";
    }
}
```

### Available DI container services

| Service | Type | Description |
|---------|------|-------------|
| `http` | `HttpDecorator` | Joomla HTTP client with automatic auth headers. Use `$http->getUri('v1/...')` for API URLs. |
| `log` | `LoggerInterface` | Monolog logger for debug/info/error messages. |
| `env` | `array` | Environment variables (`JOOMLA_BASE_URL`, `BEARER_TOKEN`). |
| `input` | `OptResult` | Parsed CLI input (command line options). |

### Available utility traits

| Trait | Description |
|-------|-------------|
| `AutoLoggingTrait` | Call `$this->autologMCPTool()` to automatically log tool invocations with arguments. |
| `HandleJoomlaAPIErrorTrait` | Parse Joomla API error responses and throw exceptions. |
| `GetDataFromResponseTrait` | Extract and validate JSON:API response data. |
| `ArticleTextTrait` | Convert Markdown to HTML via CommonMark. |
| `TitleToAliasTrait` | Generate URL-safe slugs from titles. |

### User code filtering

User code tools are always discovered regardless of `--categories` and `--no-panopticon`, but they **are** subject to `--tools`, `--non-destructive`, and `--no-schema` filtering.

The `list-tools` command shows user code tools under a "UserCode" heading.

### Docker usage

Mount your `user_code/` directory into the container:

```json
"args": ["run", "-i", "--rm", "-v", "/path/to/user_code:/app/user_code", "-e", "JOOMLA_BASE_URL=...", "-e", "BEARER_TOKEN=...", "mcp4joomla", "server"]
```

### PHAR usage

Place the `user_code/` directory in the same directory where you run the PHAR file (i.e. your current working directory).

## Provided MCP tools

For a full list of provided MCP tools, and the up-to-date roadmap please refer to the [`http/README.md`](http/README.md) file.

## Logging

By default, MCP4Joomla logs to the file `log/debug-DATE.log` file relative to its path, where `DATE` is the current date in YYYY-MM-DD format. You can change this by specifying the `--log=/path/to/your/log_file.log` argument when running the MCP server.

By default, MCP4Joomla logs only information messages (e.g. which tool was called) and errors. To log all parameters sent to it by the LLM, and the exact request data sent and response data received, you need to enable debug mode by adding the `--debug` argument when running the MCP server.

> [!IMPORTANT]  
> Issues cannot be responded to without a log file with the debug mode enabled. You must replace your Joomla! API token in the log file with a placeholder value before submitting an issue.

## Security and safety

**With great power comes great responsibility.**

You can use this MCP server to run any command available in the core Joomla API (`webservices`) plugins. This includes commands which retrieve information from your site, as well as commands _which allow you to modify your site_.

> [!CAUTION]
> MCP servers like MCP4Joomla are extremely powerful and can do serious damage to your Joomla installation if an LLM makes a mistake. Bear in mind that LLMs are experimental technology which can and does make serious mistakes, _quite often_. Use this MCP server at your own risk. I very strongly recommend you to validate the commands your LLM says it will use. Please refer to the documentation of your LLM user interface for more information on how to do this.

As far as security goes, this MCP server requires you to configure it with a Joomla API token for a Super User account, and your site's URL. This information should be kept secret at all times.

A common threat model for MCP servers is that they are exposed to the internet, so that LLMs can be used to access them. This is not a problem for MCP4Joomla, as it is running on your local machine, and is not exposed to the internet. While this is limiting for some scenarios, it is not a problem for most LLMs, and it offers a great deal of security by virtue of the fact that the MCP server can not be exposed to the internet.

## Kudos

This project could not have been possible without the following resources:

* [PHP MCP project](https://github.com/php-mcp/server)
* [Third party Joomla API documentation](https://www.postman.com/mralexandrelise/alex-api-public-workspace) (because digging through the `webservices` plugins code is NOT a great way to start, but it's definitely required to get the details not fully documented in this link).

## Copyright statement

MCP4Joomla – An MCP server for Joomla 5 and later, written in PHP.
Copyright (C) 2025–2026  Nicholas K. Dionysopoulos

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published
by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

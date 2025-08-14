# MCP4Joomla

An MCP server for Joomla 5 and later, written in PHP.

🚧 WORK IN PROGRESS 🚧

THIS PROJECT IS STILL IN DEVELOPMENT AND IS NOT READY FOR PRODUCTION USE.

## What is this?

An [MCP (Model Context Protocol)](https://en.wikipedia.org/wiki/Model_Context_Protocol) server allows a large language model (LLM, also known as “AI”) to interact with external software.

This MCP server is an implementation of the MCP protocol for Joomla 5 and later. It sits between your LLM and your Joomla installation, allowing the LLM to interact with your Joomla installation. The ultimate goal is to make Joomla a first-class citizen in LLMs, allowing you to build and manage your Joomla sites using LLMs without having to write any code or take any manual steps.

## Requirements

* PHP 8.1 or later.
* A Joomla 5.0 or later installation (it _might_ work on 4.4, but I haven't tested it).
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
				"-f",
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

You can add the following **optional** arguments to the `args` array:
* `--debug` to enable debug mode.
* `--log=/path/to/your/log_file.log` to specify the path to your log file.

## Provided MCP tools

For a full list of provided MCP tools, and the up-to-date roadmap please refer to the [`http/README.md`](http/README.md) file.

## Logging

By default, MCP4Joomla logs to the file `log/debug-DATE.log` file relative to its path, where `DATE` is the current date in YYYY-MM-DD format. You can change this by specifying the `--log=/path/to/your/log_file.log` argument when running the MCP server.

By default, MCP4Joomla logs only information messages (e.g. which tool was called) and errors. To log all parameters sent to it by the LLM, and the exact request data sent and response data received, you need to enable debug mode by adding the `--debug` argument when running the MCP server.

> [!IMPORTANT]  
> Issues cannot be responded to without a log file with the debug mode enabled. You must replace your Joomla! API token in the log file with a placeholder value before submitting an issue.

## Roadmap

- Implement a `--non-destructive` switch which turns off destructive commands (anything that can modify or delete data, change configuration, install / update extensions and the core, etc.).
- Implement support for all `webservices` plugins provided by Joomla in Joomla 5.4 and 6.0.
- Implement support for extensibility by scanning a `user_code` directory, and documenting how MCP element classes can be created. Perhaps include a demo MCP element class.
- Create a PHAR archive to package the MCP server and all its dependencies for easier deployment.
- Create a Docker image to make deployment easier.

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
Copyright (C) 2025  Nicholas K. Dionysopoulos

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

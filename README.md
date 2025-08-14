# MCP4Joomla

An MCP server for Joomla 5 and later, written in PHP.

This project aims to be the most complete and extensible MCP server for Joomla 5 and later. It sits between your LLM (AI) and your Joomla installation, allowing the LLM to interact with your Joomla installation. The ultimate goal is to make Joomla a first-class citizen in LLMs, allowing you to build and manage your Joomla sites using LLMs without having to write any code or take any manual steps.

You can use this MCP server to run any command available in the core Joomla API (`webservices`) plugins. This includes commands which retrieve information from your site, but also commands which allow you to modify your site.

> ⚠️ **WARNING**: MCP servers like MCP4Joomla are extremely powerful and can do serious damage to your Joomla installation if an LLM makes a mistake. Bear in mind that LLMs are experimental technology which can and does make serious mistakes. Use this MCP server at your own risk. I very strongly recommend you to validate the commands before running them.

## WORK IN PROGRESS

This project is still in development and considered experimental.

Roadmap:
- Implement support for all `webservices` plugins provided by Joomla in Joomla 5.4 and 6.0.
- Implement support for extensibility.
- Create PHAR archives to package the MCP server and all its dependencies for easier deployment.
- Create a Docker image to make deployment easier.

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

For a list of provided tools, please refer to the [`http/README.md`](http/README.md) file.

## Kudos

This project could not have been possible without the following resources:

* [PHP MCP project](https://github.com/php-mcp/server)
* [Joomla API](https://www.postman.com/mralexandrelise/alex-api-public-workspace) (third party documentation, far more detailed the official Joomla documentation could ever hope to be).

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

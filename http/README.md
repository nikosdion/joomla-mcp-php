# Joomla API for phpStorm

This directory contains Joomla API sample requests for PhpStorm's HTTP Client.

The files in this directory are NOT used by the MCP server. They are only for reference. This is what we used to test the API calls while we were building the respective MCP server's element.

The inspiration for this section came from the [unofficial Joomla API Postman collection](https://www.postman.com/mralexandrelise/alex-api-public-workspace).

The ordering of the files is based on the priority I have set up for integrating these API calls with the MCP server.

## Content

API requests to the `com_content`, `com_categories` and `com_fields` components relevant to content articles.

Depends on the **Web Services - Content** plugin.

* `content_articles.http` Working with com_content articles. Implemented by `\Dionysopoulos\Mcp4Joomla\Server\Content\Articles` in the following MCP tools:
  * `content_articles_create` Create a new article.
  * `content_articles_modify`Modify an existing article.
  * `content_articles_list` List existing articles.
  * `content_articles_get` Retrieve the information of the specified article.
  * `content_articles_delete` Permanently deletes an article whose state is trashed (-2).
* `content_categories.http` Working with com_content categories. **Pending.**
* `content_article_fields.http` Working with com_content article fields. **Pending.**
* `content_article_fields_groups.http` Working with com_content article field groups. **Pending.**
* `content_categories_fields.http` Working with com_content category fields. **Pending.**

## Menus

API requests to the `com_menus` component relevant to menus.

Depends on the **Web Services - Menus** plugin.

[//]: # (TODO)

## Languages

API requests to the `com_languages` component relevant to languages.

Depends on the **Web Services - Languages** plugin.

[//]: # (TODO)

## Tags

API requests to the `com_tags` component relevant to tags.

Depends on the **Web Services - Tags** plugin.

[//]: # (TODO)

## Modules

API requests to the `com_modules` component relevant to modules.

Depends on the **Web Services - Modules** plugin.

[//]: # (TODO)

## Users

API requests to the `com_users` component relevant to users.

Depends on the **Web Services - Users** plugin.

[//]: # (TODO)

## Media

API requests to the `com_media` component relevant to media files.

Depends on the **Web Services - Media** plugin.

[//]: # (TODO)

## Templates

API requests to the `com_templates` component relevant to site templates.

Depends on the **Web Services - Templates** plugin.

[//]: # (TODO)

## Contacts

API requests to the `com_contact` component relevant to contact items.

Depends on the **Web Services - Contacts** plugin.

[//]: # (TODO)

## Global and Component Configuration

API requests to the `com_config` component relevant to global Joomla and component configuration.

Depends on the **Web Services - Configuration** plugin.

[//]: # (TODO)

## Banners

API requests to the `com_banners` component relevant to banner items.

Depends on the **Web Services - Banners** plugin.

[//]: # (TODO)

## Plugins

API requests to the `com_plugins` component relevant to plugins.

Depends on the **Web Services - Plugins** plugin.

[//]: # (TODO)

## Installer

API requests to the `com_installer` component relevant to the Joomla extensions installer.

Depends on the **Web Services - Installer** plugin.

[//]: # (TODO)

## Messages

API requests to the `com_messages` component relevant to mass messages.

Depends on the **Web Services - Messages** plugin.

[//]: # (TODO)

## Newsfeeds

API requests to the `com_newsfeeds` component relevant to RSS and Atom newsfeed items.

Depends on the **Web Services - Newsfeeds** plugin.

[//]: # (TODO)

## Privacy

API requests to the `com_privacy` component relevant to the Joomla privacy requests management.

Depends on the **Web Services - Privacy** plugin.

[//]: # (TODO)

## Redirects

API requests to the `com_redirects` component relevant to URL redirects.

Depends on the **Web Services - Redirects** plugin.

[//]: # (TODO)

## Joomla Update

API requests to the `com_joomlaupdate` component relevant to Joomla core updates.

Depends on the **Web Services - Joomla Update** plugin.

[//]: # (TODO)

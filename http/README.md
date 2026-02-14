# Joomla API for PhpStorm

This directory contains Joomla API sample requests for PhpStorm's HTTP Client.

The files in this directory are NOT used by the MCP server. They are only for reference. This is what we used to test the API calls while we were building the respective MCP server tools.

The inspiration for this section came from the [unofficial Joomla API Postman collection](https://www.postman.com/mralexandrelise/alex-api-public-workspace).

## Setup

Create an `http-client.env.json` file in this directory (or in the project root) with your Joomla site's base URL and API token:

```json
{
  "dev": {
    "baseUrl": "https://example.com",
    "token": "your-joomla-api-token-here"
  }
}
```

## Content

API requests to the `com_content` and `com_categories` components relevant to content articles and categories.

Depends on the **Web Services - Content** plugin.

* `content_articles.http` Working with com_content articles. Implemented by `Server\Content\Articles`:
  * `content_articles_list` List existing articles.
  * `content_articles_read` Retrieve the information of the specified article.
  * `content_articles_create` Create a new article.
  * `content_articles_update` Update an existing article.
  * `content_articles_delete` Permanently deletes an article (must be trashed first).
* `content_categories.http` Working with com_content categories. Implemented by `Server\Content\Categories`:
  * `content_categories_list` List existing content categories.
  * `content_categories_read` Retrieve the information of the specified content category.
  * `content_categories_create` Create a new content category.
  * `content_categories_update` Update an existing content category.
  * `content_categories_delete` Permanently deletes a content category (must be trashed first).

## Banners

API requests to the `com_banners` component.

Depends on the **Web Services - Banners** plugin.

* `banners.http` Working with banners. Implemented by `Server\Banners\Banners`:
  * `banners_list` List existing banners.
  * `banners_read` Retrieve the information of the specified banner.
  * `banners_create` Create a new banner.
  * `banners_update` Update an existing banner.
  * `banners_delete` Permanently deletes a banner (must be trashed first).
* `banners_categories.http` Working with banner categories. Implemented by `Server\Banners\Categories`:
  * `banners_categories_list` List existing banner categories.
  * `banners_categories_read` Retrieve the information of the specified banner category.
  * `banners_categories_create` Create a new banner category.
  * `banners_categories_update` Update an existing banner category.
  * `banners_categories_delete` Permanently deletes a banner category (must be trashed first).
* `banners_clients.http` Working with banner clients. Implemented by `Server\Banners\Clients`:
  * `banners_clients_list` List existing banner clients.
  * `banners_clients_read` Retrieve the information of the specified banner client.
  * `banners_clients_create` Create a new banner client.
  * `banners_clients_update` Update an existing banner client.
  * `banners_clients_delete` Permanently deletes a banner client (must be trashed first).

## Contacts

API requests to the `com_contact` component.

Depends on the **Web Services - Contacts** plugin.

* `contacts.http` Working with contacts. Implemented by `Server\Contact\Contacts`:
  * `contact_list` List existing contacts.
  * `contact_read` Retrieve the information of the specified contact.
  * `contact_create` Create a new contact.
  * `contact_update` Update an existing contact.
  * `contact_delete` Permanently deletes a contact (must be trashed first).
  * `contact_form_submit` Submit a contact form for a specific contact.
* `contacts_categories.http` Working with contact categories. Implemented by `Server\Contact\Categories`:
  * `contact_categories_list` List existing contact categories.
  * `contact_categories_read` Retrieve the information of the specified contact category.
  * `contact_categories_create` Create a new contact category.
  * `contact_categories_update` Update an existing contact category.
  * `contact_categories_delete` Permanently deletes a contact category (must be trashed first).

## Menus

API requests to the `com_menus` component.

Depends on the **Web Services - Menus** plugin.

* `menus_site.http` Working with site menus. Implemented by `Server\Menus\Menus`:
  * `menus_sitemenus_list` List existing site menus.
  * `menus_sitemenus_read` Retrieve the information of the specified site menu.
  * `menus_sitemenus_create` Create a new site menu.
  * `menus_sitemenus_update` Update an existing site menu.
  * `menus_sitemenus_delete` Permanently deletes a site menu.
* `menus_site_items.http` Working with site menu items. Implemented by `Server\Menus\MenuItems`:
  * `menus_siteitems_list` List existing site menu items.
  * `menus_siteitems_read` Retrieve the information of the specified site menu item.
  * `menus_siteitems_create` Create a new site menu item.
  * `menus_siteitems_update` Update an existing site menu item.
  * `menus_siteitems_delete` Permanently deletes a site menu item.
  * `menus_siteitems_types` List available menu item types for site menus.
* `menus_admin.http` Working with administrator menus. Implemented by `Server\Menus\AdminMenus`:
  * `menus_adminmenus_list` List existing administrator menus.
  * `menus_adminmenus_read` Retrieve the information of the specified administrator menu.
  * `menus_adminmenus_create` Create a new administrator menu.
  * `menus_adminmenus_update` Update an existing administrator menu.
  * `menus_adminmenus_delete` Permanently deletes an administrator menu.
* `menus_admin_items.http` Working with administrator menu items. Implemented by `Server\Menus\AdminMenuItems`:
  * `menus_adminitems_list` List existing administrator menu items.
  * `menus_adminitems_read` Retrieve the information of the specified administrator menu item.
  * `menus_adminitems_create` Create a new administrator menu item.
  * `menus_adminitems_update` Update an existing administrator menu item.
  * `menus_adminitems_delete` Permanently deletes an administrator menu item.
  * `menus_adminitems_types` List available menu item types for administrator menus.

## Modules

API requests to the `com_modules` component.

Depends on the **Web Services - Modules** plugin.

* `modules_site.http` Working with site modules. Implemented by `Server\Modules\SiteModules`:
  * `modules_site_list` List existing site modules.
  * `modules_site_read` Retrieve the information of the specified site module.
  * `modules_site_create` Create a new site module.
  * `modules_site_update` Update an existing site module.
  * `modules_site_delete` Permanently deletes a site module (must be trashed first).
  * `modules_site_types` List available site module types.
* `modules_admin.http` Working with administrator modules. Implemented by `Server\Modules\AdminModules`:
  * `modules_admin_list` List existing administrator modules.
  * `modules_admin_read` Retrieve the information of the specified administrator module.
  * `modules_admin_create` Create a new administrator module.
  * `modules_admin_update` Update an existing administrator module.
  * `modules_admin_delete` Permanently deletes an administrator module (must be trashed first).
  * `modules_admin_types` List available administrator module types.

## Users

API requests to the `com_users` component.

Depends on the **Web Services - Users** plugin.

* `users.http` Working with users, user groups, and viewing access levels. Implemented by `Server\Users\Users`, `Server\Users\Groups`, and `Server\Users\Levels`:
  * `users_list` List existing users.
  * `users_read` Retrieve the information of the specified user.
  * `users_create` Create a new user.
  * `users_update` Update an existing user.
  * `users_delete` Permanently delete a user.
  * `users_groups_list` List existing user groups.
  * `users_groups_read` Retrieve the information of the specified user group.
  * `users_groups_create` Create a new user group.
  * `users_groups_update` Update an existing user group.
  * `users_groups_delete` Permanently delete a user group.
  * `users_levels_list` List existing viewing access levels.
  * `users_levels_read` Retrieve the information of the specified viewing access level.
  * `users_levels_create` Create a new viewing access level.
  * `users_levels_update` Update an existing viewing access level.
  * `users_levels_delete` Permanently delete a viewing access level.

## Tags

API requests to the `com_tags` component.

Depends on the **Web Services - Tags** plugin.

* `tags.http` Working with tags. Implemented by `Server\Tags\Tags`:
  * `tags_list` List existing tags.
  * `tags_read` Retrieve the information of the specified tag.
  * `tags_create` Create a new tag.
  * `tags_update` Update an existing tag.
  * `tags_delete` Permanently deletes a tag (must be trashed first).

## Templates

API requests to the `com_templates` component.

Depends on the **Web Services - Templates** plugin.

* `templates.http` Working with site and administrator template styles. Implemented by `Server\Templates\SiteStyles` and `Server\Templates\AdminStyles`:
  * `templates_sitestyles_list` List existing site template styles.
  * `templates_sitestyles_read` Retrieve the information of the specified site template style.
  * `templates_sitestyles_create` Create a new site template style.
  * `templates_sitestyles_update` Update an existing site template style.
  * `templates_sitestyles_delete` Delete a site template style.
  * `templates_adminstyles_list` List existing administrator template styles.
  * `templates_adminstyles_read` Retrieve the information of the specified administrator template style.
  * `templates_adminstyles_create` Create a new administrator template style.
  * `templates_adminstyles_update` Update an existing administrator template style.
  * `templates_adminstyles_delete` Delete an administrator template style.

## Plugins

API requests to the `com_plugins` component.

Depends on the **Web Services - Plugins** plugin.

* `plugins.http` Working with plugins. Implemented by `Server\Plugins\Plugins`:
  * `plugins_list` List existing plugins.
  * `plugins_read` Retrieve the information of the specified plugin.
  * `plugins_update` Update an existing plugin.

## Languages

API requests to the `com_languages` component.

Depends on the **Web Services - Languages** plugin.

* `languages_content.http` Working with content languages. Implemented by `Server\Languages\ContentLanguages`:
  * `languages_content_list` List existing content languages.
  * `languages_content_read` Retrieve the information of the specified content language.
  * `languages_content_create` Create a new content language.
  * `languages_content_update` Update an existing content language.
  * `languages_content_delete` Delete a content language.
* `languages_overrides.http` Working with language overrides. Implemented by `Server\Languages\Overrides`:
  * `languages_overrides_site_list` List site language overrides for a given language.
  * `languages_overrides_site_read` Retrieve a specific site language override.
  * `languages_overrides_site_create` Create a new site language override.
  * `languages_overrides_site_update` Update an existing site language override.
  * `languages_overrides_site_delete` Delete a site language override.
  * `languages_overrides_admin_list` List administrator language overrides for a given language.
  * `languages_overrides_admin_read` Retrieve a specific administrator language override.
  * `languages_overrides_admin_create` Create a new administrator language override.
  * `languages_overrides_admin_update` Update an existing administrator language override.
  * `languages_overrides_admin_delete` Delete an administrator language override.
  * `languages_overrides_search` Search language strings for creating overrides.
  * `languages_overrides_cache_refresh` Refresh the language override search cache.
* `languages_packages.http` Working with language packages. Implemented by `Server\Languages\Packages`:
  * `languages_packages_list` List available language packages.
  * `languages_packages_install` Install a language package.

## Fields

API requests to the `com_fields` component for custom fields and field groups.

Depends on the **Web Services - Fields** plugin.

* `fields.http` Working with custom fields. Implemented by `Server\Fields\Fields`:
  * `fields_list` List custom fields for a given context.
  * `fields_read` Retrieve a specific custom field.
  * `fields_create` Create a new custom field.
  * `fields_update` Update an existing custom field.
  * `fields_delete` Delete a custom field.
* `fields_groups.http` Working with custom field groups. Implemented by `Server\Fields\FieldGroups`:
  * `fields_groups_list` List custom field groups for a given context.
  * `fields_groups_read` Retrieve a specific custom field group.
  * `fields_groups_create` Create a new custom field group.
  * `fields_groups_update` Update an existing custom field group.
  * `fields_groups_delete` Delete a custom field group.

## Media

API requests to the `com_media` component.

Depends on the **Web Services - Media** plugin.

* `media.http` Working with media files and adapters. Implemented by `Server\Media\Media`:
  * `media_adapters_list` List available media adapters.
  * `media_adapters_read` Retrieve a specific media adapter.
  * `media_files_list` List media files.
  * `media_files_read` Retrieve a specific media file.
  * `media_files_create` Create a new media file.
  * `media_files_update` Update an existing media file.
  * `media_files_delete` Delete a media file.

## Messages

API requests to the `com_messages` component.

Depends on the **Web Services - Messages** plugin.

* `messages.http` Working with private messages. Implemented by `Server\Messages\Messages`:
  * `messages_list` List existing private messages.
  * `messages_read` Retrieve the information of the specified private message.
  * `messages_create` Create a new private message.
  * `messages_update` Update an existing private message.
  * `messages_delete` Permanently deletes a private message (must be trashed first).

## Newsfeeds

API requests to the `com_newsfeeds` component.

Depends on the **Web Services - Newsfeeds** plugin.

* `newsfeeds.http` Working with newsfeeds. Implemented by `Server\Newsfeeds\Feeds`:
  * `newsfeeds_list` List existing newsfeeds.
  * `newsfeeds_read` Retrieve the information of the specified newsfeed.
  * `newsfeeds_create` Create a new newsfeed.
  * `newsfeeds_update` Update an existing newsfeed.
  * `newsfeeds_delete` Permanently deletes a newsfeed (must be trashed first).
* `newsfeeds_categories.http` Working with newsfeed categories. Implemented by `Server\Newsfeeds\Categories`:
  * `newsfeeds_categories_list` List existing newsfeed categories.
  * `newsfeeds_categories_read` Retrieve the information of the specified newsfeed category.
  * `newsfeeds_categories_create` Create a new newsfeed category.
  * `newsfeeds_categories_update` Update an existing newsfeed category.
  * `newsfeeds_categories_delete` Permanently deletes a newsfeed category (must be trashed first).

## Redirects

API requests to the `com_redirect` component.

Depends on the **Web Services - Redirects** plugin.

* `redirects.http` Working with URL redirects. Implemented by `Server\Redirects\Redirects`:
  * `redirects_list` List existing redirects.
  * `redirects_read` Retrieve the information of the specified redirect.
  * `redirects_create` Create a new redirect.
  * `redirects_update` Update an existing redirect.
  * `redirects_delete` Permanently deletes a redirect (must be trashed first).

## Privacy

API requests to the `com_privacy` component.

Depends on the **Web Services - Privacy** plugin.

* `privacy.http` Working with privacy requests and consents. Implemented by `Server\Privacy\Requests` and `Server\Privacy\Consents`:
  * `privacy_requests_list` List privacy requests.
  * `privacy_requests_read` Retrieve a specific privacy request.
  * `privacy_requests_create` Create a new privacy request.
  * `privacy_requests_export` Export privacy request data.
  * `privacy_consents_list` List privacy consents.
  * `privacy_consents_read` Retrieve a specific privacy consent.

## Content History

API requests for content version history, available across multiple components.

* `contenthistory.http` Working with content version history. Implemented by `Server\ContentHistory\ContentHistory`:
  * `contenthistory_list` List content history versions for a resource item.
  * `contenthistory_keep` Toggle the keep flag on a content history version.
  * `contenthistory_delete` Delete a content history version.

## Global and Component Configuration

API requests to the `com_config` component.

Depends on the **Web Services - Configuration** plugin.

* `config.http` Working with application and component configuration. Implemented by `Server\Config\Config`:
  * `config_application_read` Read the Joomla application configuration.
  * `config_application_update` Update the Joomla application configuration.
  * `config_component_read` Read the configuration of a Joomla component.
  * `config_component_update` Update the configuration of a Joomla component.

## Installer

API requests to the `com_installer` component.

Depends on the **Web Services - Installer** plugin.

* `installer.http` Working with installed extensions. Implemented by `Server\Installer\Extensions`:
  * `installer_extensions_list` List installed extensions.

## Joomla Update

API requests to the `com_joomlaupdate` component.

* `joomlaupdate.http` Working with Joomla core updates. Implemented by `Server\JoomlaUpdate\JoomlaUpdate`:
  * `joomlaupdate_healthcheck` Check if the Joomla update system is healthy.
  * `joomlaupdate_getupdate` Get available Joomla update information.
  * `joomlaupdate_prepare` Prepare the Joomla update for installation.
  * `joomlaupdate_finalize` Finalize the Joomla update installation.
  * `joomlaupdate_notify_success` Send notification that the Joomla update was successful.
  * `joomlaupdate_notify_failed` Send notification that the Joomla update failed.

## Panopticon Connector

API requests to the [Panopticon Connector](https://github.com/akeeba/panopticon_connector_j4) component for [Akeeba Panopticon](https://github.com/akeeba/panopticon). These endpoints require the Panopticon Connector for Joomla to be installed and configured on the target site.

* `panopticon.http` Working with all Panopticon Connector endpoints:
  * **Extensions** — Implemented by `Server\Panopticon\Extensions`:
    * `panopticon_extensions_list` List installed extensions.
    * `panopticon_extensions_read` Read details of a specific extension.
    * `panopticon_extensions_install` Install an extension from a URL.
  * **Core Update** — Implemented by `Server\Panopticon\CoreUpdate`:
    * `panopticon_coreupdate_status` Get Joomla core update status.
    * `panopticon_coreupdate_changesource` Change the Joomla core update source.
    * `panopticon_coreupdate_download` Download the Joomla core update package.
    * `panopticon_coreupdate_download_chunked` Download the Joomla core update package in chunks.
    * `panopticon_coreupdate_activate` Activate the downloaded Joomla core update.
    * `panopticon_coreupdate_disable` Disable the Joomla core update.
    * `panopticon_coreupdate_postupdate` Run post-update tasks for Joomla core update.
    * `panopticon_coreupdate_checksum_prepare` Prepare file checksum verification for core update.
    * `panopticon_coreupdate_checksum_step` Execute a step of file checksum verification.
  * **Akeeba Backup** — Implemented by `Server\Panopticon\AkeebaBackup`:
    * `panopticon_akeebabackup_info` Get Akeeba Backup information and status.
  * **Admin Tools** — Implemented by `Server\Panopticon\AdminTools`:
    * `panopticon_admintools_unblock` Unblock an IP address from Admin Tools.
    * `panopticon_admintools_plugin_disable` Disable the Admin Tools system plugin.
    * `panopticon_admintools_plugin_enable` Enable the Admin Tools system plugin.
    * `panopticon_admintools_htaccess_disable` Disable the Admin Tools .htaccess protection.
    * `panopticon_admintools_htaccess_enable` Enable the Admin Tools .htaccess protection.
    * `panopticon_admintools_tempsuperuser` Create a temporary super user.
    * `panopticon_admintools_scanner_start` Start an Admin Tools security scan.
    * `panopticon_admintools_scanner_step` Execute a step of the Admin Tools security scan.
    * `panopticon_admintools_scans_list` List Admin Tools security scan results.
    * `panopticon_admintools_scan_read` Read details of a specific security scan.
    * `panopticon_admintools_scanalert_read` Read a specific scan alert.
  * **Update Sites** — Implemented by `Server\Panopticon\UpdateSites`:
    * `panopticon_updatesites_list` List update sites.
    * `panopticon_updatesites_read` Read details of a specific update site.
    * `panopticon_updatesites_update` Update an existing update site.
    * `panopticon_updatesites_delete` Delete an update site.
    * `panopticon_updatesites_rebuild` Rebuild the update sites table.
  * **Updates** — Implemented by `Server\Panopticon\Updates`:
    * `panopticon_updates_refresh` Refresh available extension updates.
    * `panopticon_updates_apply` Apply pending extension updates.
  * **Template Overrides** — Implemented by `Server\Panopticon\TemplateOverrides`:
    * `panopticon_templateoverrides_list` List changed template overrides.
    * `panopticon_templateoverrides_read` Read details of a changed template override.

<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Contact;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\ReadMergeUpdateTrait;
use Dionysopoulos\Mcp4Joomla\Utility\TitleToAliasTrait;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * MCP elements for Joomla! contacts management
 */
class Contacts
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use TitleToAliasTrait;
	use ReadMergeUpdateTrait;

	#[McpTool(
		name: 'contact_list',
		description: 'List existing contacts',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listContacts(
		#[Schema(description: 'Search contacts by name')]
		?string $filterSearch = null,
		#[Schema(
			description: 'Filter by publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null,
		#[Schema(description: 'Filter by category ID')]
		?int $filterCategory = null,
		#[Schema(description: 'Filter by language code, or "*" for all languages')]
		?string $filterLanguage = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/contacts');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[published]', $filterState);
		}

		if ($filterCategory !== null)
		{
			$uri->setVar('filter[category]', $filterCategory);
		}

		if ($filterLanguage !== null)
		{
			$uri->setVar('filter[language]', $filterLanguage);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'contacts');
	}

	#[McpTool(
		name: 'contact_read',
		description: 'Retrieve the information of the specified contact',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readContact(
		#[Schema(description: 'The ID of the contact to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/contacts/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'contacts');
	}

	#[McpTool(
		name: 'contact_create',
		description: 'Create a new contact'
	)]
	public function createContact(
		#[Schema(description: 'Contact name', minLength: 1, maxLength: 255)]
		string $name,
		#[Schema(description: 'Contact category ID', minimum: 0, exclusiveMinimum: true)]
		int $catId,
		#[Schema(description: 'URL slug for the contact', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [0, 1, 2, -2]
		)]
		int $published = 1,
		#[Schema(description: 'The Joomla! viewing access level for the contact')]
		?int $access = null,
		#[Schema(description: 'Language code for the contact, or "*" for all languages')]
		string $language = '*',
		#[Schema(description: 'Contact email address')]
		?string $email = null,
		#[Schema(description: 'Contact street address')]
		?string $address = null,
		#[Schema(description: 'Contact city/suburb')]
		?string $suburb = null,
		#[Schema(description: 'Contact state/province')]
		?string $state = null,
		#[Schema(description: 'Contact country')]
		?string $country = null,
		#[Schema(description: 'Contact postal code')]
		?string $postcode = null,
		#[Schema(description: 'Contact telephone number')]
		?string $telephone = null,
		#[Schema(description: 'Contact fax number')]
		?string $fax = null,
		#[Schema(description: 'Contact webpage URL')]
		?string $webpage = null,
		#[Schema(description: 'Miscellaneous information about the contact')]
		?string $misc = null,
		#[Schema(description: 'The Joomla user ID linked to this contact')]
		?int $userId = null,
		#[Schema(description: 'An array of tag IDs to associate with the contact', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Meta description for the contact')]
		?string $metadesc = null,
		#[Schema(description: 'Meta keywords for the contact')]
		?string $metakey = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'name'      => $name,
			'alias'     => $alias ?: $this->titleToAlias($name),
			'catid'     => $catId,
			'published' => $published,
			'access'    => $access ?? 1,
			'language'  => $language,
			'email_to'  => $email,
			'address'   => $address,
			'suburb'    => $suburb,
			'state'     => $state,
			'country'   => $country,
			'postcode'  => $postcode,
			'telephone' => $telephone,
			'fax'       => $fax,
			'webpage'   => $webpage,
			'misc'      => $misc,
			'user_id'   => $userId,
			'tags'      => $tags,
			'metadesc'  => $metadesc,
			'metakey'   => $metakey,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/contacts');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'contacts');
	}

	#[McpTool(
		name: 'contact_update',
		description: 'Update an existing contact',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateContact(
		#[Schema(description: 'The ID of the contact to modify')]
		int $id,
		#[Schema(description: 'Contact name', minLength: 1, maxLength: 255)]
		?string $name = null,
		#[Schema(description: 'Contact category ID', minimum: 0, exclusiveMinimum: true)]
		?int $catId = null,
		#[Schema(description: 'URL slug for the contact', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $published = null,
		#[Schema(description: 'The Joomla! viewing access level for the contact')]
		?int $access = null,
		#[Schema(description: 'Language code for the contact, or "*" for all languages')]
		?string $language = null,
		#[Schema(description: 'Contact email address')]
		?string $email = null,
		#[Schema(description: 'Contact street address')]
		?string $address = null,
		#[Schema(description: 'Contact city/suburb')]
		?string $suburb = null,
		#[Schema(description: 'Contact state/province')]
		?string $state = null,
		#[Schema(description: 'Contact country')]
		?string $country = null,
		#[Schema(description: 'Contact postal code')]
		?string $postcode = null,
		#[Schema(description: 'Contact telephone number')]
		?string $telephone = null,
		#[Schema(description: 'Contact fax number')]
		?string $fax = null,
		#[Schema(description: 'Contact webpage URL')]
		?string $webpage = null,
		#[Schema(description: 'Miscellaneous information about the contact')]
		?string $misc = null,
		#[Schema(description: 'The Joomla user ID linked to this contact')]
		?int $userId = null,
		#[Schema(description: 'An array of tag IDs to associate with the contact', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Meta description for the contact')]
		?string $metadesc = null,
		#[Schema(description: 'Meta keywords for the contact')]
		?string $metakey = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'name'      => $name,
			'alias'     => $alias,
			'catid'     => $catId,
			'published' => $published,
			'access'    => $access,
			'language'  => $language,
			'email_to'  => $email,
			'address'   => $address,
			'suburb'    => $suburb,
			'state'     => $state,
			'country'   => $country,
			'postcode'  => $postcode,
			'telephone' => $telephone,
			'fax'       => $fax,
			'webpage'   => $webpage,
			'misc'      => $misc,
			'user_id'   => $userId,
			'tags'      => $tags,
			'metadesc'  => $metadesc,
			'metakey'   => $metakey,
		];

		$writableFields = array_keys($postData);
		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/contacts/' . $id);

		$postData = $this->prepareReadMergeUpdatePayload($http, (string) $uri, 'contacts', $postData, $writableFields);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'contacts');
	}

	#[McpTool(
		name: 'contact_trash',
		description: 'Moves a contact to the trash by setting its state to -2',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function trashContact(
		#[Schema(description: 'The ID of the contact to trash')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/contacts/' . $id);
		$response = $http->patch($uri, json_encode(['published' => -2]), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'contacts');
	}

	#[McpTool(
		name: 'contact_delete',
		description: 'Permanently deletes a contact. Automatically trashes it first if needed.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteContact(
		#[Schema(description: 'The ID of the contact to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		$this->trashContact($id);

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/contacts/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}

	#[McpTool(
		name: 'contact_form_submit',
		description: 'Submit a contact form for a specific contact'
	)]
	public function submitContactForm(
		#[Schema(description: 'The ID of the contact to submit the form for')]
		int $contactId,
		#[Schema(description: 'The name of the person submitting the form', minLength: 1)]
		string $contactName,
		#[Schema(description: 'The email address of the person submitting the form')]
		string $contactEmail,
		#[Schema(description: 'The subject of the contact form message', minLength: 1)]
		string $contactSubject,
		#[Schema(description: 'The message body of the contact form', minLength: 1)]
		string $contactMessage
	)
	{
		$this->autologMCPTool();

		$postData = [
			'contact_name'    => $contactName,
			'contact_email'   => $contactEmail,
			'contact_subject' => $contactSubject,
			'contact_message' => $contactMessage,
		];

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/contacts/form/' . $contactId);

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'contacts');
	}
}

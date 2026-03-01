<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Content;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\ArticleTextTrait;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\TitleToAliasTrait;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * MCP elements for Joomla! articles managements
 *
 * @see  ../../../http/README.md for details
 * @link ../../../http/content_articles.http
 */
class Articles
{
	use ArticleTextTrait;
	use TitleToAliasTrait;
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'content_articles_create',
		description: 'Create a new article'
	)]
	public function createArticle(
		#[Schema(description: 'Article title', minLength: 1, maxLength: 255)]
		string $title,
		#[Schema(description: 'Article category ID', minimum: 0, exclusiveMinimum: true)]
		int $catId,
		#[Schema(description: 'Article introductory (intro) text', minLength: 1)]
		string $introText,
		#[Schema(description: 'Article full text, without the intro text', minLength: 0)]
		string $fullText,
		#[Schema(
			description: 'Article publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [0, 1, 2, -2]
		)]
		int $state = 1,
		#[Schema(description: 'When the article will start being published', format: 'date-time')]
		?string $publishStartTime = null,
		#[Schema(description: 'When the article will stop being published', format: 'date-time')]
		?string $publishEndTime = null,
		#[Schema(description: 'The Joomla! viewing access level for the article')]
		?int $access = null,
		#[Schema(description: 'Number of public views')]
		?int $hits = null,
		#[Schema(description: 'URL slug for the article', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(description: 'Language code for the article, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		string $language = '*',
		#[Schema(description: 'When the article was originally created', format: 'date-time')]
		?string $createdTime = null,
		#[Schema(description: 'The ID of the Joomla user who originally created the article')]
		?int $createdBy = null,
		#[Schema(description: 'The name of the person who wrote the article, if it is different than the full name of the Joomla user who created it')]
		?string $createdByAlias = null,
		#[Schema(description: 'The intro image of the article, as a Joomla! image URL')]
		?string $imageIntro = null,
		#[Schema(description: 'The alt text of the intro image of the article. Empty string if it is a decorative image.')]
		?string $imageIntroAlt = null,
		#[Schema(description: 'The CSS class of the intro image of the article.')]
		?string $imageIntroFloat = null,
		#[Schema(description: 'The caption of the intro image of the article.')]
		?string $imageIntroCaption = null,
		#[Schema(description: 'The full text image of the article, as a Joomla! image URL')]
		?string $imageFulltext = null,
		#[Schema(description: 'The alt text of the full text image of the article. Empty string if it is a decorative image.')]
		?string $imageFulltextAlt = null,
		#[Schema(description: 'The CSS class of the full text image of the article.')]
		?string $imageFulltextFloat = null,
		#[Schema(description: 'The caption of the full text image of the article.')]
		?string $imageFulltextCaption = null,
		#[Schema(description: 'The URL to an optional first additional link to display at the bottom of the article')]
		?string $urlA = null,
		#[Schema(description: 'The text to an optional first additional link to display at the bottom of the article')]
		?string $urlAText = null,
		#[Schema(
			description: 'The link target to an optional first additional link to display at the bottom of the article',
			enum: [null, '_blank', '_self', '_parent', '_top']
		)]
		?string $urlATarget = null,
		#[Schema(description: 'The URL to an optional second additional link to display at the bottom of the article')]
		?string $urlB = null,
		#[Schema(description: 'The text to an optional second additional link to display at the bottom of the article')]
		?string $urlBText = null,
		#[Schema(
			description: 'The link target to an optional second additional link to display at the bottom of the article',
			enum: [null, '_blank', '_self', '_parent', '_top']
		)]
		?string $urlBTarget = null,
		#[Schema(description: 'The URL to an optional third additional link to display at the bottom of the article')]
		?string $urlC = null,
		#[Schema(description: 'The text to an optional third additional link to display at the bottom of the article')]
		?string $urlCText = null,
		#[Schema(
			description: 'The link target to an optional third additional link to display at the bottom of the article',
			enum: [null, '_blank', '_self', '_parent', '_top']
		)]
		?string $urlCTarget = null,
		#[Schema(description: 'Optional meta keywords, separated by commas (e.g., "keyword1, keyword2, keyword3")')]
		?string $metaKeywords = '',
		#[Schema(description: 'Optional meta description')]
		?string $metaDescription = '',
		#[Schema(
			description: 'The robots instruction for links to this article',
			enum: [null, 'index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow']
		)]
		?string $metadataRobots = null,
		#[Schema(description: 'The full name of the author of the article for use in metadata, if different than the full name of the Joomla! user who created the article')]
		?string $metadataAuthor = null,
		#[Schema(description: 'The content rights for the article, published as article metadata')]
		?string $metadataRights = null,
		#[Schema(description: 'Is the article featured?', enum: ['true', 'false'])]
		bool $featured = false,
		#[Schema(description: 'When the article will start being featured', format: 'date-time')]
		?string $featuredStartTime = null,
		#[Schema(description: 'When the article will stop being featured', format: 'date-time')]
		?string $featuredEndTime = null,
		#[Schema(description: 'Optional note for the article')]
		?string $note = null,
		#[Schema(description: 'An array of tag IDs to associate with the article', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Optional nested images payload (Joomla format)')]
		?array $images = null,
		#[Schema(description: 'Optional nested URLs payload (Joomla format)')]
		?array $urls = null,
		#[Schema(description: 'Optional nested metadata payload (Joomla format)')]
		?array $metadata = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'            => $title,
			'alias'            => $alias ?: $this->titleToAlias($title),
			'catid'            => $catId,
			'state'            => $state,
			'created'          => $createdTime,
			'created_by'       => $createdBy,
			'created_by_alias' => $createdByAlias,
			'publish_up'       => $publishStartTime,
			'publish_down'     => $publishEndTime,
			'images'           => [
				'image_intro'            => $imageIntro,
				'image_intro_alt'        => $imageIntroAlt,
				'float_intro'            => $imageIntroFloat,
				'image_intro_caption'    => $imageIntroCaption,
				'image_fulltext'         => $imageFulltext,
				'image_fulltext_alt'     => $imageFulltextAlt,
				'float_fulltext'         => $imageFulltextFloat,
				'image_fulltext_caption' => $imageFulltextCaption,
			],
			'urls'             => [
				'urla'     => $urlA,
				'urlatext' => $urlAText,
				'targeta'  => $urlATarget,
				'urlb'     => $urlB,
				'urlbtext' => $urlBText,
				'targetb'  => $urlBTarget,
				'urlc'     => $urlC,
				'urlctext' => $urlCText,
				'targetc'  => $urlCTarget,
			],
			'metakey'          => $metaKeywords,
			'metadesc'         => $metaDescription,
			'access'           => $access ?? 1,
			'hits'             => $hits ?? 0,
			'metadata'         => [
				'robots' => $metadataRobots,
				'author' => $metadataAuthor,
				'rights' => $metadataRights,
			],
			'featured'         => $featured ?? 0,
			'language'         => $language ?? '*',
			'note'             => $note,
			'tags'             => $tags,
			'featured_up'      => $featuredStartTime,
			'featured_down'    => $featuredEndTime,
			'introtext'        => $this->toHtml($introText),
			'fulltext'         => $this->toHtml($fullText),
		];

		$postData['images']   = array_filter($postData['images'], fn($v) => $v !== null);
		$postData['urls']     = array_filter($postData['urls'], fn($v) => $v !== null);
		$postData['metadata'] = array_filter($postData['metadata'], fn($v) => $v !== null);

		if (empty($postData['images']))
		{
			unset($postData['images']);
		}

		if (empty($postData['urls']))
		{
			unset($postData['urls']);
		}
		if (empty($postData['metadata']))
		{
			unset($postData['metadata']);
		}

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/content/articles');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'articles');
	}

	#[McpTool(
		name: 'content_articles_update',
		description: 'Update an existing article',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateArticle(
		#[Schema(description: 'The ID of the article to modify')]
		int $articleId,
		#[Schema(description: 'Article title', minLength: 1, maxLength: 255)]
		?string $title = null,
		#[Schema(description: 'Article category ID', minimum: 0, exclusiveMinimum: true)]
		?int $catId = null,
		#[Schema(description: 'Article introductory (intro) text', minLength: 1)]
		?string $introText = null,
		#[Schema(description: 'Article full text, without the intro text', minLength: 0)]
		?string $fullText = null,
		#[Schema(
			description: 'Article publish state: null=no change, 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $state = null,
		#[Schema(description: 'When the article will start being published', format: 'date-time')]
		?string $publishStartTime = null,
		#[Schema(description: 'When the article will stop being published', format: 'date-time')]
		?string $publishEndTime = null,
		#[Schema(description: 'The Joomla! viewing access level for the article')]
		?int $access = null,
		#[Schema(description: 'Number of public views')]
		?int $hits = null,
		#[Schema(description: 'URL slug for the article', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(description: 'Language code for the article, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $language = null,
		#[Schema(description: 'When the article was originally created', format: 'date-time')]
		?string $createdTime = null,
		#[Schema(description: 'The ID of the Joomla user who originally created the article')]
		?int $createdBy = null,
		#[Schema(description: 'The name of the person who wrote the article, if it is different than the full name of the Joomla user who created it')]
		?string $createdByAlias = null,
		#[Schema(description: 'The intro image of the article, as a Joomla! image URL')]
		?string $imageIntro = null,
		#[Schema(description: 'The alt text of the intro image of the article. Empty string if it is a decorative image.')]
		?string $imageIntroAlt = null,
		#[Schema(description: 'The CSS class of the intro image of the article.')]
		?string $imageIntroFloat = null,
		#[Schema(description: 'The caption of the intro image of the article.')]
		?string $imageIntroCaption = null,
		#[Schema(description: 'The full text image of the article, as a Joomla! image URL')]
		?string $imageFulltext = null,
		#[Schema(description: 'The alt text of the full text image of the article. Empty string if it is a decorative image.')]
		?string $imageFulltextAlt = null,
		#[Schema(description: 'The CSS class of the full text image of the article.')]
		?string $imageFulltextFloat = null,
		#[Schema(description: 'The caption of the full text image of the article.')]
		?string $imageFulltextCaption = null,
		#[Schema(description: 'The URL to an optional first additional link to display at the bottom of the article')]
		?string $urlA = null,
		#[Schema(description: 'The text to an optional first additional link to display at the bottom of the article')]
		?string $urlAText = null,
		#[Schema(
			description: 'The link target to an optional first additional link to display at the bottom of the article',
			enum: [null, '_blank', '_self', '_parent', '_top']
		)]
		?string $urlATarget = null,
		#[Schema(description: 'The URL to an optional second additional link to display at the bottom of the article')]
		?string $urlB = null,
		#[Schema(description: 'The text to an optional second additional link to display at the bottom of the article')]
		?string $urlBText = null,
		#[Schema(
			description: 'The link target to an optional second additional link to display at the bottom of the article',
			enum: [null, '_blank', '_self', '_parent', '_top']
		)]
		?string $urlBTarget = null,
		#[Schema(description: 'The URL to an optional third additional link to display at the bottom of the article')]
		?string $urlC = null,
		#[Schema(description: 'The text to an optional third additional link to display at the bottom of the article')]
		?string $urlCText = null,
		#[Schema(
			description: 'The link target to an optional third additional link to display at the bottom of the article',
			enum: [null, '_blank', '_self', '_parent', '_top']
		)]
		?string $urlCTarget = null,
		#[Schema(description: 'Optional meta keywords, separated by commas (e.g., "keyword1, keyword2, keyword3")')]
		?string $metaKeywords = null,
		#[Schema(description: 'Optional meta description')]
		?string $metaDescription = null,
		#[Schema(
			description: 'The robots instruction for links to this article',
			enum: [null, 'index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow']
		)]
		?string $metadataRobots = null,
		#[Schema(description: 'The full name of the author of the article for use in metadata, if different than the full name of the Joomla! user who created the article')]
		?string $metadataAuthor = null,
		#[Schema(description: 'The content rights for the article, published as article metadata')]
		?string $metadataRights = null,
		#[Schema(description: 'Is the article featured?', enum: ['true', 'false'])]
		?bool $featured = null,
		#[Schema(description: 'When the article will start being featured', format: 'date-time')]
		?string $featuredStartTime = null,
		#[Schema(description: 'When the article will stop being featured', format: 'date-time')]
		?string $featuredEndTime = null,
		#[Schema(description: 'Optional note for the article')]
		?string $note = null,
		#[Schema(description: 'An array of tag IDs to associate with the article', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Optional nested images payload (Joomla field names)')]
		?array $images = null,
		#[Schema(description: 'Optional nested URLs payload (Joomla field names)')]
		?array $urls = null,
		#[Schema(description: 'Optional nested metadata payload (Joomla field names)')]
		?array $metadata = null
	)
	{
		$this->autologMCPTool();

		$incomingData = [
			'title'            => $title,
			'alias'            => $alias,
			'catid'            => $catId,
			'state'            => $state,
			'created'          => $createdTime,
			'created_by'       => $createdBy,
			'created_by_alias' => $createdByAlias,
			'publish_up'       => $publishStartTime,
			'publish_down'     => $publishEndTime,
			'images'           => [
				'image_intro'            => $imageIntro,
				'image_intro_alt'        => $imageIntroAlt,
				'float_intro'            => $imageIntroFloat,
				'image_intro_caption'    => $imageIntroCaption,
				'image_fulltext'         => $imageFulltext,
				'image_fulltext_alt'     => $imageFulltextAlt,
				'float_fulltext'         => $imageFulltextFloat,
				'image_fulltext_caption' => $imageFulltextCaption,
			],
			'urls'             => [
				'urla'     => $urlA,
				'urlatext' => $urlAText,
				'targeta'  => $urlATarget,
				'urlb'     => $urlB,
				'urlbtext' => $urlBText,
				'targetb'  => $urlBTarget,
				'urlc'     => $urlC,
				'urlctext' => $urlCText,
				'targetc'  => $urlCTarget,
			],
			'metakey'          => $metaKeywords,
			'metadesc'         => $metaDescription,
			'access'           => $access,
			'hits'             => $hits,
			'metadata'         => [
				'robots' => $metadataRobots,
				'author' => $metadataAuthor,
				'rights' => $metadataRights,
			],
			'featured'         => $featured,
			'language'         => $language,
			'note'             => $note,
			'tags'             => $tags,
			'featured_up'      => $featuredStartTime,
			'featured_down'    => $featuredEndTime,
			'introtext'        => $introText !== null ? $this->toHtml($introText) : null,
			'fulltext'         => $fullText !== null ? $this->toHtml($fullText) : null,
		];

		$imagesData   = $this->normaliseNestedArticleField($images);
		$urlsData     = $this->normaliseNestedArticleField($urls);
		$metadataData = $this->normaliseNestedArticleField($metadata);

		$incomingData['images']   = array_filter($incomingData['images'], fn($v) => $v !== null);
		$incomingData['urls']     = array_filter($incomingData['urls'], fn($v) => $v !== null);
		$incomingData['metadata'] = array_filter($incomingData['metadata'], fn($v) => $v !== null);

		if (!empty($imagesData))
		{
			$incomingData['images'] = array_replace($imagesData, $incomingData['images']);
		}

		if (!empty($urlsData))
		{
			$incomingData['urls'] = array_replace($urlsData, $incomingData['urls']);
		}

		if (!empty($metadataData))
		{
			$incomingData['metadata'] = array_replace($metadataData, $incomingData['metadata']);
		}

		if (empty($incomingData['images']))
		{
			unset($incomingData['images']);
		}

		if (empty($incomingData['urls']))
		{
			unset($incomingData['urls']);
		}
		if (empty($incomingData['metadata']))
		{
			unset($incomingData['metadata']);
		}

		$incomingData = array_filter($incomingData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/content/articles/' . $articleId);

		$currentResponse = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($currentResponse);

		$currentData = $this->getDataFromResponse($currentResponse, 'articles');

		if (!isset($currentData->data->attributes) || !is_object($currentData->data->attributes))
		{
			throw new \RuntimeException('Failed to process Joomla API response (no attributes in article data).');
		}

		$currentAttributes = $currentData->data->attributes;
		$postData          = $this->getUpdateArticlePayloadFromAttributes($currentAttributes);

		if (isset($incomingData['images']))
		{
			$postData['images'] = array_replace($postData['images'], $incomingData['images']);
			unset($incomingData['images']);
		}

		if (isset($incomingData['urls']))
		{
			$postData['urls'] = array_replace($postData['urls'], $incomingData['urls']);
			unset($incomingData['urls']);
		}

		if (isset($incomingData['metadata']))
		{
			$postData['metadata'] = array_replace($postData['metadata'], $incomingData['metadata']);
			unset($incomingData['metadata']);
		}

		$postData = array_replace($postData, $incomingData);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'articles');
	}

	private function getUpdateArticlePayloadFromAttributes(object $attributes): array
	{
		$images = $this->normaliseNestedArticleField($attributes->images ?? null);
		$urls = $this->normaliseNestedArticleField($attributes->urls ?? null);
		$metadata = $this->normaliseNestedArticleField($attributes->metadata ?? null);

		return [
			'title'            => $attributes->title ?? null,
			'alias'            => $attributes->alias ?? null,
			'catid'            => $attributes->catid ?? null,
			'state'            => $attributes->state ?? null,
			'created'          => $attributes->created ?? null,
			'created_by'       => $attributes->created_by ?? null,
			'created_by_alias' => $attributes->created_by_alias ?? null,
			'publish_up'       => $attributes->publish_up ?? null,
			'publish_down'     => $attributes->publish_down ?? null,
			'images'           => [
				'image_intro'            => $images['image_intro'] ?? null,
				'image_intro_alt'        => $images['image_intro_alt'] ?? null,
				'float_intro'            => $images['float_intro'] ?? null,
				'image_intro_caption'    => $images['image_intro_caption'] ?? null,
				'image_fulltext'         => $images['image_fulltext'] ?? null,
				'image_fulltext_alt'     => $images['image_fulltext_alt'] ?? null,
				'float_fulltext'         => $images['float_fulltext'] ?? null,
				'image_fulltext_caption' => $images['image_fulltext_caption'] ?? null,
			],
			'urls'             => [
				'urla'     => $urls['urla'] ?? null,
				'urlatext' => $urls['urlatext'] ?? null,
				'targeta'  => $urls['targeta'] ?? null,
				'urlb'     => $urls['urlb'] ?? null,
				'urlbtext' => $urls['urlbtext'] ?? null,
				'targetb'  => $urls['targetb'] ?? null,
				'urlc'     => $urls['urlc'] ?? null,
				'urlctext' => $urls['urlctext'] ?? null,
				'targetc'  => $urls['targetc'] ?? null,
			],
			'metakey'          => $attributes->metakey ?? null,
			'metadesc'         => $attributes->metadesc ?? null,
			'access'           => $attributes->access ?? null,
			'hits'             => $attributes->hits ?? null,
			'metadata'         => [
				'robots' => $metadata['robots'] ?? null,
				'author' => $metadata['author'] ?? null,
				'rights' => $metadata['rights'] ?? null,
			],
			'featured'         => $attributes->featured ?? null,
			'language'         => $attributes->language ?? null,
			'note'             => $attributes->note ?? null,
			'tags'             => $attributes->tags ?? null,
			'featured_up'      => $attributes->featured_up ?? null,
			'featured_down'    => $attributes->featured_down ?? null,
			'introtext'        => $attributes->introtext ?? null,
			'fulltext'         => $attributes->fulltext ?? null,
		];
	}

	private function normaliseNestedArticleField(mixed $field): array
	{
		if (is_array($field))
		{
			return $field;
		}

		if (is_object($field))
		{
			return (array) $field;
		}

		if (!is_string($field) || trim($field) === '')
		{
			return [];
		}

		try
		{
			$decoded = json_decode($field, true, flags: JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			return [];
		}

		return is_array($decoded) ? $decoded : [];
	}

	#[McpTool(
		name: 'content_articles_list',
		description: 'List existing articles',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listArticles(
		#[Schema(description: 'The ID of the Joomla user who has created the returned articles', minimum: 0, exclusiveMinimum: true)]
		?int $filterAuthor = null,
		#[Schema(description: 'The ID of the Joomla articles category the returned articles belong into', minimum: 0, exclusiveMinimum: true)]
		?int $filterCategory = null,
		#[Schema(
			description: 'The article state of the returned articles: 0=unpublished, 1=published, 2=archived, -2=trashed, null=any state',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null,
		#[Schema(description: 'The featured state of the returned articles: 0=not featured, 1=featured, null=both featured and not featured',
			enum: [null, 0, 1]
		)]
		?int $filterFeatured = null,
		#[Schema(description: 'An array of tag IDs the returned articles must be assigned. NULL to return articles regardless of their tags.', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $filterTag = null,
		#[Schema(description: 'The language code of the returned articles, "*" for articles explicitly assigned to "all languages", or NULL for articles assigned to any language', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $filterLanguage = null,
		#[Schema(description: 'The returned articles must have a title that matches this search string', pattern: '^.*$')]
		?string $filterSearch = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/content/articles');

		if ($filterAuthor !== null)
		{
			$uri->setVar('filter[author]', $filterAuthor);
		}

		if ($filterCategory !== null)
		{
			$uri->setVar('filter[category]', $filterCategory);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[state]', $filterState);
		}

		if ($filterFeatured !== null)
		{
			$uri->setVar('filter[featured]', $filterFeatured);
		}

		if ($filterTag !== null)
		{
			foreach ($filterTag as $tagId)
			{
				$uri->setVar('filter[tag][]', $tagId);
			}
		}

		if ($filterLanguage !== null)
		{
			$uri->setVar('filter[language]', $filterLanguage);
		}

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'articles');
	}

	#[McpTool(
		name: 'content_articles_read',
		description: 'Retrieve the information of the specified article',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readArticle(
		#[Schema(description: 'The ID of the article to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/content/articles/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'articles');
	}

	#[McpTool(
		name: 'content_articles_trash',
		description: 'Moves an article to the trash by setting its state to -2',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function trashArticle(
		#[Schema(description: 'The ID of the article to trash')]
		int $id
	)
	{
		$this->autologMCPTool();

		return $this->updateArticle(articleId: $id, state: -2);
	}

	#[McpTool(
		name: 'content_articles_delete',
		description: 'Permanently deletes an article. Automatically trashes it first if needed.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteArticle(int $id): bool
	{
		$this->autologMCPTool();

		$this->trashArticle($id);

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/content/articles/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

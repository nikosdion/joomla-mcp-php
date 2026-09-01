<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Server\Tickets;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\Content\ImageContent;
use PhpMcp\Schema\Content\TextContent;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * Attachment tools for ATS (Akeeba Ticket System). Requires ATS Pro.
 */
class Attachments
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	/**
	 * Maximum size, in bytes, of content this class will inline into an MCP tool result.
	 *
	 * Attachments (or files extracted from a ZIP attachment) larger than this are rejected with a
	 * RuntimeException pointing the caller at saveAttachmentToFile() instead. Inlining anything much
	 * bigger risks exceeding the MCP client's stdio message size limit, which silently kills the
	 * connection rather than surfacing a usable error.
	 */
	private const MAX_INLINE_BYTES = 200 * 1024;

	#[McpTool(
		name: 'tickets_attachments_list',
		description: 'List all ATS (Akeeba Ticket System) ticket attachments. Requires ATS Pro.',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listAttachments(
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/ats/attachments');

		if ($pageLimit !== null)
		{
			$uri->setVar('page[limit]', $pageLimit);
		}

		if ($pageOffset !== null)
		{
			$uri->setVar('page[offset]', $pageOffset);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'attachments');
	}

	#[McpTool(
		name: 'tickets_attachments_list_for_post',
		description: 'List all attachments for a specific ATS (Akeeba Ticket System) ticket post. Requires ATS Pro.',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listAttachmentsForPost(
		#[Schema(description: 'The ID of the post whose attachments to list')]
		int $postId
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/posts/' . $postId . '/attachments');
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'attachments');
	}

	#[McpTool(
		name: 'tickets_attachments_read',
		description: 'Read metadata of a specific ATS (Akeeba Ticket System) attachment, including its file size. Requires ATS Pro.',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readAttachment(
		#[Schema(description: 'The ID of the attachment to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/attachments/' . $id);
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'attachments');
	}

	#[McpTool(
		name: 'tickets_attachments_download',
		description: 'Download the content of an ATS (Akeeba Ticket System) attachment. Images are returned as vision-ready content. Text files (including .php) are returned as plain text. ZIP archives are extracted and each file returned individually. Requires ATS Pro.',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function downloadAttachment(
		#[Schema(description: 'The ID of the attachment to download')]
		int $id
	): mixed
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/attachments/' . $id . '/download');
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		$contentType = $response->getHeaderLine('Content-Type');
		$mimeType    = strtolower(trim(explode(';', $contentType)[0]));
		$body        = (string) $response->getBody();

		if ($mimeType === 'application/zip')
		{
			return $this->extractZip($body, $id);
		}

		if (strlen($body) > self::MAX_INLINE_BYTES)
		{
			throw new \RuntimeException(sprintf(
				'Attachment %d is %s bytes, too large to return inline (limit: %s bytes). '
				. 'Use the tickets_attachments_save_to_file tool to save it to disk instead, '
				. 'then inspect it with command-line tools.',
				$id,
				number_format(strlen($body)),
				number_format(self::MAX_INLINE_BYTES)
			));
		}

		return $this->buildContent($body, $mimeType);
	}

	#[McpTool(
		name: 'tickets_attachments_save_to_file',
		description: 'Download an ATS (Akeeba Ticket System) attachment and save its raw content to a file path on disk, instead of inlining it into the conversation. Use this for attachments that are too large to return inline (e.g. ZIP archives containing large log files) — save it, then inspect it locally with command-line tools (unzip, grep, rg, sed, head, tail). Requires ATS Pro.',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function saveAttachmentToFile(
		#[Schema(description: 'The ID of the attachment to download')]
		int $id,
		#[Schema(description: 'Absolute filesystem path to save the attachment to. Its parent directory must already exist.')]
		string $path
	): array
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/attachments/' . $id . '/download');
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		$directory = dirname($path);

		if (!is_dir($directory))
		{
			throw new \RuntimeException("The directory '{$directory}' does not exist. Create it first.");
		}

		$body = (string) $response->getBody();

		if (file_put_contents($path, $body) === false)
		{
			throw new \RuntimeException("Failed to write the attachment content to '{$path}'.");
		}

		$contentType = $response->getHeaderLine('Content-Type');
		$mimeType    = strtolower(trim(explode(';', $contentType)[0]));
		$disposition = $response->getHeaderLine('Content-Disposition');
		$filename    = null;

		if (preg_match('/filename="?([^";]+)"?/i', $disposition, $matches))
		{
			$filename = $matches[1];
		}

		return [
			'path'     => $path,
			'bytes'    => strlen($body),
			'mimeType' => $mimeType,
			'filename' => $filename,
		];
	}

	#[McpTool(
		name: 'tickets_attachments_delete',
		description: 'Delete an ATS (Akeeba Ticket System) attachment. Requires ATS Pro.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteAttachment(
		#[Schema(description: 'The ID of the attachment to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/attachments/' . $id);
		$response = $http->delete($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}

	/**
	 * Builds a Content object appropriate for the given MIME type and binary content.
	 *
	 * Images become ImageContent for VLM processing. Text types are returned as-is.
	 * Unknown binary is base64-encoded inside a JSON TextContent.
	 */
	private function buildContent(string $body, string $mimeType, string $filename = ''): ImageContent|TextContent
	{
		$prefix = $filename !== '' ? "=== {$filename} ===\n" : '';

		if (str_starts_with($mimeType, 'image/'))
		{
			return ImageContent::fromString($body, $mimeType);
		}

		$textMimes = ['application/json', 'application/xml', 'application/xhtml+xml', 'application/javascript'];

		if (str_starts_with($mimeType, 'text/') || in_array($mimeType, $textMimes, true))
		{
			return TextContent::make($prefix . $body);
		}

		return TextContent::make($prefix . json_encode(['mimeType' => $mimeType, 'base64' => base64_encode($body)]));
	}

	/**
	 * Extracts a ZIP archive from binary content and returns each file as a Content item.
	 *
	 * Entries whose uncompressed size exceeds self::MAX_INLINE_BYTES are not extracted; a placeholder
	 * TextContent pointing at saveAttachmentToFile() is returned for them instead. A ZIP's compressed
	 * size (checked before download) can be misleadingly small compared to what it expands to — e.g. a
	 * 615 KB ZIP can hold a 17 MB log file — so this per-entry check on the uncompressed size is the
	 * only reliable guard.
	 *
	 * @return array<ImageContent|TextContent>
	 */
	private function extractZip(string $zipContent, int $attachmentId): array
	{
		$tmpFile = tempnam(sys_get_temp_dir(), 'ats_zip_');

		try
		{
			file_put_contents($tmpFile, $zipContent);

			$zip = new \ZipArchive();

			if ($zip->open($tmpFile) !== true)
			{
				throw new \RuntimeException('Failed to open the ZIP archive from the attachment.');
			}

			$contents = [];

			for ($i = 0; $i < $zip->numFiles; $i++)
			{
				$name = $zip->getNameIndex($i);

				// Skip directory entries
				if (str_ends_with($name, '/'))
				{
					continue;
				}

				$stat              = $zip->statIndex($i);
				$uncompressedSize  = $stat['size'] ?? 0;

				if ($uncompressedSize > self::MAX_INLINE_BYTES)
				{
					$contents[] = TextContent::make(sprintf(
						"=== %s ===\nThis file is %s bytes when extracted, too large to return inline "
						. "(limit: %s bytes). Use the tickets_attachments_save_to_file tool with id=%d "
						. "to save the ZIP attachment to disk, then extract and inspect '%s' locally "
						. "with command-line tools (unzip, grep, rg, sed, head, tail).",
						$name,
						number_format($uncompressedSize),
						number_format(self::MAX_INLINE_BYTES),
						$attachmentId,
						$name
					));

					continue;
				}

				$fileContent = $zip->getFromIndex($i);
				$ext         = strtolower(pathinfo($name, PATHINFO_EXTENSION));
				$mimeType    = $this->mimeFromExtension($ext);

				$contents[] = $this->buildContent($fileContent, $mimeType, $name);
			}

			$zip->close();

			return empty($contents)
				? [TextContent::make('The ZIP archive is empty.')]
				: $contents;
		}
		finally
		{
			if (file_exists($tmpFile))
			{
				unlink($tmpFile);
			}
		}
	}

	/**
	 * Maps a file extension to a MIME type for use in buildContent().
	 *
	 * .php and other source/config/log extensions are treated as text/plain so
	 * users can attach diagnostic files and have the AI read them directly.
	 */
	private function mimeFromExtension(string $ext): string
	{
		return match ($ext)
		{
			'jpg', 'jpeg'                                                              => 'image/jpeg',
			'png'                                                                      => 'image/png',
			'gif'                                                                      => 'image/gif',
			'webp'                                                                     => 'image/webp',
			'svg'                                                                      => 'image/svg+xml',
			'txt', 'log', 'csv', 'md',
			'php', 'js', 'css', 'html', 'htm',
			'xml', 'json', 'ini', 'conf', 'cfg',
			'yaml', 'yml', 'sql', 'sh', 'bat'                                         => 'text/plain',
			default                                                                    => 'application/octet-stream',
		};
	}
}

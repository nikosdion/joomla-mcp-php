<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Server\Tickets;

use Dionysopoulos\Mcp4Joomla\Server\Tickets\Attachments;
use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TestContainerTrait;
use PhpMcp\Schema\Content\ImageContent;
use PhpMcp\Schema\Content\TextContent;
use PHPUnit\Framework\TestCase;

class AttachmentsTest extends TestCase
{
	use TestContainerTrait;

	private Attachments $attachments;

	protected function setUp(): void
	{
		$this->setUpTestContainer();
		$this->attachments = new Attachments();
	}

	protected function tearDown(): void
	{
		$this->tearDownTestContainer();
	}

	public function testListAttachments(): void
	{
		$body = json_encode([
			'data' => [
				['type' => 'attachments', 'id' => '1', 'attributes' => ['filename' => 'test.png', 'file_size' => 1024]],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/attachments')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->attachments->listAttachments();

		$this->assertIsObject($result);
		$this->assertCount(1, $result->data);
		$this->assertSame('attachments', $result->data[0]->type);
	}

	public function testListAttachmentsForPost(): void
	{
		$body = json_encode([
			'data' => [
				['type' => 'attachments', 'id' => '2', 'attributes' => ['filename' => 'log.txt']],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/posts/5/attachments')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->attachments->listAttachmentsForPost(5);

		$this->assertIsObject($result);
		$this->assertCount(1, $result->data);
	}

	public function testReadAttachment(): void
	{
		$body = json_encode([
			'data' => ['type' => 'attachments', 'id' => '7', 'attributes' => ['filename' => 'photo.jpg', 'file_size' => 204800]],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/attachments/7')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->attachments->readAttachment(7);

		$this->assertIsObject($result);
		$this->assertSame('attachments', $result->data->type);
	}

	public function testDownloadAttachmentImage(): void
	{
		$imageData = "\x89PNG\r\n\x1a\n"; // Minimal PNG header

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/attachments/7/download')))
			->willReturn(createJoomlaResponse(200, $imageData, ['Content-Type' => 'image/png']));

		$result = $this->attachments->downloadAttachment(7);

		$this->assertInstanceOf(ImageContent::class, $result);
		$this->assertSame('image/png', $result->mimeType);
		$this->assertSame(base64_encode($imageData), $result->data);
	}

	public function testDownloadAttachmentTextFile(): void
	{
		$logContent = "2025-01-01 12:00:00 [INFO] Application started\n2025-01-01 12:00:01 [INFO] Done\n";

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/attachments/8/download')))
			->willReturn(createJoomlaResponse(200, $logContent, ['Content-Type' => 'text/plain']));

		$result = $this->attachments->downloadAttachment(8);

		$this->assertInstanceOf(TextContent::class, $result);
		$this->assertStringContainsString('Application started', $result->text);
	}

	public function testDownloadAttachmentPhpFileAsText(): void
	{
		$phpContent = "<?php\necho 'Hello';\n";

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->willReturn(createJoomlaResponse(200, $phpContent, ['Content-Type' => 'application/octet-stream; name=config.php']));

		// The binary MIME is the Content-Type; the .php extension handling is in ZIP extraction only.
		// For a direct download, the MIME from the header governs the behaviour.
		$result = $this->attachments->downloadAttachment(9);

		// application/octet-stream falls through to the base64 JSON path
		$this->assertInstanceOf(TextContent::class, $result);
		$decoded = json_decode($result->text, true);
		$this->assertArrayHasKey('mimeType', $decoded);
		$this->assertArrayHasKey('base64', $decoded);
	}

	public function testDownloadAttachmentZip(): void
	{
		// Build a minimal valid ZIP in memory
		$zip     = new \ZipArchive();
		$tmpFile = tempnam(sys_get_temp_dir(), 'test_zip_');
		$zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
		$zip->addFromString('readme.txt', 'Hello from inside the ZIP');
		$zip->close();

		$zipContent = file_get_contents($tmpFile);
		unlink($tmpFile);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->willReturn(createJoomlaResponse(200, $zipContent, ['Content-Type' => 'application/zip']));

		$result = $this->attachments->downloadAttachment(10);

		$this->assertIsArray($result);
		$this->assertCount(1, $result);
		$this->assertInstanceOf(TextContent::class, $result[0]);
		$this->assertStringContainsString('readme.txt', $result[0]->text);
		$this->assertStringContainsString('Hello from inside the ZIP', $result[0]->text);
	}

	public function testDownloadAttachmentZipWithImageAndPhp(): void
	{
		$zip     = new \ZipArchive();
		$tmpFile = tempnam(sys_get_temp_dir(), 'test_zip2_');
		$zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
		$zip->addFromString('screenshot.png', "\x89PNG\r\n\x1a\n"); // fake PNG
		$zip->addFromString('config.php', "<?php\ndefine('DEBUG', true);\n");
		$zip->close();

		$zipContent = file_get_contents($tmpFile);
		unlink($tmpFile);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->willReturn(createJoomlaResponse(200, $zipContent, ['Content-Type' => 'application/zip']));

		$result = $this->attachments->downloadAttachment(11);

		$this->assertIsArray($result);
		$this->assertCount(2, $result);

		$types = array_map(fn($item) => get_class($item), $result);
		$this->assertContains(ImageContent::class, $types);
		$this->assertContains(TextContent::class, $types);

		// PHP file should be readable text
		$textItems = array_filter($result, fn($item) => $item instanceof TextContent);
		$textItem  = reset($textItems);
		$this->assertStringContainsString('config.php', $textItem->text);
		$this->assertStringContainsString("define('DEBUG'", $textItem->text);
	}

	public function testDownloadAttachmentTooLargeThrows(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('get')
			->willReturn(createJoomlaResponse(200, str_repeat('x', 201 * 1024), ['Content-Type' => 'text/plain']));

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches('/too large to return inline/');

		$this->attachments->downloadAttachment(12);
	}

	public function testDownloadAttachmentZipWithOversizedEntrySkipsIt(): void
	{
		$zip     = new \ZipArchive();
		$tmpFile = tempnam(sys_get_temp_dir(), 'test_zip3_');
		$zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
		$zip->addFromString('small.txt', 'This one is small enough to inline.');
		$zip->addFromString('huge.txt', str_repeat('x', 201 * 1024));
		$zip->close();

		$zipContent = file_get_contents($tmpFile);
		unlink($tmpFile);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->willReturn(createJoomlaResponse(200, $zipContent, ['Content-Type' => 'application/zip']));

		$result = $this->attachments->downloadAttachment(13);

		$this->assertIsArray($result);
		$this->assertCount(2, $result);

		$texts = array_map(fn($item) => $item->text, $result);

		$this->assertTrue((bool) array_filter($texts, fn($t) => str_contains($t, 'small enough to inline')));
		$this->assertTrue((bool) array_filter($texts, fn($t) => str_contains($t, 'too large to return inline')
			&& str_contains($t, 'tickets_attachments_save_to_file')
			&& str_contains($t, 'id=13')));
	}

	public function testSaveAttachmentToFile(): void
	{
		$content = "2025-01-01 12:00:00 [INFO] Application started\n";

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/attachments/14/download')))
			->willReturn(createJoomlaResponse(200, $content, [
				'Content-Type'        => 'text/plain',
				'Content-Disposition' => 'attachment; filename="debug.log"',
			]));

		$targetPath = sys_get_temp_dir() . '/mcp4joomla_test_' . uniqid() . '.log';

		try
		{
			$result = $this->attachments->saveAttachmentToFile(14, $targetPath);

			$this->assertSame($targetPath, $result['path']);
			$this->assertSame(strlen($content), $result['bytes']);
			$this->assertSame('text/plain', $result['mimeType']);
			$this->assertSame('debug.log', $result['filename']);
			$this->assertFileExists($targetPath);
			$this->assertSame($content, file_get_contents($targetPath));
		}
		finally
		{
			if (file_exists($targetPath))
			{
				unlink($targetPath);
			}
		}
	}

	public function testSaveAttachmentToFileThrowsForMissingDirectory(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('get')
			->willReturn(createJoomlaResponse(200, 'content', ['Content-Type' => 'text/plain']));

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches('/does not exist/');

		$this->attachments->saveAttachmentToFile(15, '/no/such/directory/here/file.log');
	}

	public function testDeleteAttachment(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/attachments/7')))
			->willReturn(createJoomlaResponse(204));

		$result = $this->attachments->deleteAttachment(7);

		$this->assertTrue($result);
	}
}

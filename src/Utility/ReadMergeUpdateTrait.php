<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Utility;

trait ReadMergeUpdateTrait
{
	/**
	 * Reads the current record from the API and merges incoming update data into the known writable fields.
	 *
	 * @param   HttpDecorator  $http            The HTTP client.
	 * @param   string         $uri             API endpoint URI of the record to update.
	 * @param   string|null    $expectedType    Expected JSON:API `data.type`.
	 * @param   array          $incomingData    Filtered update data from the MCP tool input.
	 * @param   array          $writableFields  Field names accepted by the update endpoint.
	 *
	 * @return  array
	 */
	private function prepareReadMergeUpdatePayload(
		HttpDecorator $http,
		string $uri,
		?string $expectedType,
		array $incomingData,
		array $writableFields
	): array
	{
		$currentResponse = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($currentResponse);

		$currentData = $this->getDataFromResponse($currentResponse, $expectedType);

		$currentAttributes = $this->normaliseAttributesToArray($this->extractAttributesForUpdate($currentData->data));
		$baselinePayload   = [];

		foreach ($writableFields as $field)
		{
			$baselinePayload[$field] = $currentAttributes[$field] ?? null;
		}

		return array_filter(array_replace($baselinePayload, $incomingData), fn($v) => $v !== null);
	}

	/**
	 * Reads the current record and recursively merges incoming data into all currently known attributes.
	 *
	 * @param   HttpDecorator  $http          The HTTP client.
	 * @param   string         $uri           API endpoint URI of the record to update.
	 * @param   string|null    $expectedType  Expected JSON:API `data.type`.
	 * @param   array          $incomingData  Filtered update data from the MCP tool input.
	 *
	 * @return  array
	 */
	private function prepareReadMergeUpdatePayloadRecursive(
		HttpDecorator $http,
		string $uri,
		?string $expectedType,
		array $incomingData
	): array
	{
		$currentResponse = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($currentResponse);

		$currentData = $this->getDataFromResponse($currentResponse, $expectedType);
		$currentAttributes = $this->normaliseAttributesToArray($this->extractAttributesForUpdate($currentData->data));

		return array_replace_recursive($currentAttributes, $incomingData);
	}

	private function extractAttributesForUpdate(object $data): object
	{
		if (isset($data->attributes) && is_object($data->attributes))
		{
			return $data->attributes;
		}

		$fallback = clone $data;
		unset($fallback->type, $fallback->id, $fallback->links, $fallback->relationships);

		if (empty((array) $fallback))
		{
			throw new \RuntimeException('Failed to process Joomla API response (no attributes in record data).');
		}

		return $fallback;
	}

	private function normaliseAttributesToArray(object $attributes): array
	{
		try
		{
			$normalised = json_decode(json_encode($attributes, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			$normalised = [];
		}

		return is_array($normalised) ? $normalised : [];
	}
}

<?php

declare(strict_types=1);

namespace AlanShahoei\LibraryManagement\Infrastructure\Persistence\Json;

use InvalidArgumentException;
use RuntimeException;

class JsonFileStorage
{
    public function __construct(private string $filePath)
    {
        $normalizedFilePath = trim($this->filePath);

        if ($normalizedFilePath === '') {
            throw new InvalidArgumentException('File path cannot be empty.');
        }

        $this->filePath = $normalizedFilePath;
    }

    public function read(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        if (!is_readable($this->filePath)) {
            throw new RuntimeException("JSON file is not readable: {$this->filePath}");
        }

        $contents = file_get_contents($this->filePath);

        if ($contents === false) {
            throw new RuntimeException("Unable to read JSON file: {$this->filePath}");
        }

        $contents = trim($contents);

        if ($contents === '') {
            return [];
        }

        $records = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($records) || !array_is_list($records)) {
            throw new RuntimeException(
                "JSON file must contain a list of records: {$this->filePath}"
            );
        }

        return $records;
    }

    public function write(array $records): void
    {
        if (!array_is_list($records)) {
            throw new InvalidArgumentException('Records must be a list.');
        }

        $directory = dirname($this->filePath);

        if (!is_dir($directory) && !mkdir($directory, recursive: true)) {
            throw new RuntimeException("Unable to create storage directory: {$directory}");
        }

        $json = json_encode(
            $records,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        if (file_put_contents($this->filePath, $json . PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException("Unable to write JSON file: {$this->filePath}");
        }
    }
}

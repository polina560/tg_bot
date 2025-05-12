<?php

/*
 * CKFinder
 * ========
 * https://ckeditor.com/ckfinder/
 * Copyright (c) 2007-2023, CKSource Holding sp. z o.o. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

namespace CKSource\CKFinder\Filesystem\File;

use CKSource\CKFinder\{CKFinder,
    Error,
    Exception\InvalidUploadException,
    Filesystem\Path,
    Image,
    ResourceType\ResourceType};
use Exception;
use League\Flysystem\FilesystemException;

/**
 * The ExistingFile class.
 *
 * Represents a file that already exists in CKFinder and can be
 * pointed using the resource type, path and file name.
 */
abstract class ExistingFile extends File
{
    /**
     * Array for errors that may occur during file processing.
     */
    protected array $errors = [];

    /**
     * File metadata.
     */
    protected array $metadata;

    /**
     * Constructor.
     *
     * @param string       $folder       Resource type relative folder.
     * @param ResourceType $resourceType File resource type.
     */
    public function __construct(
        string $fileName,
        protected string $folder,
        protected ResourceType $resourceType,
        CKFinder $app,
    ) {
        parent::__construct($fileName, $app);
    }

    /**
     * Checks if the current file folder path is valid.
     *
     * @return bool `true` if the path is valid
     */
    public function hasValidPath(): bool
    {
        return Path::isValid($this->getPath());
    }

    /**
     * Returns backend-relative folder path (i.e. a path with a prepended resource type directory).
     *
     * @return string backend-relative path
     */
    public function getPath(): string
    {
        return Path::combine($this->resourceType->getDirectory(), $this->folder);
    }

    /**
     * Returns the resource type of the file.
     */
    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    /**
     * Checks if the current file has an extension allowed in its resource type.
     *
     * @return bool `true` if the file has an allowed exception
     */
    public function hasAllowedExtension(): bool
    {
        $extension = $this->getExtension();

        return $this->resourceType->isAllowedExtension($extension);
    }

    /**
     * Checks if the current file is hidden.
     *
     * @return bool `true` if the file is hidden
     */
    public function isHidden(): bool
    {
        return $this->resourceType->getBackend()->isHiddenFile($this->getFilename());
    }

    /**
     * Checks if the current file has a hidden path (i.e. if any of the parent folders is hidden).
     *
     * @return bool `true` if the path is hidden
     */
    public function hasHiddenPath(): bool
    {
        return $this->resourceType->getBackend()->isHiddenPath($this->getPath());
    }

    /**
     * Checks if the current file exists.
     *
     * @return bool `true` if the file exists
     * @throws FilesystemException
     */
    public function exists(): bool
    {
        $filePath = $this->getFilePath();
        $backend = $this->resourceType->getBackend();
        return $backend->has($filePath);
    }

    /**
     * Returns backend-relative file path.
     *
     * @return string file path
     */
    public function getFilePath(): string
    {
        return Path::combine($this->getPath(), $this->getFilename());
    }

    /**
     * Returns file contents stream.
     *
     * @return resource contents stream
     * @throws FilesystemException
     */
    public function getContentsStream()
    {
        $filePath = $this->getFilePath();

        return $this->resourceType->getBackend()->readStream($filePath);
    }

    /**
     * Returns file contents.
     *
     * @return string contents
     * @throws FilesystemException
     */
    public function getContents(): string
    {
        $filePath = $this->getFilePath();

        return $this->resourceType->getBackend()->read($filePath);
    }

    /**
     * Sets new file contents.
     *
     * @param string      $contents file contents
     * @param string|null $filePath path to save the file
     *
     *
     * @throws Exception if content size is too big
     * @throws FilesystemException
     */
    public function save(string $contents, string $filePath = null): void
    {
        $filePath = $filePath ?: $this->getFilePath();

        $maxSize = $this->resourceType->getMaxSize();

        $contentsSize = strlen($contents);

        if ($maxSize && $contentsSize > $maxSize) {
            throw new InvalidUploadException(
                'New file contents is too big for resource type limit',
                Error::UPLOADED_TOO_BIG,
            );
        }

        $this->resourceType->getBackend()->write($filePath, $contents);
        $this->deleteThumbnails();
    }

    /**
     * Removes the thumbnail generated for the current file.
     *
     * @throws FilesystemException
     */
    public function deleteThumbnails(): void
    {
        $extension = $this->getExtension();

        if (
            Image::isSupportedExtension($extension)
            || ('bmp' === $extension && $this->config->get('thumbnails.bmpSupported'))
        ) {
            $this->resourceType
                ->getThumbnailRepository()
                ->deleteThumbnails($this->resourceType, $this->folder, $this->getFilename());
        }
    }

    /**
     * Adds an error to the array of errors of the current file.
     *
     * @param int $number error number
     *
     * @see Error
     */
    public function addError(int $number): void
    {
        $this->errors[] = [
            'number' => $number,
            'name' => $this->getFilename(),
            'type' => $this->resourceType->getName(),
            'folder' => $this->folder,
        ];
    }

    /**
     * Returns an array of errors that occurred during file processing.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Removes resized images generated for the current file.
     *
     * @throws FilesystemException
     */
    public function deleteResizedImages(): void
    {
        $extension = $this->getExtension();

        if (Image::isSupportedExtension($extension)) {
            $this->resourceType
                ->getResizedImageRepository()
                ->deleteResizedImages($this->resourceType, $this->folder, $this->getFilename());
        }
    }

    /**
     * Returns last modification time.
     *
     * @return int Unix timestamp
     * @throws FilesystemException
     */
    public function getTimestamp(): int
    {
        return $this->resourceType->getBackend()->lastModified($this->getFilePath());
    }

    /**
     * Returns file MIME type.
     *
     * @return string file MIME type
     * @throws FilesystemException
     */
    public function getMimeType(): string
    {
        return $this->resourceType->getBackend()->mimeType($this->getFilePath());
    }

    /**
     * Returns file size.
     *
     * @return int size in bytes
     * @throws FilesystemException
     */
    public function getSize(): int
    {
        return $this->resourceType->getBackend()->fileSize($this->getFilePath());
    }
}

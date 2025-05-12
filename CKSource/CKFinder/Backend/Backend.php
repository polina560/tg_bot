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

namespace CKSource\CKFinder\Backend;

use CKSource\CKFinder\{Acl\AclInterface,
    Acl\Permission,
    CKFinder,
    Config,
    Filesystem\Path,
    ResizedImage\ResizedImage,
    ResourceType\ResourceType,
    Utils};
use Exception;
use League\Flysystem\{Filesystem, FilesystemAdapter, FilesystemException};

/**
 * The Backend file system class.
 *
 * A wrapper class for League\Flysystem\Filesystem with
 * CKFinder customizations.
 */
class Backend extends Filesystem
{
    /**
     * Access Control Lists.
     */
    protected AclInterface $acl;

    /**
     * Configuration.
     */
    protected Config $ckConfig;

    /**
     * Constructor.
     *
     * @param array             $backendConfig    the backend configuration node
     * @param CKFinder          $app              the CKFinder app container
     * @param FilesystemAdapter $adapter          the adapter
     * @param array             $filesystemConfig the configuration
     */
    public function __construct(
        protected array $backendConfig,
        protected CKFinder $app,
        FilesystemAdapter $adapter,
        array $filesystemConfig = [],
    ) {
        $this->acl = $app['acl'];
        $this->ckConfig = $app['config'];

        parent::__construct($adapter, $filesystemConfig);
    }

    /**
     * Returns the name of the backend.
     *
     * @return string name of the backend
     */
    public function getName(): string
    {
        return $this->backendConfig['name'];
    }

    /**
     * Returns an array of commands that should use operation tracking.
     */
    public function getTrackedOperations(): array
    {
        return $this->backendConfig['trackedOperations'] ?? [];
    }

    /**
     * Returns a filtered list of directories for a given resource type and path.
     *
     * @throws FilesystemException
     */
    public function directories(ResourceType $resourceType, string $path = '', bool $recursive = false): array
    {
        $directoryPath = $this->buildPath($resourceType, $path);
        $contents = $this->listContents($directoryPath, $recursive)->toArray();

        return array_filter($contents, fn($v)
            => isset($v['type']) &&
            'dir' === $v['type'] &&
            !$this->isHiddenFolder(basename($v['path'])) &&
            $v['visibility'] === 'public',
        );
    }

    /**
     * Returns a path based on the resource type and the resource type relative path.
     *
     * @param ResourceType $resourceType the resource type
     * @param string       $path         the resource type relative path
     *
     * @return string path to be used with the backend adapter
     */
    public function buildPath(ResourceType $resourceType, string $path): string
    {
        return Path::combine($resourceType->getDirectory(), $path);
    }

    /**
     * Checks if the directory with a given name is hidden.
     *
     * @return bool `true` if the directory is hidden
     */
    public function isHiddenFolder(string $folderName): bool
    {
        $hideFoldersRegex = $this->ckConfig->getHideFoldersRegex();

        if ($hideFoldersRegex) {
            return (bool)preg_match($hideFoldersRegex, $folderName);
        }

        return false;
    }

    /**
     * Returns a filtered list of files for a given resource type and path.
     *
     * @throws FilesystemException
     */
    public function files(ResourceType $resourceType, string $path = '', bool $recursive = false): array
    {
        $directoryPath = $this->buildPath($resourceType, $path);
        $contents = $this->listContents($directoryPath, $recursive);

        return array_filter(
            $contents->toArray(),
            fn($v)
                => isset($v['type']) &&
                'file' === $v['type'] &&
                !$this->isHiddenFile(basename($v['path'])) &&
                $resourceType->isAllowedExtension(pathinfo($v['path'], PATHINFO_EXTENSION) ?? ''),
        );
    }

    /**
     * Checks if the file with a given name is hidden.
     *
     * @return bool `true` if the file is hidden
     */
    public function isHiddenFile(string $fileName): bool
    {
        $hideFilesRegex = $this->ckConfig->getHideFilesRegex();

        if ($hideFilesRegex) {
            return (bool)preg_match($hideFilesRegex, $fileName);
        }

        return false;
    }

    /**
     * Check if the directory for a given path contains subdirectories.
     *
     * @return bool `true` if the directory contains subdirectories
     * @throws FilesystemException
     */
    public function containsDirectories(ResourceType $resourceType, string $path = ''): bool
    {
        $directoryPath = $this->buildPath($resourceType, $path);

        // It's possible that directory may not exist yet. This is the case when very first Init command
        // is received, and resource type directories were not created yet. Some adapters will throw in
        // this case, so handle this gracefully.
        try {
            $contents = $this->listContents($directoryPath);
        } catch (Exception) {
            return false;
        }

        foreach ($contents as $entry) {
            if ('dir' === $entry['type'] &&
                !$this->isHiddenFolder(basename($entry['path'])) &&
                $this->acl->isAllowed(
                    $resourceType->getName(),
                    Path::combine($path, basename($entry['path'])),
                    Permission::FOLDER_VIEW,
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the path is hidden.
     *
     * @return bool `true` if the path is hidden
     */
    public function isHiddenPath(string $path): bool
    {
        $pathParts = explode('/', trim($path, '/'));
        if ($pathParts) {
            foreach ($pathParts as $part) {
                if ($this->isHiddenFolder($part)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Deletes a directory.
     *
     * @throws FilesystemException
     */
    public function deleteDir($dirname): void
    {
        parent::deleteDirectory($dirname);
    }

    /**
     * Returns a URL to a file.
     *
     * If the useProxyCommand option is set for a backend, the returned
     * URL will point to the CKFinder connector Proxy command.
     *
     * @param ResourceType $resourceType      the file resource type
     * @param string       $folderPath        the resource-type relative folder path
     * @param string       $fileName          the file name
     * @param string|null  $thumbnailFileName the thumbnail file name - if the file is a thumbnail
     *
     * @return null|string URL to a file or `null` if the backend does not support it
     */
    public function getFileUrl(
        ResourceType $resourceType,
        string $folderPath,
        string $fileName,
        string $thumbnailFileName = null,
    ): ?string {
        if ($this->usesProxyCommand()) {
            $connectorUrl = $this->app->getConnectorUrl();

            $queryParameters = [
                'command' => 'Proxy',
                'type' => $resourceType->getName(),
                'currentFolder' => $folderPath,
                'fileName' => $fileName,
            ];

            if ($thumbnailFileName) {
                $queryParameters['thumbnail'] = $thumbnailFileName;
            }

            $proxyCacheLifetime = (int)$this->ckConfig->get('cache.proxyCommand');

            if ($proxyCacheLifetime > 0) {
                $queryParameters['cache'] = $proxyCacheLifetime;
            }

            return $connectorUrl . '?' . http_build_query($queryParameters, '', '&');
        }

        $path = $thumbnailFileName
            ? Path::combine(
                $resourceType->getDirectory(),
                $folderPath,
                ResizedImage::DIR,
                $fileName,
                $thumbnailFileName,
            )
            : Path::combine($resourceType->getDirectory(), $folderPath, $fileName);

        if (isset($this->backendConfig['baseUrl'])) {
            return Path::combine($this->backendConfig['baseUrl'], Utils::encodeURLParts($path));
        }

        return null;
    }

    /**
     * Returns a Boolean value telling if the backend uses the Proxy command.
     */
    public function usesProxyCommand(): bool
    {
        return isset($this->backendConfig['useProxyCommand']) && $this->backendConfig['useProxyCommand'];
    }

    /**
     * Returns the base URL used to build the direct URL to files stored
     * in this backend.
     *
     * @return null|string base URL or `null` if the base URL for a backend
     *                     was not defined
     */
    public function getBaseUrl(): ?string
    {
        if (isset($this->backendConfig['baseUrl']) && !$this->usesProxyCommand()) {
            return $this->backendConfig['baseUrl'];
        }

        return null;
    }

    /**
     * Returns the root directory defined for the backend.
     *
     * @return null|string root directory or `null` if the root directory
     *                     was not defined
     */
    public function getRootDirectory(): ?string
    {
        return $this->backendConfig['root'] ?? null;
    }

    /**
     * Renames the object for a given path.
     *
     * @throws FilesystemException
     */
    public function rename($path, $newPath): void
    {
        parent::move($path, $newPath);
    }

    /**
     * Checks if a backend contains a directory.
     *
     * The Backend::has() method is not always reliable and may
     * work differently for various adapters. Checking for directory
     * should be done with this method.
     *
     * @throws FilesystemException
     */
    public function hasDirectory(string $directoryPath): bool
    {
        $pathParts = array_filter(explode('/', $directoryPath), 'strlen');
        $dirName = array_pop($pathParts);

        try {
            $contents = $this->listContents(implode('/', $pathParts));
        } catch (Exception) {
            return false;
        }

        foreach ($contents as $c) {
            if (isset($c['type'], $c['path']) && 'dir' === $c['type'] && basename($c['path']) === $dirName) {
                return true;
            }
        }
        return false;
    }
}

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

namespace CKSource\CKFinder\Cache\Adapter;

use CKSource\CKFinder\{Backend\Backend, Filesystem\Path};
use League\Flysystem\{FilesystemException};

/**
 * The BackendAdapter class.
 */
class BackendAdapter implements AdapterInterface
{
    /**
     * Constructor.
     */
    public function __construct(protected Backend $backend, protected ?string $cachePath = null)
    {
    }

    /**
     * Sets the value in cache under given key.
     *
     * @throws FilesystemException
     */
    public function set(string $key, mixed $value): void
    {
        $this->backend->write($this->createCachePath($key), serialize($value));
    }

    /**
     * Creates backend-relative path for cache file for given key.
     */
    public function createCachePath(string $key, bool $prefix = false): string
    {
        return Path::combine($this->cachePath, trim($key, '/') . ($prefix ? '' : '.cache'));
    }

    /**
     * Returns value under given key from cache.
     *
     * @throws FilesystemException
     */
    public function get(string $key): ?array
    {
        $path = $this->createCachePath($key);

        if (!$this->backend->has($path)) {
            return null;
        }

        return unserialize($this->backend->read($path));
    }

    /**
     * Deletes value under given key  from cache.
     *
     * @return bool true if successful
     * @throws FilesystemException
     */
    public function delete(string $key): bool
    {
        $path = $this->createCachePath($key);

        if (!$this->backend->has($path)) {
            return false;
        }

        $this->backend->delete($path);

        $dirs = explode('/', dirname($path));

        do {
            $dirPath = implode('/', $dirs);

            if (!empty($this->backend->listContents($dirPath)->toArray())) {
                break;
            }

            $this->backend->deleteDir($dirPath);
            array_pop($dirs);
        } while (!empty($dirs));
        return true;
    }

    /**
     * Deletes all cache entries with given key prefix.
     *
     * @throws FilesystemException
     */
    public function deleteByPrefix(string $keyPrefix): void
    {
        $path = $this->createCachePath($keyPrefix, true);
        if ($this->backend->hasDirectory($path)) {
            $this->backend->deleteDir($path);
        }
    }

    /**
     * Changes prefix for all entries given key prefix.
     *
     * @throws FilesystemException
     */
    public function changePrefix(string $sourcePrefix, string $targetPrefix): void
    {
        $sourceCachePath = $this->createCachePath($sourcePrefix, true);

        if (!$this->backend->hasDirectory($sourceCachePath)) {
            return;
        }

        $targetCachePath = $this->createCachePath($targetPrefix, true);

        $this->backend->rename($sourceCachePath, $targetCachePath);
    }
}

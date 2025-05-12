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

namespace CKSource\CKFinder\Cache;

use CKSource\CKFinder\Cache\Adapter\AdapterInterface;

/**
 * The CacheManager class.
 */
class CacheManager
{
    /**
     * Constructor.
     */
    public function __construct(protected AdapterInterface $adapter)
    {
    }

    /**
     * Sets the value in cache for a given key.
     */
    public function set(string $key, mixed $value): void
    {
        $this->adapter->set($key, $value);
    }

    /**
     * Returns the value for a given key from cache.
     */
    public function get(string $key): array
    {
        return $this->adapter->get($key);
    }

    /**
     * Moves the value for a given key to another key.
     *
     * @return bool `true` if successful
     */
    public function move(string $sourceKey, string $targetKey): bool
    {
        return $this->copy($sourceKey, $targetKey) && $this->delete($sourceKey);
    }

    /**
     * Copies the value for a given key to another key.
     */
    public function copy(string $sourceKey, string $targetKey): void
    {
        $value = $this->adapter->get($sourceKey);

        if (is_null($value)) {
            return;
        }

        $this->adapter->set($targetKey, $value);
    }

    /**
     * Deletes the value under a given key from cache.
     *
     * @return bool `true` if successful
     */
    public function delete(string $key): bool
    {
        return $this->adapter->delete($key);
    }

    /**
     * Deletes all cache entries with a given key prefix.
     */
    public function deleteByPrefix(string $keyPrefix): void
    {
        $this->adapter->deleteByPrefix($keyPrefix);
    }

    /**
     * Changes the prefix for all entries given a key prefix.
     */
    public function changePrefix(string $sourcePrefix, string $targetPrefix): void
    {
        $this->adapter->changePrefix($sourcePrefix, $targetPrefix);
    }
}

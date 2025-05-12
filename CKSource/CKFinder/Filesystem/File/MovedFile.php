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

use Exception;
use CKSource\CKFinder\{Filesystem\Path};
use League\Flysystem\FilesystemException;

/**
 * The MovedFile class.
 *
 * Represents the moved file.
 */
class MovedFile extends CopiedFile
{
    /**
     * Moves the current file.
     *
     * @throws Exception
     * @throws FilesystemException
     */
    public function doMove(): void
    {
        $originalFilePath = $this->getFilePath();
        $originalFileName = $this->getFilename(); // Save original file name - it may be autorenamed when copied
        $this->doCopy();
        if (empty($this->errors)) {
            // Remove source file
            $this->deleteThumbnails();
            $this->resourceType->getResizedImageRepository()->deleteResizedImages(
                $this->resourceType,
                $this->folder,
                $originalFileName
            );
            $this->getCache()->delete(Path::combine($this->resourceType->getName(), $this->folder, $originalFileName));

            $this->resourceType->getBackend()->delete($originalFilePath);
        }
    }
}

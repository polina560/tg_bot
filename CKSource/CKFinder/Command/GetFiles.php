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

namespace CKSource\CKFinder\Command;

use CKSource\CKFinder\{Acl\Permission, Utils};
use CKSource\CKFinder\Filesystem\Folder\WorkingFolder;
use League\Flysystem\FilesystemException;
use stdClass;

class GetFiles extends CommandAbstract
{
    protected array $requires = [Permission::FILE_VIEW];

    /**
     * @throws FilesystemException
     */
    public function execute(WorkingFolder $workingFolder): stdClass
    {
        $data = new stdClass();
        $files = $workingFolder->listFiles();

        $data->files = [];

        foreach ($files as $file) {
            $fileObject = [
                'name' => basename($file['path']),
                'date' => Utils::formatDate($file['lastModified']),
                'size' => Utils::formatSize($file['fileSize']),
            ];

            $data->files[] = $fileObject;
        }

        // Sort files
        usort($data->files, static function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        return $data;
    }
}

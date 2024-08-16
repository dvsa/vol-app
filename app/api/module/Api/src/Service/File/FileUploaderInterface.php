<?php

namespace Dvsa\Olcs\Api\Service\File;

use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;

/**
 * File Upload Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface FileUploaderInterface
{
    /**
     * Upload file by identifier
     *
     * @param string           $identifier File identifier
     * @param ContentStoreFile $file       File
     *
     * @return ContentStoreFile
     */
    public function upload($identifier, ContentStoreFile $file);

    /**
     * Process the file download
     *
     * @param string $identifier File identifier
     *
     * @return ContentStoreFile
     */
    public function download($identifier);

    /**
     * Process the file removal
     *
     * @param string $identifier File identifier
     *
     * @return ContentStoreFile
     */
    public function remove($identifier);
}

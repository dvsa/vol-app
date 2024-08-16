<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;

/**
 * Uploader Aware Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface UploaderAwareInterface
{
    public function setUploader(ContentStoreFileUploader $uploader);

    /**
     * @return ContentStoreFileUploader
     */
    public function getUploader();
}

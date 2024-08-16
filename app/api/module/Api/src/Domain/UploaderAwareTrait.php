<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;

/**
 * Uploader Aware Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait UploaderAwareTrait
{
    /**
     * @var ContentStoreFileUploader
     */
    private $uploader;

    public function setUploader(ContentStoreFileUploader $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * @return ContentStoreFileUploader
     */
    public function getUploader()
    {
        return $this->uploader;
    }
}

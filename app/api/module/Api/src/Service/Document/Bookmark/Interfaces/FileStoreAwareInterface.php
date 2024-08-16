<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces;

/**
 * File Store Aware Interface
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
interface FileStoreAwareInterface
{
    /**
     * @NOTE: FileStoreService isn't a real interface so isn't type hinted, but in the future
     * we could implement it. It should just expose some basic read and write methods and
     * could internally be backed by whatever storage it wants (disk, memoryetc)
     */
    public function setFileStore(/* FileStoreService */ $fileStore);

    public function getFileStore();
}

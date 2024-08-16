<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Unpublished publication records
 */
class Unpublished extends AbstractQuery
{
    protected $publicationSection;
    protected $publication;

    /**
     * @return mixed
     */
    public function getPublicationSection()
    {
        return $this->publicationSection;
    }

    /**
     * @return mixed
     */
    public function getPublication()
    {
        return $this->publication;
    }
}

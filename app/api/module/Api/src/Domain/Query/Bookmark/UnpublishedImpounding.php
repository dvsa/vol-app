<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

/**
 * Unpublished Impounding
 */
class UnpublishedImpounding extends Unpublished
{
    protected $impounding;

    /**
     * @return int
     */
    public function getImpounding()
    {
        return $this->impounding;
    }
}

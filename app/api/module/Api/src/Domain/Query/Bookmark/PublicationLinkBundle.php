<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * PublicationLink Bundle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PublicationLinkBundle extends AbstractQuery
{
    protected $busReg;

    protected $bundle = [];

    /**
     * @return mixed
     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * @return mixed
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}

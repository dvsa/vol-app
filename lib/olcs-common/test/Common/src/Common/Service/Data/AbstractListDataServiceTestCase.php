<?php

namespace CommonTest\Common\Service\Data;

use Common\Service\Data\AbstractListDataServiceServices;

/**
 * AbstractListDataServiceTestCase
 */
class AbstractListDataServiceTestCase extends AbstractDataServiceTestCase
{
    /** @var  AbstractListDataServiceServices */
    protected $abstractListDataServiceServices;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->abstractListDataServiceServices = new AbstractListDataServiceServices(
            $this->abstractDataServiceServices
        );
    }
}

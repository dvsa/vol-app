<?php

namespace OlcsTest\Service\Data;

use Common\Service\Data\Application as ApplicationDataService;
use Common\Service\Data\Licence as LicenceDataService;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Mockery as m;
use Olcs\Service\Data\AbstractPublicInquiryDataServices;

/**
 * AbstractPublicInquiryDataTestCase
 */
class AbstractPublicInquiryDataTestCase extends AbstractDataServiceTestCase
{
    /** @var  AbstractPublicInquiryDataServices */
    protected $abstractPublicInquiryDataServices;

    /** @var ApplicationDataService */
    protected $applicationDataService;

    /** @var LicenceDataService */
    protected $licenceDataService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->applicationDataService = m::mock(ApplicationDataService::class);
        $this->licenceDataService = m::mock(LicenceDataService::class);

        $this->abstractPublicInquiryDataServices = new AbstractPublicInquiryDataServices(
            $this->abstractDataServiceServices,
            $this->applicationDataService,
            $this->licenceDataService
        );
    }
}

<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\Application as ApplicationDataService;
use Common\Service\Data\Licence as LicenceDataService;

/**
 * AbstractPublicInquiryDataServices
 */
class AbstractPublicInquiryDataServices
{
    /** @var AbstractDataServiceServices */
    protected $abstractDataServiceServices;

    /** @var ApplicationDataService */
    protected $applicationDataService;

    /** @var LicenceDataService */
    protected $licenceDataService;

    /**
     * Create service instance
     *
     *
     * @return AbstractPublicInquiryDataServices
     */
    public function __construct(
        AbstractDataServiceServices $abstractDataServiceServices,
        ApplicationDataService $applicationDataService,
        LicenceDataService $licenceDataService
    ) {
        $this->abstractDataServiceServices = $abstractDataServiceServices;
        $this->applicationDataService = $applicationDataService;
        $this->licenceDataService = $licenceDataService;
    }

    /**
     * Return the AbstractDataServiceServices
     *
     * @return AbstractDataServiceServices
     */
    public function getAbstractDataServiceServices(): AbstractDataServiceServices
    {
        return $this->abstractDataServiceServices;
    }

    /**
     * Return the ApplicationDataService
     *
     * @return ApplicationDataService
     */
    public function getApplicationDataService(): ApplicationDataService
    {
        return $this->applicationDataService;
    }

    /**
     * Return the LicenceDataService
     *
     * @return LicenceDataService
     */
    public function getLicenceDataService(): LicenceDataService
    {
        return $this->licenceDataService;
    }
}

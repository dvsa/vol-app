<?php

namespace Common\Service\Data;

/**
 * Class ApplicationOperatingCentre
 *
 * @package Olcs\Service\Data
 */
class ApplicationOperatingCentre extends AbstractDataService implements ListDataInterface
{
    use ApplicationServiceTrait;

    public const OUTPUT_TYPE_FULL = 1;

    public const OUTPUT_TYPE_PARTIAL = 2;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var int
     */
    protected $outputType = self::OUTPUT_TYPE_FULL;

    /**
     * Create service instance
     *
     *
     * @return ApplicationOperatingCentre
     */
    public function __construct(
        AbstractDataServiceServices $abstractDataServiceServices,
        Application $applicationDataService
    ) {
        parent::__construct($abstractDataServiceServices);
        $this->setApplicationService($applicationDataService);
    }

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function fetchListOptions($context = null, $useGroups = false)
    {
        $id = $this->getId();

        if (is_null($this->getData($id))) {
            $data = [];
            $rawData =  $this->getApplicationService()->fetchOperatingCentreData($this->getId());

            if (is_array($rawData['operatingCentres'])) {
                $outputType = $this->getOutputType();

                $fields = ($outputType == self::OUTPUT_TYPE_PARTIAL)
                    ? ['addressLine1', 'town']
                    : ['addressLine1', 'addressLine2', 'addressLine3', 'addressLine4', 'town', 'postcode'];

                foreach ($rawData['operatingCentres'] as $applicationOperatingCentre) {
                    $addressString = '';

                    foreach ($fields as $field) {
                        $addressString .= empty($applicationOperatingCentre['operatingCentre']['address'][$field]) ?
                            '' : $applicationOperatingCentre['operatingCentre']['address'][$field] . ', ';
                    }

                    $data[$applicationOperatingCentre['operatingCentre']['id']] = substr($addressString, 0, -2);
                }
            }

            $this->setData($id, $data);
        }

        return $this->getData($id);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->getApplicationService()->getId();
    }

    /**
     * Get output type
     *
     * @return int
     */
    public function getOutputType()
    {
        return $this->outputType;
    }

    /**
     * Set output type
     *
     * @param int $outputType Output type
     *
     * @return $this
     */
    public function setOutputType($outputType)
    {
        $this->outputType = $outputType;

        return $this;
    }
}

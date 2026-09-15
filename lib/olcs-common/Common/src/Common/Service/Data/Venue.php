<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\Interfaces\ListData;
use Dvsa\Olcs\Transfer\Query\Venue\VenueList;

/**
 * Class Venue
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Venue extends AbstractDataService implements ListData
{
    use LicenceServiceTrait;

    /**
     * Create service instance
     *
     *
     * @return RefData
     */
    public function __construct(
        AbstractDataServiceServices $abstractDataServiceServices,
        Licence $licenceDataService
    ) {
        parent::__construct($abstractDataServiceServices);
        $this->setLicenceService($licenceDataService);
    }

    /**
     * Format data
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $datum['name'];
        }

        return $optionData;
    }

    /**
     * Fetch list options
     *
     * @param string $category  Category
     * @param bool   $useGroups Use groups
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function fetchListOptions($category, $useGroups = false)
    {
        $context = $this->getLicenceContext();

        $data = $this->fetchListData($context);

        if (!$data) {
            return [];
        }

        return $this->formatData($data);
    }

    /**
     * Fetch list data
     *
     * @param array $params Params
     *
     * @return array
     */
    public function fetchListData($params)
    {
        if (is_null($this->getData('Venue'))) {
            $dtoData = VenueList::create(
                [
                    'trafficArea' => empty($params['trafficArea']) ? null : $params['trafficArea']
                ]
            );

            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('Venue', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('Venue', $response->getResult()['results']);
            }
        }

        return $this->getData('Venue');
    }
}

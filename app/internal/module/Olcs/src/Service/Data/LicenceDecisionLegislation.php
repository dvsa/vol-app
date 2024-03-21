<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\Licence;
use Common\Service\Data\LicenceServiceTrait;
use Common\Service\Data\ListDataInterface;
use Dvsa\Olcs\Transfer\Query\Decision\DecisionList as DecisionListDto;

/**
 * Class LicenceDecisionLegislation
 *
 * @package Olcs\Service\Data
 */
class LicenceDecisionLegislation extends AbstractDataService implements ListDataInterface
{
    use LicenceServiceTrait;

    /**
     * @var string
     */
    protected $sort = 'sectionCode';

    /**
     * @var string
     */
    protected $order = 'ASC';

    /**
     * Create service instance
     *
     * @param AbstractDataServiceServices $abstractDataServiceServices
     * @param Licence $licenceDataService
     *
     * @return LicenceDecisionLegislation
     */
    public function __construct(
        AbstractDataServiceServices $abstractDataServiceServices,
        Licence $licenceDataService
    ) {
        parent::__construct($abstractDataServiceServices);
        $this->setLicenceService($licenceDataService);
    }

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        /**
         * For Info:
         * LicenceService getId returns the licence Id from the route. For refrerence, this is because the controller
         * implements the LicenceControllerInterface which is configured to attach the licence Listener.
         * The listener looks for a licence Id and sets it.
         */
        $context = empty($context) ? $this->getLicenceContext() : $context;

        $data = $this->fetchListData($context);

        if ($useGroups) {
            return $this->formatDataForGroups($data);
        }

        return $this->formatData($data);
    }

    /**
     * Fetch list data
     *
     * @param array $context Context
     *
     * @return array
     * @throw DataServiceException
     */
    public function fetchListData($context)
    {
        if (is_null($this->getData('licenceDecisionLegislation'))) {
            $params = array_merge(
                $context,
                [
                    'sort' => $this->sort,
                    'order' => $this->order
                ]
            );

            $dtoData = DecisionListDto::create($params);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('licenceDecisionLegislation', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('licenceDecisionLegislation', $response->getResult()['results']);
            }
        }

        return $this->getData('licenceDecisionLegislation');
    }

    /**
     * Format data
     *
     * @param array $data Data
     *
     * @return array
     */
    protected function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $datum['sectionCode'] . ' - ' . $datum['description'];
        }

        return $optionData;
    }

    /**
     * Format for groups
     *
     * @param array $data Data
     *
     * @return array
     */
    protected function formatDataForGroups(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $parentId = $datum['sectionCode'];
            if (!isset($optionData[$parentId])) {
                $optionData[$parentId] = [
                    'label' => $datum['sectionCode'],
                    'options' => []
                ];
            }
            $optionData[$parentId]['options'][$datum['id']] = $datum['description'];
        }

        return $optionData;
    }
}

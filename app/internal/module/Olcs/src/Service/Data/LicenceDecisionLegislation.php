<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractDataService;
use Dvsa\Olcs\Transfer\Query\Decision\DecisionList as DecisionListDto;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\Service\Data\LicenceServiceTrait;

/**
 * Class LicenceDecisionLegislation
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class LicenceDecisionLegislation extends AbstractDataService implements ListDataInterface, FactoryInterface
{
    use LicenceServiceTrait;

    protected $sort = 'sectionCode';
    protected $order = 'ASC';

    /**
     * @param mixed $context
     * @param bool $useGroups
     * @return array|void
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        /**
         * For Info:
         * LicenceService getId returns the licence Id from the route. For refrerence, this is because the controller
         * implements the LicenceControllerInterface which is configured to attach the licence Listener.
         * The listener looks for a licence Id and sets it.
         */
        $context = empty($context)? $this->getLicenceContext() : $context;

        $data = $this->fetchListData($context);

        if ($useGroups) {
            return $this->formatDataForGroups($data);
        }

        return $this->formatData($data);
    }

    /**
     * Fetch decision list data
     *
     * @return array
     * @throws UnexpectedResponseException
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
                throw new UnexpectedResponseException('unknown-error');
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
     * @param array $data
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
     * @param array $data
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

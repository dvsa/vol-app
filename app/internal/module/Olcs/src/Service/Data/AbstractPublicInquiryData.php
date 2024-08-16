<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ApplicationServiceTrait;
use Common\Service\Data\LicenceServiceTrait;
use Common\Service\Data\ListDataInterface;

/**
 * Class Abstract Public Inquiry Data
 *
 * @package Olcs\Service\Data
 */
abstract class AbstractPublicInquiryData extends AbstractDataService implements ListDataInterface
{
    use LicenceServiceTrait;
    use ApplicationServiceTrait;

    /** @var string */
    protected $listDto;
    /** @var string */
    protected $sort;
    /** @var string */
    protected $order;

    /**
     * Create service instance
     *
     * @param AbstractPublicInquiryDataServices $abstractPublicInquiryDataServices
     *
     * @return AbstractPublicInquiryData
     */
    public function __construct(AbstractPublicInquiryDataServices $abstractPublicInquiryDataServices)
    {
        parent::__construct($abstractPublicInquiryDataServices->getAbstractDataServiceServices());

        $this->setApplicationService($abstractPublicInquiryDataServices->getApplicationDataService());
        $this->setLicenceService($abstractPublicInquiryDataServices->getLicenceDataService());
    }

    /**
     * Fetch back a set of options for a drop down list, context passed is parameters which may need to be passed to the
     * back end to filter the result set returned, use groups when specified should, cause this method to return the
     * data as a multi dimensioned array suitable for display in opt-groups. It is permissible for the method to ignore
     * this flag if the data doesn't allow for option groups to be constructed.
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $params = empty($context) ? $this->getLicenceContext() : array_merge($context, $this->getLicenceContext());

        $licenceId = $this->getLicenceService()->getId();

        if ($licenceId === null) {
            $params['goodsOrPsv'] = 'NULL';
        } elseif (empty($params['goodsOrPsv'])) {
            //  if application not loaded
            if ($this->getApplicationService()->getId() === null) {
                $licenceData = $this->getLicenceService()->fetchLicenceData($licenceId);

                if (!empty($licenceData['applications'])) {
                    $this->getApplicationService()->setId($licenceData['applications'][0]['id']);
                }
            }

            // use application's goodsOrPsv instead
            $params['goodsOrPsv'] = $this->getApplicationContext()['goodsOrPsv'];
        }

        $data = $this->fetchPublicInquiryData($params);

        if (!is_array($data)) {
            return [];
        }

        if ($useGroups) {
            return $this->formatDataForGroups($data);
        }

        return $this->formatData($data);
    }

    /**
     * Fetch public inquiry data
     *
     * @param array $params Params
     *
     * @return array
     */
    public function fetchPublicInquiryData($params)
    {
        if (is_null($this->getData('pid'))) {
            $result = $this->fetchListData($params);

            if (isset($result['results'])) {
                $this->setData('pid', $result['results']);
            }
        }

        return $this->getData('pid');
    }

    /**
     * Method to make the backend call to retrieve the PI data based on listDto set in the child class
     *
     * @param array $params Params
     *
     * @return array
     * @throws DataServiceException
     */
    public function fetchListData(array $params)
    {
        /** @var \Dvsa\Olcs\Transfer\Query\QueryInterface $listDto */
        $listDto = $this->listDto;

        $dtoData = $listDto::create(
            array_merge(
                $params,
                [
                    'sort' => $this->sort,
                    'order' => $this->order
                ]
            )
        );

        $response = $this->handleQuery($dtoData);

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        return $response->getResult();
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
            $optionData[$datum['id']] = $datum['description'];
        }

        return $optionData;
    }

    /**
     * Format data for groups
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatDataForGroups($data)
    {
        $groups = [];
        $optionData = [];

        foreach ($data as $datum) {
            if (isset($datum['sectionCode'])) {
                $groups[$datum['sectionCode']][] = $datum;
            }
        }

        foreach ($groups as $parent => $groupData) {
            $optionData[$parent]['options'] = $this->formatData($groupData);
            $optionData[$parent]['label'] = $parent;
        }

        return $optionData;
    }
}

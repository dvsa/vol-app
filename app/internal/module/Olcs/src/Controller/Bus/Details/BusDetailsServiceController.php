<?php

/**
 * Bus Details Service Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

/**
 * Bus Details Service Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsServiceController extends BusDetailsController
{
    protected $item = 'service';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-service-number-and-type';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
            )
        )
    );

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'subsidised' => array(
                'id'
            ),
            'busNoticePeriod' => array(
                'id'
            ),
            'busServiceTypes' => array(
                'properties' => 'ALL'
            ),
            'otherServices',
            'parent'
        )
    );

    /**
     * Array of form fields to disable if this is EBSR
     */
    protected $disableFormFields = array(
        'serviceNo',
        'startPoint',
        'finishPoint',
        'via',
        'busServiceTypes',
        'otherDetails',
        'receivedDate',
        'effectiveDate',
        'endDate',
        'busNoticePeriod',
    );

    protected $inlineScripts = ['bus-servicenumbers'];

    public function processSave($data)
    {
        $shortNoticeService = $this->getServiceLocator()->get('Common\Service\ShortNotice');

        $data['fields']['isShortNotice'] = 'N';

        if ($shortNoticeService->isShortNotice($data['fields'])) {
            $data['fields']['isShortNotice'] = 'Y';
        }

        $data['fields']['otherServices'] = array_filter(
            $data['fields']['otherServices'],
            array($this, 'filterServices')
        );

        return parent::processSave($data);
    }

    protected function filterServices($item)
    {
        return isset($item['serviceNo']) && !empty($item['serviceNo']);
    }
}

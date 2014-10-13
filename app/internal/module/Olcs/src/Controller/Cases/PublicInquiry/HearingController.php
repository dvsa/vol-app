<?php

namespace Olcs\Controller\Cases\PublicInquiry;

/**
 * Class HearingController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class HearingController extends PublicInquiryController
{
    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'pi';

    /**
     * Identifier key
     *
     * @var string
     */
    protected $identifierKey = 'pi';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'PublicInquiryHearing';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'PiHearing';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'case',
        'pi'
    ];

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = [
        'children' => [
            'piVenue' => [
                'properties' => [
                    'id'
                ],
            ],
            'presidingTc' => [
                'properties' => [
                    'id'
                ],
            ],
            'presidedByRole' => [
                'properties' => [
                    'id'
                ],
            ],
        ]
    ];

    /**
     * Get data for form
     *
     * @return array
     */
    public function getDataForForm()
    {
        $data = parent::getDataForForm();
        $data['fields']['pi'] = $this->getFromRoute('pi');

        return $data;
    }

    /**
     * Overrides the parent, make sure there's nothing there shouldn't be in the optional fields
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processSave($data)
    {
        if ($data['fields']['piVenue'] != 'other') {
            $data['fields']['piVenueOther'] = null;
        }

        if ($data['fields']['isCancelled'] != 'Y') {
            $data['fields']['cancelledReason'] = null;
            $data['fields']['cancelledDate'] = null;
        }

        if ($data['fields']['isAdjourned'] != 'Y') {
            $data['fields']['adjournedReason'] = null;
            $data['fields']['adjournedDate'] = null;
        }

        return parent::processSave($data);
    }
}

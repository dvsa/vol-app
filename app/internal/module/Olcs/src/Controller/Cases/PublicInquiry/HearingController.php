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
    protected function getDataForForm()
    {
        $data = parent::getDataForForm();
        //echo'<pre>';
//print_r($data); die();
        $data['fields']['pi'] = $this->getFromRoute('pi');

        return $data;
    }
}

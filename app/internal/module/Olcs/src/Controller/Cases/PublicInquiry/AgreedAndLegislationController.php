<?php

namespace Olcs\Controller\Cases\PublicInquiry;

/**
 * Class AgreedAndLegislationController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class AgreedAndLegislationController extends PublicInquiryController
{
    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'PublicInquiryAgreedAndLegislation';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
            ),
            'values' => array(
                'piStatus' => 'pi_s_reg'
            )
        )
    );

    /**
     * @return array
     */
    public function getFormDefaults()
    {
        return ['agreedDate' => date('Y-m-d')];
    }
}

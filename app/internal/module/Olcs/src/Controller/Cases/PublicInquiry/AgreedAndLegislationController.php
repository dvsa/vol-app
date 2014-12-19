<?php

namespace Olcs\Controller\Cases\PublicInquiry;
use Olcs\Controller\Interfaces\CaseControllerInterface;

/**
 * Class AgreedAndLegislationController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class AgreedAndLegislationController extends PublicInquiryController implements CaseControllerInterface
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
    public function processLoad($data)
    {
        if (empty($data)) {
            return ['fields' => ['agreedDate' => date('Y-m-d')]];
        }

        return parent::processLoad($data);
    }
}

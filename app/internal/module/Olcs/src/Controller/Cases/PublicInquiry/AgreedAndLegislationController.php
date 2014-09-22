<?php

namespace Olcs\Controller\Cases\PublicInquiry;

// Olcs
use Common\Controller\type;
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

use Zend\View\Model\ViewModel;

/**
 * Case Complaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
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

    public function getFormDefaults()
    {
        return ['agreedDate' => date('Y-m-d')];
    }
}

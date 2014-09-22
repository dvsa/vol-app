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

    public function processDataMapForSave($oldData, $map = array(), $section = 'main')
    {
        $data = parent::processDataMapForSave($oldData, $map, $section);
        if (!isset($data['case']) || empty($data['case'])) {
            $data['case'] = $this->params()->fromRoute('case');
        }
        return $data;
    }
}

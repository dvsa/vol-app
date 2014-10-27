<?php

namespace Olcs\Controller\Cases\PublicInquiry;

/**
 * Class SlaController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class SlaController extends PublicInquiryController
{
    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'PublicInquirySla';

    /**
     * @var array
     */
    protected $inlineScripts = ['showhideinput', 'pi-sla'];

    public function processLoad($data)
    {
        $data = parent::processLoad($data);

        $data = $this->formatData($data);

        $this->getServiceLocator()->get('Common\Service\Data\Sla')->setContext('pi', $data);

        return $data;
    }

    public function formatData($data)
    {
        if (isset($data['piHearings']) && is_array($data['piHearings']) && count($data['piHearings']) > 0) {

            $hearing = array_pop($data['piHearings']);

            if ($hearing['isAdjourned'] != 'Y' && $hearing['isCancelled'] != 'Y') {

                $data['hearingDate'] = $hearing['hearingDate'];
            }

        }

        return $data;
    }

    public function onInvalidPost($form)
    {
        $this->processLoad($this->loadCurrent());
    }
}

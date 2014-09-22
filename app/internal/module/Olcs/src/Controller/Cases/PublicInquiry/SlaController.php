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
    protected $inlineScripts = ['pi-sla'];
}

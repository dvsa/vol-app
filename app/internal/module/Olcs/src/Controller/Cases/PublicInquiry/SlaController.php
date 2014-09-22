<?php

namespace Olcs\Controller\Cases\PublicInquiry;

class SlaController extends PublicInquiryController
{
    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'PublicInquirySla';

    protected $inlineScripts = ['pi-sla'];
}

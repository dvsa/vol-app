<?php

namespace Olcs\Controller\Cases\PublicInquiry;

/**
 * Class RegisterDecisionController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class RegisterDecisionController extends PublicInquiryController
{
    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'PublicInquiryRegisterDecision';

    protected $inlineScripts = ['shared/definition'];
}

<?php

namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;

class IrhpNotEligibleController extends AbstractSelfserveController
{
    protected $templateConfig = [
        'generic' => 'permits/not-eligible'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'title' => 'permits.page.irhp-not-eligible.title',
            'browserTitle' => 'permits.page.irhp-not-eligible.browser.title',
        ]
    ];
}

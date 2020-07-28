<?php

namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;

class IrhpNoLicencesController extends AbstractSelfserveController
{
    protected $templateConfig = [
        'generic' => 'permits/no-licences'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'title' => 'permits.page.irhp-no-licences.title',
            'browserTitle' => 'permits.page.irhp-no-licences.browser.title',
        ]
    ];
}

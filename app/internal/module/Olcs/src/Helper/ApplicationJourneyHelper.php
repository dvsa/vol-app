<?php

/**
 * ApplicationJourney Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Helper;

use Zend\View\Model\ViewModel;

/**
 * ApplicationJourney Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationJourneyHelper
{
    public function render($layout)
    {
        $header = $this->getPageHeader();

        return $this->getBaseLayout($header, $layout);
    }

    public function renderNavigation()
    {
        
    }

    protected function getBaseLayout($header, $layout)
    {
        $base = new ViewModel();
        $base->setTemplate('layout/base')
            ->setTerminal(true)
            ->setVariables($layout->getVariables())
            ->addChild($header, 'header')
            ->addChild($layout, 'content');

        return $base;
    }

    protected function getPageHeader()
    {
        $header = new ViewModel(
            array(
                'pageTitle' => 'Licence number/Application number',
                'pageSubTitle' => 'Company name ltd'
            )
        );

        $header->setTemplate('layout/partials/header');

        return $header;
    }
}

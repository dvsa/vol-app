<?php

/**
 * ApplicationJourney Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Helper;

use Zend\View\Model\ViewModel;
use Zend\ServiceManager;
use Common\Util\RestCallTrait;

/**
 * ApplicationJourney Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationJourneyHelper implements ServiceManager\ServiceLocatorAwareInterface
{
    use ServiceManager\ServiceLocatorAwareTrait,
        RestCallTrait;

    /**
     * Setup the layouts and then render
     *
     * @param ViewModel $content
     * @parm int $applicationId
     * @return ViewModel
     */
    public function render($content, $applicationId)
    {
        $header = $this->renderPageHeader($applicationId);

        $layout = new ViewModel();
        $layout->setTemplate('layout/application');
        $layout->addChild($content, 'content');

        return $this->renderBaseLayout($header, $layout);
    }

    /**
     * Render the base layout
     *
     * @param ViewModel $header
     * @param ViewModel $layout
     * @return ViewModel
     */
    protected function renderBaseLayout($header, $layout)
    {
        $base = new ViewModel();
        $base->setTemplate('layout/base')
            ->setTerminal(true)
            ->setVariables($layout->getVariables())
            ->addChild($header, 'header')
            ->addChild($layout, 'content');

        return $base;
    }

    /**
     * Render the page header
     *
     * @return ViewModel
     */
    protected function renderPageHeader($applicationId)
    {
        $bundle = array(
            'properties' => array('id'),
            'children' => array(
                'status' => array(
                    'properties' => array('id')
                ),
                'licence' => array(
                    'properties' => array(
                        'id',
                        'licNo'
                    ),
                    'children' => array(
                        'organisation' => array(
                            'properties' => array(
                                'name'
                            )
                        )
                    )
                )
            )
        );

        $results = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);

        $licenceNo = isset($results['licence']['licNo']) ? $results['licence']['licNo'] : '';

        if (!empty($licenceNo)) {
            $url = $this->getServiceLocator()->get('viewhelpermanager')->get('url');
            $licenceUrl = $url('licence', array('licence' => $results['licence']['id']));

            $licenceNo = '<a href="' . $licenceUrl . '">' . $licenceNo . '</a>';
        }

        $pageTitle = $licenceNo;

        if (!empty($pageTitle)) {
            $pageTitle .= ' / ' . $applicationId;
        }

        $pageSubTitle = isset($results['licence']['organisation']['name'])
            ? $results['licence']['organisation']['name']
            : '';

        switch ($results['status']['id']) {
            case 'apsts_new':
                $pageSubTitle .= '<span class="page-header__status suspended">New</span>';
        }

        $header = new ViewModel(
            array(
                'pageTitle' => $pageTitle,
                'pageSubTitle' => $pageSubTitle
            )
        );

        $header->setTemplate('layout/partials/header');

        return $header;
    }
}

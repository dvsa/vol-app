<?php

/**
 * Licence Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Traits;

/**
 * Licence Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait LicenceController
{
    protected $licences = array();

    /**
     * Get view with licence
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    public function getViewWithLicence($variables = array())
    {
        $licence = $this->getLicence();

        if ($licence['goodsOrPsv']['id'] == 'lcat_gv') {
            $this->getServiceLocator()->get('Navigation')->findOneBy('id', 'licence_bus')->setVisible(0);
        }

        $variables['licence'] = $licence;

        $view = $this->getView($variables);

        $this->pageTitle = $licence['licNo'];
        $this->pageSubTitle = $licence['goodsOrPsv']['id'] . ', ' . $licence['licenceType']['id']
            . ', ' . $licence['status']['id'];

        return $view;
    }

    /**
     * Gets the licence by ID.
     *
     * @param integer $id
     * @return array
     */
    public function getLicence($id = null)
    {
        if (is_null($id)) {
            $id = $this->getFromRoute('licence');
        }

        if (!isset($this->licences[$id])) {
            $bundle = array(
                'properties' => 'ALL',
                'children' => array(
                    'status' => array(
                        'properties' => array('id')
                    ),
                    'goodsOrPsv' => array(
                        'properties' => array('id')
                    ),
                    'licenceType' => array(
                        'properties' => array('id')
                    ),
                    'trafficArea' => array(
                        'properties' => 'ALL'
                    ),
                    'organisation' => array(
                        'properties' => 'ALL'
                    )
                )
            );

            $this->licences[$id] = $this->makeRestCall('Licence', 'GET', array('id' => $id), $bundle);
        }

        return $this->licences[$id];
    }
}

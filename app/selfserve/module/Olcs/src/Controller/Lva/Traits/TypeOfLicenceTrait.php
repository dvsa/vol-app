<?php

/**
 * Shared logic between Type Of Licence controllers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Zend\Form\Form;
use Olcs\View\Model\Section;

/**
 * Shared logic between Type Of Licence controllers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait TypeOfLicenceTrait
{
    /**
     * Format data for save
     *
     * @param array $data
     * @return array
     */
    private function formatDataForSave($data)
    {
        return array(
            'version' => $data['version'],
            'niFlag' => $data['type-of-licence']['operator-location'],
            'goodsOrPsv' => $data['type-of-licence']['operator-type'],
            'licenceType' => $data['type-of-licence']['licence-type']
        );
    }

    /**
     * Format data for form
     *
     * @param array $data
     * @return array
     */
    private function formatDataForForm($data)
    {
        return array(
            'version' => $data['version'],
            'type-of-licence' => array(
                'operator-location' => $data['niFlag'],
                'operator-type' => $data['goodsOrPsv'],
                'licence-type' => $data['licenceType']
            )
        );
    }

    /**
     * Get type of licence form
     *
     * @return \Zend\Form\Form
     */
    private function getTypeOfLicenceForm()
    {
        return $this->getHelperService('FormHelper')->createForm('Lva\TypeOfLicence');
    }

    /**
     * Get section view
     *
     * @param \Zend\Form\Form $form
     * @return Section
     */
    private function getSectionView(Form $form)
    {
        // @TODO in a custom view model instead?
        $this->getServiceLocator()
            ->get('Script')
            ->loadFile('type-of-licence');

        return new Section(
            [
                'title' => 'Type of licence',
                'form' => $form
            ]
        );
    }
}

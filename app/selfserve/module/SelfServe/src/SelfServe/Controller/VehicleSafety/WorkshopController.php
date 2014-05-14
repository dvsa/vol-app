<?php

/**
 * WorkshopController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\VehicleSafety;

use Zend\View\Model\ViewModel;

/**
 * WorkshopController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class WorkshopController extends AbstractVehicleSafetyController
{
    /**
     * Add workshop
     *
     * @return ViewModel
     */
    public function addAction()
    {
        if ($this->isButtonPressed('cancel')) {

            return $this->backToSafety();
        }

        $applicationId = $this->getApplicationId();

        $data = array(
            'data' => array(
                'applicationId' => $applicationId
            )
        );

        $form = $this->generateFormWithData('vehicle-safety-workshop', 'processAddWorkshop', $data);

        $form->get('data')->setLabel('Add safety inspection provider');

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/layout/form');

        return $this->renderLayoutWithSubSections($view, 'safety');
    }
    /**
     * Edit workshop
     *
     * @return ViewModel
     */
    public function editAction()
    {
        if ($this->isButtonPressed('cancel')) {

            return $this->backToSafety();
        }

        $id = $this->getFromRoute('id');

        $bundle = array(
            'properties' => array(
                'id',
                'version',
                'isExternal'
            ),
            'children' => array(
                'contactDetails' => array(
                    'properties' => array(
                        'id',
                        'fao',
                        'version'
                    ),
                    'children' => array(
                        'address' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'addressLine1',
                                'addressLine2',
                                'addressLine3',
                                'city',
                                'country',
                                'postcode'
                            )
                        )
                    )
                )
            )
        );

        $result = $this->makeRestCall('Workshop', 'GET', array('id' => $id), $bundle);

        $data = $this->formatDataForForm($result);

        $form = $this->generateFormWithData('vehicle-safety-workshop', 'processEditWorkshop', $data);

        $form->get('data')->setLabel('Update safety inspection provider');

        $form->get('form-actions')->remove('addAnother');

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/layout/form');

        return $this->renderLayoutWithSubSections($view, 'safety');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        $id = $this->getFromRoute('id');

        $this->makeRestCall('Workshop', 'DELETE', array('id' => $id));

        return $this->backToSafety();
    }

    /**
     * Process add workshop
     *
     * @param array $data
     * @return mixed
     */
    public function processAddWorkshop($data)
    {
        $data = $this->processAddressData($data, 'address');

        $contactDetails = array(
            'fao' => $data['data']['fao'],
            'addresses' => $data['addresses'],
            'contactDetailsType' => 'contact_type.work'
        );

        $result = $this->makeRestCall('ContactDetails', 'POST', $contactDetails);

        $workshopDetails = array(
            'application' => $data['data']['applicationId'],
            'isExternal' => $data['data']['isExternal'],
            'contactDetails' => $result['id']
        );

        $result = $this->makeRestCall('Workshop', 'POST', $workshopDetails);

        if (!empty($result)) {

            if ($this->isButtonPressed('addAnother')) {

                return $this->redirectToRoute(null, array(), array(), true);
            }

            return $this->backToSafety();
        }
    }

    /**
     * Process editing a workshop
     *
     * @param array $data
     */
    public function processEditWorkshop($data)
    {
        $data = $this->processAddressData($data, 'address');

        $contactDetails = array(
            'id' => $data['data']['contactDetails.id'],
            'version' => $data['data']['contactDetails.version'],
            'fao' => $data['data']['fao'],
            'addresses' => $data['addresses']
        );

        $this->makeRestCall('ContactDetails', 'PUT', $contactDetails);

        $workshopDetails = array(
            'id' => $data['data']['id'],
            'version' => $data['data']['version'],
            'isExternal' => $data['data']['isExternal']
        );

        $this->makeRestCall('Workshop', 'PUT', $workshopDetails);

        return $this->backToSafety();
    }

    /**
     * Format data for form
     *
     * @param array $result
     * @return array
     */
    private function formatDataForForm($result)
    {
        $data = array(
            'data' => array(
                'id' => $result['id'],
                'version' => $result['version'],
                'isExternal' => $result['isExternal'],
                'fao' => $result['contactDetails']['fao'],
                'contactDetails.id' => $result['contactDetails']['id'],
                'contactDetails.version' => $result['contactDetails']['version']
            ),
            'address' => $result['contactDetails']['address']
        );

        $data['address']['country'] = 'country.' . $data['address']['country'];

        return $data;
    }
}

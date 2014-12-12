<?php
/**
 * Scanning Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Scanning Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class ScanningController extends AbstractActionController
{
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            // do some stuff
            $data = (array)$this->getRequest()->getPost();
        } else {
            $data = [
                'details' => [
                    'category' => 1,
                    'subCategory' => 85
                ]
            ];
        }

        $category = $data['details']['category'];
        $subCategory = $data['details']['subCategory'];

        $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Olcs\Service\Data\SubCategory')
            ->setCategory($category);

        $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Olcs\Service\Data\SubCategoryDescription')
            ->setSubCategory($subCategory);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('Scanning');

        if ($this->getRequest()->isPost()) {

            $form->setData($data);

            if ($form->isValid()) {
                $entity = $this->getServiceLocator()
                    ->get('Processing\Entity')
                    ->findEntityForCategory(
                        $data['details']['category'],
                        $data['details']['entityIdentifier']
                    );

                if ($entity === false) {
                    // @TODO attach an error message to the entityIdentifier input
                } else {
                    // all good
                }
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('forms/scanning');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form');
        return $this->renderView($view, 'Scanning');
    }
}

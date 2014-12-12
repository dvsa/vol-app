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

        $category    = $data['details']['category'];
        $subCategory = $data['details']['subCategory'];

        $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Olcs\Service\Data\SubCategory')
            ->setCategory($category);

        $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Olcs\Service\Data\SubCategoryDescription')
            ->setSubCategory($subCategory);

        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Scanning');

        $this->getServiceLocator()->get('Script')->loadFile('forms/scanning');

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
                    $form->get('details')
                        ->get('entityIdentifier')
                        ->setMessages(['scanning.error.entity.' . $category]);
                } else {
                    $this->getServiceLocator()
                        ->get('Helper\FlashMessenger')
                        ->addSuccessMessage('scanning.message.success');

                    // The AC says these should be reset to their defaults, but
                    // this presents an issue; description depends on sub category,
                    // but we don't know what the "default" sub category is in order
                    // to re-fetch the correct list of descriptions...
                    $data['details']['subCategory'] = null;
                    $data['details']['description'] = null;
                    $data['details']['otherDescription'] = null;
                    $form->setData($data);

                    // ... so we load in some extra JS which will fire off our cascade
                    // input, which in turn will populate the list of descriptions
                    $this->getServiceLocator()->get('Script')->loadFile('scanning-success');
                }
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form');
        return $this->renderView($view, 'Scanning');
    }
}

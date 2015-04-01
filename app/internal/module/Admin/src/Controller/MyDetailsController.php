<?php

/**
 * My Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;
use Common\Exception\ResourceNotFoundException;
use Common\Exception\BadRequestException;

/**
 * My Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class MyDetailsController extends CrudAbstract
{
    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'my-details';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'admin-my-details';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'User';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-my-account';

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'User';

    /**
     * Edit action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $user = $this->getUserService();
        $data = $user->fetchMyDetailsFormData($this->getLoggedInUser());
        $form = $this->generateFormWithData($this->getFormName(), $this->getFormCallback(), $data);

        $view = $this->getView();
        $this->setPlaceholder('form', $form);
        $view->setTemplate('pages/crud-form');

        return $this->renderView($view);
    }

    /**
     * Form has passed validation so call the user service to save the record
     *
     * @param array $data
     * @return mixed
     */
    public function processSave($data)
    {
        try {
            $id = $this->getUserService()->save($data);
            $this->addSuccessMessage('User updated successfully');
        } catch (BadRequestException $e) {
            $this->addErrorMessage($e->getMessage());
            $id = false;
        } catch (ResourceNotFoundException $e) {
            $this->addErrorMessage($e->getMessage());
            $id = false;
        }

        return $id;
    }

    /**
     * Redirect action (makes default page for the section the details form)
     *
     * @return \Zend\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirectToRouteAjax(
            'admin-dashboard/admin-my-account/details',
            ['action'=>'edit'],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * Gets the user service
     *
     * @return mixed
     */
    private function getUserService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\User');
    }
}

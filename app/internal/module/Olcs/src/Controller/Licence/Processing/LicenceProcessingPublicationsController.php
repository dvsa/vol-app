<?php

/**
 * Licence Processing Publications Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Zend\View\Model\ViewModel;
use Common\Exception\ResourceNotFoundException;
use Common\Exception\BadRequestException;
use Common\Exception\DataServiceException;

/**
 * Licence Processing Publications Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingPublicationsController extends AbstractLicenceProcessingController
{
    protected $section = 'publications';

    public function indexAction()
    {
        $this->checkForCrudAction(null, [], 'id');

        $view = $this->getViewWithLicence();

        $requestQuery = $this->getRequest()->getQuery();
        $requestArray = $requestQuery->toArray();

        $defaultParams = [
            'page' => 1,
            'limit' => 10,
            'sort' => 'createdOn',
            'order' => 'DESC',
            'licence' => $this->params()->fromRoute('licence')
        ];

        $filters = array_merge(
            $defaultParams,
            $requestArray
        );

        $data = $this->getService()->fetchList($filters);

        if (!isset($data['url'])) {
            $data['url'] = $this->getPluginManager()->get('url');
        }

        $table = $this->getServiceLocator()->get('Table')->buildTable(
            'publication',
            $data,
            array_merge(
                $filters,
                array('query' => $requestQuery)
            ),
            false
        );

        $view->setVariables(['table' => $table]);
        $view->setTemplate('licence/processing/layout');

        return $this->renderView($view);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        $publication = $this->getService();

        try {
            $publication->delete($id);
            $this->addErrorMessage('Record deleted successfully');
        } catch (DataServiceException $e) {
            $this->addErrorMessage($e->getMessage());
        } catch (BadRequestException $e) {
            $this->addErrorMessage($e->getMessage());
        } catch (ResourceNotFoundException $e) {
            $this->addErrorMessage($e->getMessage());
        }

        return $this->redirectToIndex();
    }

    /**
     * @return string
     */
    protected function getService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\PublicationLink');
    }
}

<?php

/**
 * Transport Manager Processing Publication Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Processing;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;

/**
 * Transport Manager Processing Publication Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class PublicationController extends AbstractTransportManagerProcessingController
{
    /**
     * @var string
     */
    protected $section = 'processing-history';


    /**
     * Publications action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->checkForCrudAction(null, [], 'id');

        $view = $this->getViewWithTm([]);

        $requestQuery = $this->getRequest()->getQuery();
        $requestArray = $requestQuery->toArray();

        $defaultParams = [
            'page'             => 1,
            'limit'            => 10,
            'sort'             => 'createdOn',
            'order'            => 'DESC',
            'transportManager' => $this->params()->fromRoute('transportManager')
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
            'tm.publication',
            $data,
            array_merge(
                $filters,
                array('query' => $requestQuery)
            ),
            false
        );

        $view->setVariables(['table' => $table]);
        $view->setTemplate('partials/table');

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
     * Edit action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $id = $this->getFromRoute('id');
        $service = $this->getService();
        $publication = $service->fetchOne($id);

        $readOnly = [
            'typeArea' => $publication['publication']['pubType'] . ' / ' .
                $publication['publication']['trafficArea']['name'],
            'publicationNo' => $publication['publication']['publicationNo'],
            'status' => $publication['publication']['pubStatus']['description'],
            'section' => $publication['publicationSection']['description'],
            'trafficArea' => $publication['publication']['trafficArea']['name'],
            'publicationDate' => date('d/m/Y', strtotime($publication['publication']['pubDate']))
        ];

        $textFields = [
            'text1' => $publication['text1'],
            'text2' => $publication['text2'],
            'text3' => $publication['text3']
        ];

        if ($publication['publication']['pubStatus']['id'] == 'pub_s_new') {
            $base = [
                'id' => $publication['id'],
                'version' => $publication['version']
            ];

            $data = [
                'fields' => array_merge($textFields, $base)
            ];

            $form = 'Publication';
        } else {
            $data = [
                'readOnlyText' => $textFields
            ];

            $form = 'PublicationNotNew';
        }

        $data['readOnly'] = $readOnly;

        $form = $this->generateFormWithData($form, 'processSave', $data);

        //having read only fields means that they aren't populated in the event of a post so we need to do it here
        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $data,
                (array)$this->params()->fromPost(),
                $this->fieldValues
            );

            $form->setData($data);
        }

        $view = $this->getViewWithTm();

        $this->getServiceLocator()->get('viewHelperManager')
            ->get('placeholder')
            ->getContainer('form')
            ->set($form);

        $view->setTemplate('pages/crud-form');

        return $this->renderView($view);
    }

    /**
     * Specific save processing functionality
     *
     * @param array $data
     * @return int
     */
    public function processSave($data)
    {
        $saveData = [
            'text1' => $data['fields']['text1'],
            'text2' => $data['fields']['text2'],
            'text3' => $data['fields']['text3'],
            'id' => $data['fields']['id'],
            'version' => $data['fields']['version']
        ];

        $publication = $this->getService();
        $publication->update($data['fields']['id'], $saveData);

        return $this->redirectToIndex();
    }

    public function addAction()
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\PublicationLink');
    }
}

<?php

/**
 * Note controller
 * Licence note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Licence\Processing;

use Common\Controller\CrudInterface;
use Olcs\Controller\Traits\DeleteActionTrait;
use Zend\View\Model\ViewModel;

/**
 * Note controller
 * Licence note search and display
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingNoteController extends AbstractLicenceProcessingController implements CrudInterface
{
    use DeleteActionTrait;

    protected $section = 'notes';

    public function indexAction()
    {
        $licenceId = $this->getFromRoute('licence');

        $this->checkForCrudAction('licence/processing/notes', array('licence' => $licenceId), 'id');

        $bundle = $this->getBundle();

        $searchData = [];
        $searchData['url'] = $this->url();
        $searchData['licence'] = $licenceId;
        $searchData['page'] = $this->getFromRoute('page', 1);
        $searchData['sort'] = $this->getFromRoute('sort', 'priority');
        $searchData['order'] = $this->getFromRoute('order', 'desc');
        $searchData['limit'] = $this->getFromRoute('limit', 10);

        $resultData = $this->makeRestCall('Note', 'GET', $searchData, $bundle);

        $table = $this->buildTable('note', $resultData, $searchData);

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('licence/processing/notes/index');

        return $this->renderView($view);
    }

    public function addAction()
    {
        $licenceId = $this->getFromRoute('licence');
        $noteType = $this->getFromRoute('noteType');

        $form = $this->generateFormWithData(
            'licence-notes',
            'processAddNotes',
            array(
                'licence' => $licenceId,
                'noteType' => $noteType
            )
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('licence/processing/notes/form');

        return $this->renderView($view);
    }

    public function editAction()
    {
        $licenceId = $this->getFromRoute('licence');

        $form = $this->generateFormWithData(
            'licence-notes',
            'processEditNotes',
            array(
                'licence' => $licenceId,
            )
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('licence/processing/notes/form');

        return $this->renderView($view);
    }

    public function processAddNotes($data)
    {
        $data = array_merge($data, $data['main']);
        $data['createdBy'] = $this->getLoggedInUser();
        $data['lastModifiedBy'] = $this->getLoggedInUser();

        $field = $this->getIdField($data['noteType']);

        if ($field) {
            $data[$field] = $data['linkedId'];
        }

        unset($data['linkedId'])

        $result = $this->processAdd($data, 'Note');

        if (isset($result['id'])) {
            return $this->redirectToIndex();
        }

        return $this->redirectToRoute('licence/processing/note', ['action' => 'add'], [], true);
    }

    public function processEditNotes($data)
    {
        $data = array_merge($data, $data['main']);
        $data['lastModifiedBy'] = $this->getLoggedInUser();

        $result = $this->processEdit($data, 'Note');

        if (empty($result)) {
            return $this->redirectToIndex();
        }

        return $this->redirectToRoute('licence/processing/note', ['action' => 'edit'], [], true);
    }

    public function getBundle()
    {
        return [
            'children' => [
                'createdBy' => [
                    'properties' => [
                        'name'
                    ]
                ],
                'noteType' => [
                    'properties' => [
                        'description'
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns the field name used for linking the id to the appropriate record type
     *
     * @param $noteType
     * @return string
     */
    private function getIdField($noteType)
    {
        $field = '';

        switch ($noteType) {
            case 'note_t_app':
                $field = 'application';
                break;
            case 'note_t_irfo_gv':
                $field = 'irfoGvPermit';
                break;
            case 'note_t_irfo_psv':
                $field = 'irfoPsvAuth';
                break;
            case 'note_t_case':
                $field = 'case';
                break;
            case 'note_t_bus':
                $field = 'busReg';
                break;
        }

        return $field;
    }

    public function getDeleteServiceName()
    {
        return 'Note';
    }


}
<?php

namespace Olcs\Controller\Sla;

use Dvsa\Olcs\Transfer\Command\System\CreateSlaTargetDate as CreateDto;
use Dvsa\Olcs\Transfer\Command\System\UpdateSlaTargetDate as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Document\Document;
use Dvsa\Olcs\Transfer\Query\System\SlaTargetDate as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Data\Mapper\SlaTargetDate as Mapper;
use Olcs\Form\Model\Form\SlaTargetDate as Form;

abstract class AbstractSlaTargetDateController extends AbstractInternalController
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = '';

    protected $routeIdentifier = 'slaId';

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;
    // 'id' => 'complaint', to => from
    protected $itemParams = ['entityId', 'entityType', 'entityDescription'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add Sla Target Date';
    protected $editContentTitle = 'Edit Sla Target Date';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Form data for the add form.
     *
     * Format is name => value
     * name => "route" means get value from route,
     * see conviction controller
     *
     * @var array
     */
    protected $defaultData = [
        'entityType' => 'route',
        'entityId' => 'route'
    ];

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;
    protected $deleteModalTitle = 'internal.delete-action-trait.title';

    /**
     * Variables for controlling the delete action.
     * Format is: required => supplied
     */
    protected $deleteParams = ['id' => 'slaId'];

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions']
    ];

    protected function getEntityType()
    {
        return $this->entityType;
    }

    public function addSlaAction()
    {
        return parent::addAction();
    }

    public function editSlaAction()
    {
        return parent::editAction();
    }

    /**
     * Alter Form to add the entity description to the form.
     *
     * @param  \Common\Controller\Form $form
     * @param  array                   $initialData
     * @return \Common\Controller\Form
     */
    public function alterFormForAddSla($form, $initialData)
    {
        $entity = $this->loadEntity($initialData['fields']['entityId']);

        $form->get('fields')
            ->get('entityTypeHtml')
            ->setValue($entity['description']);

        return $form;
    }

    /**
     * Load the entity and return
     *
     * @param  $id
     * @return array|mixed
     */
    private function loadEntity($id)
    {
        $documentDto = Document::class;
        $query = $documentDto::create(['id' => $id]);

        $response = $this->handleQuery($query);

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelperService->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            if (isset($data)) {
                return $data;
            }
        }
    }
}

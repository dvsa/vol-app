<?php

namespace Olcs\Controller\Operator;

use Common\RefData;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\LicenceVehicle\CreateUnlicensedOperatorLicenceVehicle as CreateDto;
use Dvsa\Olcs\Transfer\Command\LicenceVehicle\DeleteUnlicensedOperatorLicenceVehicle as DeleteDto;
use Dvsa\Olcs\Transfer\Command\LicenceVehicle\UpdateUnlicensedOperatorLicenceVehicle as UpdateDto;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehicle as ItemDto;
use Dvsa\Olcs\Transfer\Query\Operator\UnlicensedVehicles as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Data\Mapper\UnlicensedOperatorLicenceVehicle as Mapper;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;

class UnlicensedOperatorVehiclesController extends AbstractInternalController implements
    OperatorControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'unlicensed_operator_vehicles';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'priority';
    protected $tableName = 'unlicensed-vehicles';
    protected $listDto = ListDto::class;
    protected $listVars = ['organisation'];

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'pages/form';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = ItemDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = ['organisation', 'id' => 'id'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add vehicle';
    protected $editContentTitle = 'Edit vehicle';

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
        'organisation' => AddFormDefaultData::FROM_ROUTE,
    ];

    protected $routeIdentifier = 'id';

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;

    protected $inlineScripts = [
        'indexAction' => ['forms/filter', 'table-actions']
    ];

    /**
     * Alter table presentation depending on operator type
     *
     * @param  TableBuilder $table
     * @param  array $data
     * @return TableBuilder
     */
    protected function alterTable($table, $data)
    {
        $columnToRemove = $data['extra']['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_PSV
            ? 'weight'
            : 'type';

        $table->removeColumn($columnToRemove);

        return $table;
    }

    public function addAction()
    {
        return $this->add(
            $this->getAddFormClass(),
            new AddFormDefaultData($this->defaultData),
            $this->createCommand,
            $this->mapperClass,
            $this->editViewTemplate,
            'Created record',
            $this->addContentTitle
        );
    }

    public function editAction()
    {
        return $this->edit(
            $this->getEditFormClass(),
            $this->itemDto,
            new GenericItem($this->itemParams),
            $this->updateCommand,
            $this->mapperClass,
            $this->editViewTemplate,
            'Updated record',
            $this->editContentTitle
        );
    }

    private function getAddFormClass()
    {
        if ($this->getGoodsOrPsv() === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return \Olcs\Form\Model\Form\AddUnlicensedGoodsVehicle::class;
        }

        return \Olcs\Form\Model\Form\AddUnlicensedPsvVehicle::class;
    }

    private function getEditFormClass()
    {
        if ($this->getGoodsOrPsv() === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return \Olcs\Form\Model\Form\EditUnlicensedGoodsVehicle::class;
        }

        return \Olcs\Form\Model\Form\EditUnlicensedPsvVehicle::class;
    }

    /**
     * @return string
     */
    private function getGoodsOrPsv()
    {
        $paramProvider = new GenericList($this->listVars, $this->defaultTableSortField);
        $paramProvider->setParams($this->plugin('params'));
        $response = $this->handleQuery(ListDto::create($paramProvider->provideParameters()));

        $data = $response->getResult();

        return $data['extra']['goodsOrPsv']['id'];
    }
}

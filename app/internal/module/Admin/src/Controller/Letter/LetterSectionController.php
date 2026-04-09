<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Admin\Data\Mapper\Letter\LetterSection as LetterSectionMapper;
use Admin\Data\Mapper\Letter\LetterSectionEditContent as LetterSectionEditContentMapper;
use Admin\Data\Mapper\Letter\LetterSectionVariantAdd as LetterSectionVariantAddMapper;
use Admin\Data\Mapper\Letter\LetterSectionVariantEdit as LetterSectionVariantEditMapper;
use Admin\Form\Model\Form\Letter\LetterSection as LetterSectionForm;
use Admin\Form\Model\Form\Letter\LetterSectionEditContent as LetterSectionEditContentForm;
use Admin\Form\Model\Form\Letter\LetterSectionVariantAdd as LetterSectionVariantAddForm;
use Admin\Form\Model\Form\Letter\LetterSectionVariantEdit as LetterSectionVariantEditForm;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSection\Create as CreateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSection\Update as UpdateDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSection\Delete as DeleteDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSectionVariant\Create as CreateVariantDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSectionVariant\Update as UpdateVariantDTO;
use Dvsa\Olcs\Transfer\Command\Letter\LetterSectionVariant\Delete as DeleteVariantDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterSection\Get as ItemDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterSection\GetList as ListDTO;
use Dvsa\Olcs\Transfer\Query\Letter\LetterSectionVariant\Get as VariantItemDTO;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;

class LetterSectionController extends AbstractInternalController implements LeftViewProvider
{
    protected $tableName = 'admin-letter-section';
    protected $defaultTableSortField = 'sectionKey';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $itemParams = ['id'];

    protected $formClass = LetterSectionForm::class;
    protected $addFormClass = LetterSectionForm::class;
    protected $mapperClass = LetterSectionMapper::class;

    protected $createCommand = CreateDTO::class;
    protected $updateCommand = UpdateDTO::class;
    protected $deleteCommand = DeleteDTO::class;

    protected $addContentTitle = 'Add Letter Section';
    protected $editContentTitle = 'Edit Letter Section (Creates New Version)';

    protected $deleteModalTitle = 'Remove Letter Section';
    protected $deleteConfirmMessage = 'Are you sure you want to remove this letter section?';
    protected $deleteSuccessMessage = 'The letter section has been removed';

    protected $addSuccessMessage = 'Letter section created successfully';
    protected $editSuccessMessage = 'Letter section updated successfully (new version created)';

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/letter-section'],
        'editAction' => ['forms/letter-section'],
        'editContentAction' => ['forms/letter-section'],
        'editVariantAction' => ['forms/letter-section'],
    ];

    protected $redirectConfig = [
        'editcontent' => [
            'action' => 'details',
            'routeMap' => ['id' => 'id'],
            'reUseParams' => false,
        ],
        'addvariant' => [
            'action' => 'details',
            'routeMap' => ['id' => 'id'],
            'reUseParams' => false,
        ],
        'editvariant' => [
            'action' => 'details',
            'routeMap' => ['id' => 'id'],
            'reUseParams' => false,
        ],
        'deletevariant' => [
            'action' => 'details',
            'routeMap' => ['id' => 'id'],
            'reUseParams' => false,
        ],
    ];

    /**
     * Detail page for a letter section - shows metadata, default content, variants, and version history
     */
    #[\Override]
    public function detailsAction()
    {
        $id = $this->params('id');
        if (!$id) {
            return $this->notFoundAction();
        }

        $response = $this->handleQuery(ItemDTO::create(['id' => $id]));
        if (!$response->isOk()) {
            $this->flashMessengerHelperService->addErrorMessage('Section not found');
            return $this->redirect()->toRouteAjax('admin-dashboard/admin-letter-section');
        }

        $data = $response->getResult();

        // Build variant display data and extract version history from default variant
        $variants = [];
        $versions = [];
        foreach ($data['variants'] ?? [] as $variant) {
            // Identify default variant (all null conditions) -- extract its versions
            $isDefault = empty($variant['goodsOrPsv'])
                && $variant['isVariation'] === null
                && empty($variant['isNi'])
                && empty($variant['organisationType'])
                && empty($variant['letterChoice']);

            if ($isDefault) {
                $versions = $variant['versions'] ?? [];
                continue;
            }
            $variants[] = [
                'id' => $variant['id'],
                'goodsOrPsv' => $variant['goodsOrPsv']['description'] ?? $variant['goodsOrPsv']['id'] ?? 'Any',
                'isVariation' => ($variant['isVariation'] !== null && $variant['isVariation'] !== '')
                    ? ($variant['isVariation'] ? 'Variation' : 'New Application')
                    : 'Any',
                'isNi' => ($variant['isNi'] !== null && $variant['isNi'] !== '')
                    ? ($variant['isNi'] ? 'NI' : 'GB')
                    : 'Any',
                'organisationType' => $variant['organisationType']['description'] ?? $variant['organisationType']['id'] ?? 'Any',
                'letterChoice' => $variant['letterChoice']['label'] ?? 'None',
            ];
        }

        $view = new ViewModel([
            'section' => $data,
            'currentVersion' => $data['currentVersion'] ?? [],
            'variants' => $variants,
            'versions' => $versions,
            'sectionId' => $id,
        ]);
        $view->setTemplate('admin/letter-section/details');

        $this->placeholder()->setPlaceholder(
            'contentTitle',
            'Letter Section: ' . ($data['currentVersion']['name'] ?? $data['sectionKey'] ?? '')
        );

        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Edit default content action - modal form for editing just the default content
     */
    public function editContentAction()
    {
        return $this->edit(
            LetterSectionEditContentForm::class,
            $this->itemDto,
            new GenericItem($this->itemParams),
            UpdateDTO::class,
            LetterSectionEditContentMapper::class,
            $this->editViewTemplate,
            'Default content updated successfully',
            'Edit Default Content'
        );
    }

    /**
     * Version history action - display all versions of a section
     */
    public function versionHistoryAction()
    {
        $id = $this->params()->fromRoute('id');

        // Get the section with all versions
        $response = $this->handleQuery(
            ItemDTO::create(['id' => $id])
        );

        if (!$response->isOk()) {
            $this->flashMessenger()->addErrorMessage('Unable to load version history');
            return $this->redirect()->toRoute('admin-dashboard/letter-management/letter-section');
        }

        $data = $response->getResult();

        $view = new ViewModel([
            'section' => $data,
            'versions' => $data['versions'] ?? [],
            'currentVersionId' => $data['currentVersion']['id'] ?? null,
        ]);

        $view->setTemplate('admin/letter-section/version-history');

        return $view;
    }

    /**
     * Add variant action - renders add variant form (conditions only, no EditorJS)
     */
    public function addVariantAction()
    {
        return $this->add(
            LetterSectionVariantAddForm::class,
            new GenericItem(['sectionId' => 'id']),
            CreateVariantDTO::class,
            LetterSectionVariantAddMapper::class,
            $this->editViewTemplate,
            'Variant added successfully',
            'Add Section Variant'
        );
    }

    /**
     * Edit variant action - renders edit variant form with EditorJS
     */
    public function editVariantAction()
    {
        return $this->edit(
            LetterSectionVariantEditForm::class,
            VariantItemDTO::class,
            new GenericItem(['id' => 'variant']),
            UpdateVariantDTO::class,
            LetterSectionVariantEditMapper::class,
            $this->editViewTemplate,
            'Variant updated successfully',
            'Edit Section Variant'
        );
    }

    /**
     * Delete variant action - confirmation + delete
     */
    public function deleteVariantAction()
    {
        return $this->confirmCommand(
            new ConfirmItem(['id' => 'variant']),
            DeleteVariantDTO::class,
            'Delete Variant',
            'Are you sure you want to remove this variant?',
            'Variant removed successfully'
        );
    }

    #[\Override]
    public function getLeftView(): ViewModel
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/letter-management',
                'navigationTitle' => 'Letter Management',
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }
}

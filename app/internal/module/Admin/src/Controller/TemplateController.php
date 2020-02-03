<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Query\Template\PreviewTemplateSource;
use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\Template\TemplateSource as ItemDto;
use Dvsa\Olcs\Transfer\Query\Template\AvailableTemplates as ListDto;
use Dvsa\Olcs\Transfer\Command\Template\UpdateTemplateSource as UpdateDto;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Admin\Data\Mapper\Template as Mapper;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Admin\Form\Model\Form\TemplateEdit;
use Admin\Form\Model\Form\TemplateFilter;
use Zend\View\Model\JsonModel;

/**
 * Email Template admin controller
 */
class TemplateController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'editAction' => ['forms/template-modal'],
    ];

    protected $tableName = 'admin-email-templates';
    protected $defaultTableSortField = 'description';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $updateCommand = UpdateDto::class;

    protected $navigationId = 'admin-dashboard/templates';

    protected $filterForm = TemplateFilter::class;
    protected $formClass = TemplateEdit::class;
    protected $mapperClass = Mapper::class;

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/templates',
                'navigationTitle' => 'Templates'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Set any email template category filter choice from querystring into List DTO params
     *
     * @param array $parameters parameters
     *
     * @return array
     */
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['emailTemplateCategory'] = $this->params()->fromQuery('emailTemplateCategory');
        return $parameters;
    }

    /**
     * Uses description field from Item response to add title to Edit modal
     *
     * @param Form $form
     * @param array $formData
     * @return Form
     */
    public function alterFormForEdit(Form $form, Array $formData)
    {
        $this->placeholder()->setPlaceholder('contentTitle', 'Edit: '.$formData['description']);

        $form->get('jsonUrl')
            ->setValue(
                $this->url()->fromRoute(
                    'admin-dashboard/admin-email-templates',
                    [
                        'action' => 'previewTemplate'
                    ]
                )
            );

        return $form;
    }

    public function previewTemplateAction()
    {
        $request = $this->getRequest();
        $postData = $request->getPost();

        $response = $this->handleQuery(
            PreviewTemplateSource::create(
                [
                    'id' => $postData['id'],
                    'source' => $postData['source']
                ]
            )
        );

        $returnData = $response->getResult();

        if (isset($returnData['error'])) {
            $this->getResponse()->setStatusCode(422);
            unset($returnData['error']);
        }

        return new JsonModel($returnData);
    }
}

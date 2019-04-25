<?php

namespace Admin\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\Template\TemplateSource as ItemDto;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Dvsa\Olcs\Transfer\Query\Template\AvailableTemplates as ListDto;
use Admin\Data\Mapper\Template as Mapper;
use Zend\View\Model\ViewModel;
use Admin\Form\Model\Form\TemplateEdit;
use Admin\Form\Model\Form\TemplateFilter;

/**
 * Email Template admin controller
 *
 */
class EmailTemplateController extends AbstractInternalController implements LeftViewProvider, ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::ADMIN_PERMITS
        ],
    ];

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions']
    ];

    protected $tableName = 'admin-email-templates';
    protected $defaultTableSortField = 'description';
    protected $defaultTableOrderField = 'ASC';

    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;

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
     * Override this to make any required changes to parameters prior to creation of $listDto
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
}

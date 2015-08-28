<?php

/**
 * Transport Manager Details Detail Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Dvsa\Olcs\Transfer\Command\Tm\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Tm\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Tm\TransportManager as TransportManagerQry;
use Olcs\Data\Mapper\TransportManager as Mapper;
use Common\Service\Entity\TransportManagerEntityService;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Form\Model\Form\TransportManager as TransportManagerForm;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;

/**
 * Transport Manager Details Detail Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsDetailController extends AbstractInternalController implements
    PageLayoutProvider,
    PageInnerLayoutProvider,
    TransportManagerControllerInterface
{
    protected $section = 'transport-manager';

    /* for edit */
    protected $formClass = TransportManagerForm::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;

    /* form add */
    protected $createCommand = CreateDto::class;

    /* for view */
    protected $editViewTemplate = 'pages/crud-form';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = TransportManagerQry::class;
    protected $itemParams = ['id' => 'transportManager'];

    protected $defaultData = [
        'tmType'  => TransportManagerEntityService::TRANSPORT_MANAGER_STATUS_ACTIVE
    ];

    protected $redirectConfig = [
        'index' => [
            'action' => 'index',
            'route' => 'transport-manager/details/details',
            'reUseParams' => true,
            'resultIdMap' => [
                'transportManager' => 'transportManager'
            ]
        ]
    ];

    public function getPageLayout()
    {
        return 'layout/transport-manager-section-migrated';
    }

    public function getPageInnerLayout()
    {
        return 'pages/crud-form';
    }

    public function indexAction()
    {
        $tmId = $this->params()->fromRoute('transportManager');

        if ($this->isButtonPressed('cancel')) {
            if ($tmId) {
                $this->flashMessenger()->addSuccessMessage('Your changes have been discarded');
                return $this->redirect()->toRouteAjax('transport-manager/details', ['transportManager' => $tmId]);
            } else {
                return $this->redirect()->toRouteAjax('operators/operators-params');
            }
        }

        $this->placeholder()->setPlaceholder('section', 'details-details');
        if ($tmId) {
            $this->placeholder()->setPlaceholder('disabled', false);
            return $this->edit(
                $this->formClass,
                $this->itemDto,
                new GenericItem($this->itemParams),
                $this->updateCommand,
                $this->mapperClass,
                $this->editViewTemplate,
                'internal-transport-manager-updated'
            );
        } else {
            $this->placeholder()->setPlaceholder('disabled', true);
            $title = $this->getServiceLocator()
                ->get('translator')
                ->translate('internal-transport-manager-new-transport-manager');
            $this->placeholder()->setPlaceholder('pageTitle', $title);
            return $this->add(
                $this->formClass,
                new AddFormDefaultData($this->defaultData),
                $this->createCommand,
                $this->mapperClass,
                $this->editViewTemplate,
                'internal-transport-manager-created'
            );
        }
    }

    /**
     * Check if a button was pressed
     *
     * @param string $button
     * @param array $data
     * @return bool
     */
    public function isButtonPressed($button, $data = null)
    {
        $request = $this->getRequest();

        if (is_null($data)) {
            $data = (array)$request->getPost();
        }

        return $request->isPost() && isset($data['form-actions'][$button]);
    }

    public function alterFormForIndex($form, $data)
    {
        if (isset($data['removedDate']) && !is_null($data['removedDate'])) {
            $form->setOption('readonly', true);
        }

        return $form;
    }
}

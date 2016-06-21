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
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\TransportManager as Mapper;
use Common\Service\Entity\TransportManagerEntityService;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Form\Model\Form\TransportManager as TransportManagerForm;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Zend\View\Model\ViewModel;
use Common\RefData;

/**
 * Transport Manager Details Detail Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsDetailController extends AbstractInternalController implements
    TransportManagerControllerInterface,
    LeftViewProvider
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
        'tmStatus'  => RefData::TRANSPORT_MANAGER_STATUS_CURRENT
    ];

    protected $redirectConfig = [
        'index' => [
            'action' => 'index',
            'route' => 'transport-manager/details',
            'reUseParams' => true,
            'resultIdMap' => [
                'transportManager' => 'transportManager'
            ]
        ]
    ];

    public function getLeftView()
    {
        $tmId = $this->params()->fromRoute('transportManager');

        if ($tmId) {
            $view = new ViewModel();
            $view->setTemplate('sections/transport-manager/partials/details-left');
            return $view;
        }

        return null;
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

        if ($tmId) {
            return $this->edit(
                $this->formClass,
                $this->itemDto,
                new GenericItem($this->itemParams),
                $this->updateCommand,
                $this->mapperClass,
                $this->editViewTemplate,
                'internal-transport-manager-updated',
                'Details'
            );
        } else {
            $this->placeholder()->setPlaceholder('pageTitle', 'internal-transport-manager-new-transport-manager');
            return $this->add(
                $this->formClass,
                new AddFormDefaultData($this->defaultData),
                $this->createCommand,
                $this->mapperClass,
                $this->editViewTemplate,
                'internal-transport-manager-created',
                null
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
        // if TM has removedDate then make the form readonly
        if (isset($data['transport-manager-details']['removedDate']) &&
            $data['transport-manager-details']['removedDate'] !== null
        ) {
            $form->setOption('readonly', true);
        }
        if (empty($data['transport-manager-details']['id'])) {
            $this->getServiceLocator()
                ->get('Helper\Form')
                ->remove($form, 'transport-manager-details->transport-manager-id');
        } else {
            $form->get('transport-manager-details')
                ->get('transport-manager-id')
                ->setValue($data['transport-manager-details']['id']);
        }

        return $form;
    }
}

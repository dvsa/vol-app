<?php

namespace Olcs\Controller\Bus\Details;

use Dvsa\Olcs\Transfer\Command\Bus\UpdateQualitySchemes as UpdateQualityCmd;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateServiceDetails as UpdateServiceCmd;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateStops as UpdateStopCmd;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateTaAuthority as UpdateTaCmd;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg as ItemDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Data\Mapper\BusReg as BusRegMapper;
use Olcs\Form\Model\Form\BusRegQuality as QualityForm;
use Olcs\Form\Model\Form\BusRegStop as StopForm;
use Olcs\Form\Model\Form\BusRegTa as TaForm;
use Olcs\Form\Model\Form\BusServiceNumberAndType as ServiceForm;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;

class BusDetailsController extends AbstractInternalController implements
    BusRegControllerInterface,
    LeftViewProvider
{
    use ControllerTraits\BusControllerTrait;

    protected $redirectConfig = [
        'service' => [
            'action' => 'service'
        ],
        'ta' => [
            'action' => 'ta'
        ],
        'stop' => [
            'action' => 'stop'
        ],
        'quality' => [
            'action' => 'quality'
        ]
    ];

    protected $navigationId = 'licence_bus_details';

    protected $inlineScripts = ['serviceAction' => [], 'taAction' => ['forms/bus-details-ta']];

    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id' => 'busRegId'];
    protected $mapperClass = BusRegMapper::class;
    protected $section = 'details';
    protected $subNavRoute = 'licence_bus_details';

    /**
     * Get left view
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/bus/partials/left');

        return $view;
    }

    /**
     * Service page
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function serviceAction()
    {
            return $this->edit(
                ServiceForm::class,
                $this->itemDto,
                new GenericItem($this->itemParams),
                UpdateServiceCmd::class,
                $this->mapperClass,
                'pages/crud-form',
                'Updated record',
                'Service No. & type'
            );
    }

    /**
     * TA page
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function taAction()
    {
        return $this->edit(
            TaForm::class,
            $this->itemDto,
            new GenericItem($this->itemParams),
            UpdateTaCmd::class,
            $this->mapperClass,
            'pages/crud-form',
            'Updated record',
            'TA\'s & authorities'
        );
    }

    /**
     * Stop page
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function stopAction()
    {
        return $this->edit(
            StopForm::class,
            $this->itemDto,
            new GenericItem($this->itemParams),
            UpdateStopCmd::class,
            $this->mapperClass,
            'pages/crud-form',
            'Updated record',
            'Stops, manoeuvres & subsidies'
        );
    }

    /**
     * Quality page
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function qualityAction()
    {
        return $this->edit(
            QualityForm::class,
            $this->itemDto,
            new GenericItem($this->itemParams),
            UpdateQualityCmd::class,
            $this->mapperClass,
            'pages/crud-form',
            'Updated record',
            'Quality schemes'
        );
    }

    /**
     * Alter form
     *
     * @param \Common\Form\Form $form     Form
     * @param array             $formData Form data
     *
     * @return \Common\Form\Form
     */
    protected function alterForm($form, $formData)
    {
        $busReg = $this->getBusReg();

        if ($busReg['isReadOnly']) {
            // If read only show read only form
            $form->setOption('readonly', true);
        }

        return $form;
    }

    /**
     * Alter form for service
     *
     * @param \Common\Form\Form $form     Form
     * @param array             $formData Form data
     *
     * @return \Common\Form\Form
     */
    protected function alterFormForService($form, $formData)
    {
        return $this->alterForm($form, $formData);
    }

    /**
     * Alter form for TA
     *
     * @param \Common\Form\Form $form     Form
     * @param array             $formData Form data
     *
     * @return \Common\Form\Form
     */
    protected function alterFormForTa($form, $formData)
    {
        return $this->alterForm($form, $formData);
    }

    /**
     * Alter form for stop
     *
     * @param \Common\Form\Form $form     Form
     * @param array             $formData Form data
     *
     * @return \Common\Form\Form
     */
    protected function alterFormForStop($form, $formData)
    {
        return $this->alterForm($form, $formData);
    }

    /**
     * Alter form for quality
     *
     * @param \Common\Form\Form $form     Form
     * @param array             $formData Form data
     *
     * @return \Common\Form\Form
     */
    protected function alterFormForQuality($form, $formData)
    {
        return $this->alterForm($form, $formData);
    }
}

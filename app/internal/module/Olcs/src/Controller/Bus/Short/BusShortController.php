<?php

/**
 * Bus Short Notice Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Short;

use Olcs\Controller\AbstractInternalController;
use Olcs\Data\Mapper\BusRegShortNotice as ShortNoticeMapper;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Form\Model\Form\BusShortNotice as ShortNoticeForm;
use Dvsa\Olcs\Transfer\Query\Bus\ShortNoticeByBusReg as ShortNoticeDto;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg as BusRegDto;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateShortNotice as UpdateShortNoticeCmd;

/**
 * Bus Short Notice Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusShortController extends AbstractInternalController implements BusRegControllerInterface
{
    protected $navigationId = 'licence_bus_short';
    protected $itemDto = ShortNoticeDto::class;
    protected $itemParams = ['id' => 'busRegId'];
    protected $formClass = ShortNoticeForm::class;
    protected $updateCommand = UpdateShortNoticeCmd::class;
    protected $mapperClass = ShortNoticeMapper::class;
    protected $editContentTitle = 'Bus Short Notice';

    /**
     * @param \Common\Form\Form $form
     * @param array $formData
     * @return \Common\Form\Form
     */
    protected function alterFormForEdit($form, $formData)
    {
        $busReg = $this->getBusReg();

        if ($busReg['isReadOnly'] || $busReg['isFromEbsr']) {
            $form->setOption('readonly', true);
        }

        return $form;
    }

    /**
     * Gets a Bus Reg - we'll have this query cached, and if it previously failed we'll have returned a 404 already
     *
     * @return array|mixed
     */
    private function getBusReg()
    {
        $params = ['id' => $this->params()->fromRoute('busRegId')];
        $response = $this->handleQuery(BusRegDto::create($params));

        return $response->getResult();
    }
}

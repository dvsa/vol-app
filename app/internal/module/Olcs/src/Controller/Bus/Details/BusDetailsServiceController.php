<?php

/**
 * Bus Details Service Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

use Dvsa\Olcs\Transfer\Command\Bus\UpdateServiceDetails;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg;
use Olcs\Controller\AbstractInternalController;
use \Olcs\Data\Mapper\BusReg as BusRegMapper;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Form\Model\Form\BusServiceNumberAndType;

/**
 * Bus Details Service Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsServiceController extends AbstractInternalController
    implements BusRegControllerInterface, PageLayoutProvider , PageInnerLayoutProvider
{
    public function getPageInnerLayout()
    {
        return 'layout/bus-registration-subsection';
    }

    public function getPageLayout()
    {
        return 'layout/bus-registrations-section';
    }

    protected $inlineScripts = ['bus-servicenumbers'];


    protected $itemDto = BusReg::class;
    protected $itemParams = ['id' => 'busRegId'];
    protected $formClass = BusServiceNumberAndType::class;
    protected $updateCommand = UpdateServiceDetails::class;
    protected $mapperClass = BusRegMapper::class;
    public function editAction()
    {
        //@TODO deal with setting read only based on below conditional
        /*if ($this->isFromEbsr() || !$this->isLatestVariation()) {
            $form->setOption('readonly', true);
        }*/
        $this->script()->addScripts($this->inlineScripts);
        return $this->edit(
            $this->formClass,
            $this->itemDto,
            $this->itemParams,
            $this->updateCommand,
            $this->mapperClass
        );
    }
}

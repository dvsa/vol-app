<?php

/**
 * External Application Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UndertakingsController extends Lva\AbstractUndertakingsController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    /**
     * @param \Zend\Form\Form
     */
    protected function alterFormForLva(\Zend\Form\Form $form)
    {
        $fieldSet = $form->get('declarationsAndUndertakings');

        $application = [];
        $fieldSet->get('undertakings')->setValue($this->getUndertakingsPartial($application));
        $fieldSet->get('declarations')->setValue($this->getDeclarationsPartial($application));
    }

    /**
     * Determine correct partial to use for undertakings html
     *
     * (public for unit testing)
     *
     * @param array $application application data
     * @return string
     */
    public function getUndertakingsPartial(array $application) {
        $prefix = 'markup-undertakings-';

        // @TODO switch on PSV / Goods / S,SI,SR,S
        // options are gv79-standard, gv79-restricted, psv421-standard, psv421-restricted, psv-356 (SR)
        $part = 'gv79-restricted';

        return $prefix.$part;
    }

    /**
     * Determine correct partial to use for undertakings html
     *
     * (public for unit testing)
     *
     * @param array $application application data
     * @return string
     */
    public function getDeclarationsPartial(array $application) {
        $prefix = 'markup-declarations-';

        // @TODO switch on PSV / Goods / S,SI,SR,S
        // options are gv79, psv421, psv-356
        $part = 'gv79';

        return $prefix.$part;
    }
}

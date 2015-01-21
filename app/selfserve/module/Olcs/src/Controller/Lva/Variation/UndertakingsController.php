<?php

/**
 * External Variation Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * External Variation Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UndertakingsController extends Lva\AbstractUndertakingsController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
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

        // @TODO switch on PSV / Goods / upgrade
        // options are gv81-standard, gv81-restricted, gv80a, psv430-431
        $part = 'psv430-431';

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

        // @TODO switch on PSV / Goods / upgrade
        // options are gv81-standard, gv81-restricted, gv80a, psv430-431-standard, psv430-431-restricted
        $part = 'psv430-431-standard';

        return $prefix.$part;
    }
}

<?php


namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractConvictionsPenaltiesController;
use Common\RefData;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;

class ConvictionsPenaltiesController extends AbstractConvictionsPenaltiesController
{
    use ExternalControllerTrait;

    protected $lva = self::LVA_VAR;
    protected $variationType = RefData::VARIATION_TYPE_DIRECTOR_CHANGE;

    protected function getIdentifier()
    {
        return $this->params($this->getIdentifierIndex());
    }

    /**
     * Get Identifier Index
     *
     * @return string
     */
    protected function getIdentifierIndex()
    {

        if ($this->lva === self::LVA_LIC) {
            return 'licence';
        }

        return 'application';
    }







}

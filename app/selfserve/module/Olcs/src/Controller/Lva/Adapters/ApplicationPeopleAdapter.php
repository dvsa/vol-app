<?php

namespace Olcs\Controller\Lva\Adapters;

use Common\Service\Lva\PeopleLvaService;
use Psr\Container\ContainerInterface;

/**
 * External Application People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationPeopleAdapter extends VariationPeopleAdapter
{
    public function __construct(ContainerInterface $container, PeopleLvaService $peopleLvaService)
    {
        $this->peopleLvaService = $peopleLvaService;
        parent::__construct($container, $peopleLvaService);
    }

    /**
     * Can Modify
     *
     * @return bool
     */
    #[\Override]
    public function canModify(): bool
    {
        if ($this->hasInforceLicences() === false) {
            return true;
        }

        return parent::canModify();
    }
}

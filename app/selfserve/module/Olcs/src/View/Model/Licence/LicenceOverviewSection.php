<?php

/**
 * Licence Overview Section
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\View\Model\Licence;

use Olcs\View\Model\LvaOverviewSection;

/**
 * Licence Overview Section
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceOverviewSection extends LvaOverviewSection
{
    protected $type = 'licence';

    public function __construct($ref, $data)
    {
        parent::__construct($ref, $data, 'update');
    }
}

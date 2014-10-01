<?php

/**
 * Discs Psv Controller
 *
 * Internal - Licence - Discs PSV section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Licence\Details;

/**
 * Discs Psv Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DiscsPsvController extends AbstractLicenceDetailsController
{
    /**
     * Set the form name
     *
     * @var string
     */
    protected $formName = 'application_vehicle-safety_discs-psv';

    /**
     * Setup the section
     *
     * @var string
     */
    protected $section = 'discs_psv';
}

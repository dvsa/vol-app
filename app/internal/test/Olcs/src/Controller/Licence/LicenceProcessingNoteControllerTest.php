<?php
/**
 * Licence Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Licence;

use OlcsTest\Controller\ProcessingNoteControllerTestAbstract;

/**
 * Licence Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceProcessingNoteControllerTest extends ProcessingNoteControllerTestAbstract
{
    protected $testClass = '\Olcs\Controller\Licence\Processing\LicenceProcessingNoteController';
    protected $mainIdRouteParam = 'licence';
}

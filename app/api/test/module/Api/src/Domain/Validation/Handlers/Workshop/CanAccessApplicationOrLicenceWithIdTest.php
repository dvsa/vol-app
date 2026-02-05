<?php

declare(strict_types=1);

/**
 * Can Access Application or Licence With Id test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Workshop;

use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Workshop\CanAccessApplicationOrLicenceWithId;
use Laminas\ServiceManager\ServiceManager;
use Dvsa\Olcs\Transfer\Query\Workshop\Workshop as Qry;

/**
 * Can Access Application or Licence With Id test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessApplicationOrLicenceWithIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessApplicationOrLicenceWithId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessApplicationOrLicenceWithId();

        parent::setUp();
    }

    public function testIsValidNoContext(): void
    {
        $data = [
            'application' => null,
            'licence' => null
        ];

        $dto = Qry::create($data);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidWithAppContextNoAccess(): void
    {
        $data = [
            'id' => 111,
            'application' => 321,
        ];

        $dto = Qry::create($data);

        $this->setIsValid('canAccessApplication', [321], false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidWithLicContextNoAccess(): void
    {
        $data = [
            'id' => 111,
            'licence' => 321,
        ];

        $dto = Qry::create($data);

        $this->setIsValid('canAccessLicence', [321], false);

        $this->assertFalse($this->sut->isValid($dto));
    }

    public function testIsValidWithLicContextHasAccess(): void
    {
        $data = [
            'id' => 111,
            'licence' => 321,
        ];

        $dto = Qry::create($data);

        $this->setIsValid('canAccessLicence', [321], true);

        $this->assertTrue($this->sut->isValid($dto));
    }
}

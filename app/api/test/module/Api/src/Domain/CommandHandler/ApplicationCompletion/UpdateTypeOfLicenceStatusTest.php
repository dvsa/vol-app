<?php

declare(strict_types=1);

/**
 * Update Type Of Licence Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateTypeOfLicenceStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateTypeOfLicenceStatus;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Type Of Licence Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UpdateTypeOfLicenceStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'TypeOfLicence';

    public function setUp(): void
    {
        $this->sut = new UpdateTypeOfLicenceStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->refData = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            Licence::LICENCE_TYPE_RESTRICTED,
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            Licence::LICENCE_CATEGORY_PSV,
            RefData::APP_VEHICLE_TYPE_PSV,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setTypeOfLicenceStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setTypeOfLicenceStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutGoodsOrPsv(): void
    {
        $this->applicationCompletion->setTypeOfLicenceStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setNiFlag('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutLicenceType(): void
    {
        $this->applicationCompletion->setTypeOfLicenceStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setNiFlag('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithInvalidCombo(): void
    {
        $this->applicationCompletion->setTypeOfLicenceStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setNiFlag('Y');
        $this->application->setGoodsOrPsv($this->refData[Licence::LICENCE_CATEGORY_PSV]);
        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommand(): void
    {
        $this->applicationCompletion->setTypeOfLicenceStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setNiFlag('N');
        $this->application->setGoodsOrPsv($this->refData[Licence::LICENCE_CATEGORY_PSV]);
        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $this->application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_PSV]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}

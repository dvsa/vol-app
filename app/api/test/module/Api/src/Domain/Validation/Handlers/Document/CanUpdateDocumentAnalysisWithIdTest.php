<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Document;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanUpdateDocumentAnalysisWithId;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentAnalysisAssessmentStatus as Cmd;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;

final class CanUpdateDocumentAnalysisWithIdTest extends AbstractHandlerTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanUpdateDocumentAnalysisWithId();

        parent::setUp();
    }

    public function testIsNotValidForANonInternalUser(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->assertFalse($this->sut->isValid(Cmd::create(['id' => 1, 'status' => 'APPROVED'])));
    }

    public function testIsValidForAnInternalUserWithNoContext(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $this->assertTrue($this->sut->isValid(Cmd::create(['id' => 1, 'status' => 'APPROVED'])));
    }

    public function testIsValidWhenTheAnalysisBelongsToTheApplication(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $this->setIsValid('documentAnalysisBelongsToApplication', [1, 42], true);

        $this->assertTrue($this->sut->isValid(Cmd::create(['id' => 1, 'application' => 42, 'status' => 'APPROVED'])));
    }

    public function testIsNotValidWhenTheAnalysisBelongsToAnotherApplication(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $this->setIsValid('documentAnalysisBelongsToApplication', [1, 42], false);

        $this->assertFalse($this->sut->isValid(Cmd::create(['id' => 1, 'application' => 42, 'status' => 'APPROVED'])));
    }

    public function testIsValidWhenTheAnalysisBelongsToTheLicence(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $this->setIsValid('documentAnalysisBelongsToLicence', [1, 7], true);

        $this->assertTrue($this->sut->isValid(Cmd::create(['id' => 1, 'licence' => 7, 'status' => 'APPROVED'])));
    }

    public function testIsNotValidWhenTheAnalysisBelongsToAnotherLicence(): void
    {
        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $this->setIsValid('documentAnalysisBelongsToLicence', [1, 7], false);

        $this->assertFalse($this->sut->isValid(Cmd::create(['id' => 1, 'licence' => 7, 'status' => 'APPROVED'])));
    }
}


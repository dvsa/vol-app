<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepository;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\FinanceReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * FinanceReviewServiceTest
 */
class FinanceReviewServiceTest extends MockeryTestCase
{
    /** @var DeclarationReviewService */
    protected $sut;

    /** @var ContinuationDetail */
    private $continuationDetail;

    /** @var DocumentRepository */
    private $mockDocumentRepo;

    public function setUp(): void
    {
        $mockLicence = m::mock();
        $mockLicence->shouldReceive('getOrganisation->getId')->once()->with()->andReturn(123);

        $this->continuationDetail = new ContinuationDetail();
        $this->continuationDetail->setId(99);
        $this->continuationDetail->setLicence($mockLicence);

        $mockFinancialService = m::mock(FinancialStandingHelperService::class);
        $mockFinancialService->shouldReceive('getFinanceCalculationForOrganisation')->once()->with(123)->andReturn(100);

        $mockTranslator = m::mock(TranslatorInterface::class);
        $mockTranslator->shouldReceive('translate')->andReturnUsing(
            fn($message) => $message . '_translated'
        );

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->mockDocumentRepo = m::mock(DocumentRepository::class);

        $this->sut = new FinanceReviewService(
            $abstractReviewServiceServices,
            $mockFinancialService,
            $this->mockDocumentRepo
        );
    }

    public function testGetConfigFrom(): void
    {
        $result = $this->sut->getConfigFromData($this->continuationDetail);
        $this->assertCount(7, $result);

        $this->assertSame(
            [
                ['value' => 'continuations.finance.financial-amount-required', 'header' => true],
                ['value' => '&pound;100.00', 'noEscape' => true],
            ],
            $result[0]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.average-balance-amount', 'header' => true],
                ['value' => 'None_translated', 'noEscape' => true],
            ],
            $result[1]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.overdraft-facility', 'header' => true],
                ['value' => 'None_translated', 'noEscape' => true],
            ],
            $result[2]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.factoring-amount', 'header' => true],
                ['value' => 'None_translated', 'noEscape' => true],
            ],
            $result[3]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.other-available-finances', 'header' => true],
                ['value' => 'None_translated', 'noEscape' => true],
            ],
            $result[4]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.finances-are-sufficient', 'header' => true],
                ['value' => 'No_translated', 'noEscape' => false],
            ],
            $result[5]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.financial-evidence', 'header' => true],
                ['value' => 'None_translated', 'noEscape' => true],
            ],
            $result[6]
        );
    }

    public function testGetConfigFromFinanceRequiredLessThanAvailable(): void
    {
        $this->continuationDetail->setAverageBalanceAmount(45);
        $this->continuationDetail->setOverdraftAmount(46);
        $this->continuationDetail->setFactoringAmount(47);

        $result = $this->sut->getConfigFromData($this->continuationDetail);
        $this->assertCount(5, $result);

        $this->assertSame(
            [
                ['value' => 'continuations.finance.financial-amount-required', 'header' => true],
                ['value' => '&pound;100.00', 'noEscape' => true],
            ],
            $result[0]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.average-balance-amount', 'header' => true],
                ['value' => '&pound;45.00', 'noEscape' => true],
            ],
            $result[1]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.overdraft-facility', 'header' => true],
                ['value' => '&pound;46.00', 'noEscape' => true],
            ],
            $result[2]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.factoring-amount', 'header' => true],
                ['value' => '&pound;47.00', 'noEscape' => true],
            ],
            $result[3]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.finances-are-sufficient', 'header' => true],
                ['value' => 'Yes_translated', 'noEscape' => false],
            ],
            $result[4]
        );
    }

    public function testGetConfigFromFinanceRequiredGreaterThanAvailable(): void
    {
        $this->continuationDetail->setAverageBalanceAmount(4);
        $this->continuationDetail->setOverdraftAmount(5);
        $this->continuationDetail->setFactoringAmount(6);
        $this->continuationDetail->setOtherFinancesAmount(7);
        $this->continuationDetail->setOtherFinancesDetails('FOO BAR');

        $result = $this->sut->getConfigFromData($this->continuationDetail);
        $this->assertCount(8, $result);

        $this->assertSame(
            [
                ['value' => 'continuations.finance.financial-amount-required', 'header' => true],
                ['value' => '&pound;100.00', 'noEscape' => true],
            ],
            $result[0]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.average-balance-amount', 'header' => true],
                ['value' => '&pound;4.00', 'noEscape' => true],
            ],
            $result[1]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.overdraft-facility', 'header' => true],
                ['value' => '&pound;5.00', 'noEscape' => true],
            ],
            $result[2]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.factoring-amount', 'header' => true],
                ['value' => '&pound;6.00', 'noEscape' => true],
            ],
            $result[3]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.other-available-finances', 'header' => true],
                ['value' => '&pound;7.00', 'noEscape' => true],
            ],
            $result[4]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.where-do-these-finances-come-from', 'header' => true],
                ['value' => 'FOO BAR', 'noEscape' => false],
            ],
            $result[5]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.finances-are-sufficient', 'header' => true],
                ['value' => 'No_translated', 'noEscape' => false],
            ],
            $result[6]
        );
        $this->assertSame(
            [
                ['value' => 'continuations.finance.financial-evidence', 'header' => true],
                ['value' => 'None_translated', 'noEscape' => true],
            ],
            $result[7]
        );
    }

    public function testGetConfigFromDataSendInPost(): void
    {
        $this->continuationDetail->setFinancialEvidenceUploaded(false);
        $result = $this->sut->getConfigFromData($this->continuationDetail);
        $this->assertCount(7, $result);

        $this->assertSame(
            [
                ['value' => 'continuations.finance.financial-evidence', 'header' => true],
                ['value' => 'continuations.finance.send-in-post_translated', 'noEscape' => true],
            ],
            $result[6]
        );
    }

    public function testGetConfigFromDataFilesUploaded(): void
    {
        $document1 = new Document(1);
        $document1->setDescription('document1');
        $document2 = new Document(2);
        $document2->setDescription('document2');

        $this->mockDocumentRepo->shouldReceive('fetchListForContinuationDetail')->with(99)->once()->andReturn(
            [$document1, $document2]
        );

        $this->continuationDetail->setFinancialEvidenceUploaded(true);
        $result = $this->sut->getConfigFromData($this->continuationDetail);
        $this->assertCount(7, $result);

        $this->assertSame(
            [
                ['value' => 'continuations.finance.financial-evidence', 'header' => true],
                ['value' => 'document1<br>document2', 'noEscape' => true],
            ],
            $result[6]
        );
    }
}

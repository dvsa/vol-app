<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva;

use Common\Form\Elements\Types\FileUploadList;
use Common\Form\Form;
use Common\RefData;
use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use DateTimeImmutable;
use Dvsa\Olcs\Application\Controller\UploadEvidenceController as ApplicationController;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument;
use Dvsa\Olcs\Transfer\Query\Application\UploadEvidence;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Olcs\Controller\Lva\Variation\UploadEvidenceController as VariationController;
use PHPUnit\Framework\Attributes\DataProvider;

final class AbstractUploadEvidenceControllerTest extends AbstractLvaControllerTestCase
{
    #[DataProvider('controllerProvider')]
    public function testInitialRequestCreatesCutoff(string $controllerClass): void
    {
        $before = time();
        $this->setUpController($controllerClass);
        $this->sut->shouldNotReceive('handleCommand');

        $view = $this->sut->indexAction();

        $this->assertSame($this->form, $view->getVariable('form'));
        $cutoff = $this->form->get('correlationId')->getValue();
        $this->assertNotEmpty($cutoff);
        $timestamp = (new DateTimeImmutable($cutoff))->getTimestamp();
        $this->assertGreaterThanOrEqual($before, $timestamp);
        $this->assertLessThanOrEqual(time(), $timestamp);
    }

    public static function controllerProvider(): \Iterator
    {
        yield 'application' => [ApplicationController::class];
        yield 'variation' => [VariationController::class];
    }

    #[DataProvider('controllerProvider')]
    public function testRemovalPreservesCutoffForSubsequentUpload(string $controllerClass): void
    {
        $cutoff = '2020-01-01T10:00:00+00:00';
        $first = $this->document(1, '2020-01-01T10:01:00+00:00');
        $removed = $this->document(2, '2020-01-01T10:02:00+00:00');
        $third = $this->document(3, '2020-01-01T10:03:00+00:00');
        $this->setUpController($controllerClass, [$first, $removed, $third]);
        $this->request->setMethod('POST');
        $this->request->setPost(new Parameters([
            'correlationId' => $cutoff,
            'supportingEvidence' => ['files' => ['list' => [
                'file-2' => ['id' => 2, 'remove' => 'Remove'],
            ]]],
        ]));
        $this->expectCommand(DeleteDocument::class, ['id' => 2, 'unlinkLicence' => true], []);

        $this->sut->indexAction();

        $this->assertSame(
            ['file-1', 'file-3'],
            array_keys($this->form->get('supportingEvidence')->get('files')->get('list')->getFieldsets())
        );
        $renderedCutoff = $this->form->get('correlationId')->getValue();
        $this->assertSame($cutoff, $renderedCutoff);

        $this->setUpController($controllerClass, [
            $first,
            $third,
            $this->document(4, '2020-01-01T10:04:00+00:00'),
            $this->document(5, $cutoff),
            $this->document(6, '2020-01-01T09:59:00+00:00'),
            $this->document(7, '2020-01-01T10:01:00+00:00', false),
        ]);
        $this->request->setMethod('POST');
        $this->request->setPost(new Parameters([
            'correlationId' => $renderedCutoff,
            'supportingEvidence' => ['files' => ['upload' => 'Upload']],
        ]));
        $this->sut->shouldNotReceive('handleCommand');
        $this->sut->indexAction();

        $this->assertSame(
            [1, 3, 4],
            array_column($this->sut->supportingEvidenceLoadFileUpload(), 'id')
        );
    }

    private function document(int $id, string $createdOn, bool $isPostSubmissionUpload = true): array
    {
        return [
            'id' => $id,
            'description' => 'Document ' . $id,
            'size' => 100,
            'version' => 1,
            'createdOn' => $createdOn,
            'isPostSubmissionUpload' => $isPostSubmissionUpload,
        ];
    }

    private function setUpController(string $controllerClass, array $documents = []): void
    {
        $this->form = new Form('upload-evidence');
        $this->form->add(new Hidden('correlationId'));
        $this->form->add(new Fieldset('financialEvidence'));
        $files = new Fieldset('files');
        $files->add(new FileUploadList('list'));
        $supportingEvidence = new Fieldset('supportingEvidence');
        $supportingEvidence->add($files);
        $this->form->add($supportingEvidence);

        $formHelper = m::mock(FormHelperService::class);
        $formHelper->shouldReceive('createForm')->with('Lva\UploadEvidence')->once()->andReturn($this->form);
        $urlHelper = m::mock(UrlHelperService::class);
        $urlHelper->shouldReceive('fromRoute')->andReturn('/document');
        $uploadHelper = new FileUploadHelperService($urlHelper, m::mock(Scan::class));

        $constructorParams = [
            m::mock(NiTextTranslation::class),
            m::mock(AuthorizationService::class),
            $formHelper,
        ];
        if ($controllerClass === ApplicationController::class) {
            $constructorParams[] = m::mock(RestrictionHelperService::class);
            $constructorParams[] = m::mock(StringHelperService::class);
        }
        $constructorParams[] = $uploadHelper;
        $constructorParams[] = m::mock(TranslationHelperService::class);
        $this->mockController($controllerClass, $constructorParams);

        $this->sut->shouldReceive('getIdentifier')->andReturn(123);
        $this->sut->shouldReceive('getApplicationData')->with(123)->once()->andReturn([
            'goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_PSV],
            'status' => ['id' => RefData::APPLICATION_STATUS_UNDER_CONSIDERATION],
        ]);
        $this->expectQuery(UploadEvidence::class, ['id' => 123], [
            'financialEvidence' => ['canAdd' => false],
            'supportingEvidence' => $documents,
        ]);
        $this->sut->shouldReceive('render')
            ->with('upload-evidence', $this->form, ['warningText' => 'supply-supporting-evidence-warning'])
            ->once()
            ->andReturn(new ViewModel(['form' => $this->form]));
    }
}

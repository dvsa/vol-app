<?php

declare(strict_types=1);

/**
 * Internal Licence Community Licences Controller Test
 */

namespace OlcsTest\Controller\Lva\Licence;

use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicences;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Mvc\Controller\Plugin\Params;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Olcs\Controller\Lva\Licence\CommunityLicencesController;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Internal Licence Community Licences Controller Test
 */
final class CommunityLicencesControllerTest extends AbstractLvaControllerTestCase
{
    protected $mockFormHelper;
    protected $mockFormServiceManager;
    protected $mockScriptFactory;

    public function setUp(): void
    {
        parent::setUp();

        $mockNiTextTranslationUtil = m::mock(NiTextTranslation::class);
        $mockAuthService = m::mock(AuthorizationService::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $mockFlashMessenger = m::mock(FlashMessengerHelperService::class);
        $this->mockFormServiceManager = m::mock(FormServiceManager::class);
        $this->mockScriptFactory = m::mock(ScriptFactory::class);
        $mockTransferAnnotationBuilder = m::mock(AnnotationBuilder::class);
        $mockCommandService = m::mock(CommandService::class);
        $mockNavigation = m::mock('navigation');

        $this->mockController(CommunityLicencesController::class, [
            $mockNiTextTranslationUtil,
            $mockAuthService,
            $this->mockFormHelper,
            $mockFlashMessenger,
            $this->mockFormServiceManager,
            $this->mockScriptFactory,
            $mockTransferAnnotationBuilder,
            $mockCommandService,
            $mockNavigation,
        ]);
    }

    /**
     * Exercises the GET path of indexAction(), including getTableData()'s use of the
     * GenericList parameter provider to build the query sent for the community licences list.
     */
    public function testIndexActionGet(): void
    {
        $licenceId = 123;

        // No query string filters applied -> default filters/params used throughout
        $paramsPlugin = m::mock(Params::class);
        $paramsPlugin->shouldReceive('fromQuery')
            ->andReturnUsing(fn($key = null, $default = null) => $key === null ? [] : $default);
        $paramsPlugin->shouldReceive('fromRoute')
            ->with('licence')
            ->andReturn($licenceId);

        $this->sut->shouldReceive('params')
            ->withNoArgs()
            ->andReturn($paramsPlugin);
        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);
        $this->sut->shouldReceive('plugin')
            ->with('params')
            ->andReturn($paramsPlugin);

        // GenericList will produce these defaulted params, which getTableData() then
        // augments with 'statuses' (from the default filters) and overrides 'limit'/'licence'
        $expectedListParams = [
            'page' => 1,
            'sort' => 'id',
            'order' => 'DESC',
            'limit' => 50,
            'licence' => $licenceId,
            'statuses' => implode(',', [
                RefData::COMMUNITY_LICENCE_STATUS_PENDING,
                RefData::COMMUNITY_LICENCE_STATUS_ACTIVE,
                RefData::COMMUNITY_LICENCE_STATUS_WITHDRAWN,
                RefData::COMMUNITY_LICENCE_STATUS_SUSPENDED,
            ]),
            'sortWhitelist' => [],
        ];

        $communityLicencesResult = [
            'count' => 0,
            'extra' => [
                'officeCopy' => false,
                'totActiveCommunityLicences' => 5,
            ],
        ];

        $communityLicencesResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)
            ->shouldReceive('isClientError')->andReturn(false)
            ->shouldReceive('isServerError')->andReturn(false)
            ->shouldReceive('getResult')->andReturn($communityLicencesResult)
            ->getMock();

        $capturedListQuery = null;
        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(CommunityLicences::class))
            ->once()
            ->andReturnUsing(function ($query) use (&$capturedListQuery, $communityLicencesResponse) {
                $capturedListQuery = $query;
                return $communityLicencesResponse;
            });

        // Used by getGoodsOrPsv() - not PSV, so title/form label are left unchanged
        $this->expectQuery(
            Licence::class,
            ['id' => $licenceId],
            ['goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE]]
        );

        $tableBuilder = m::mock(TableBuilder::class);
        $tableBuilder->shouldReceive('getRows')->andReturn([]);
        $tableBuilder->shouldReceive('removeAction');

        $tablePlugin = m::mock();
        $tablePlugin->shouldReceive('buildTable')->andReturn($tableBuilder);
        $this->sut->shouldReceive('table')->andReturn($tablePlugin);

        $urlPlugin = m::mock();
        $urlPlugin->shouldReceive('fromRoute')->andReturn('/lva-licence/community_licences');
        $this->sut->shouldReceive('url')->andReturn($urlPlugin);

        $filterForm = m::mock(Form::class);
        $filterForm->shouldReceive('setAttribute')->once()->andReturnSelf();
        $filterForm->shouldReceive('setData')
            ->once()
            ->with(['status' => [
                RefData::COMMUNITY_LICENCE_STATUS_PENDING,
                RefData::COMMUNITY_LICENCE_STATUS_ACTIVE,
                RefData::COMMUNITY_LICENCE_STATUS_WITHDRAWN,
                RefData::COMMUNITY_LICENCE_STATUS_SUSPENDED,
            ]])
            ->andReturnSelf();

        $this->mockFormHelper->shouldReceive('createForm')
            ->once()
            ->with('Lva\CommunityLicenceFilter', false)
            ->andReturn($filterForm);

        $form = m::mock(Form::class);
        $form->shouldReceive('get')->with('table')->andReturn(m::mock(\Laminas\Form\Fieldset::class));
        $form->shouldReceive('setData')
            ->once()
            ->with(['data' => ['totalActiveCommunityLicences' => 5]])
            ->andReturnSelf();

        $formService = m::mock();
        $formService->shouldReceive('getForm')->andReturn($form);
        $this->mockFormServiceManager->shouldReceive('get')
            ->with('lva-licence-community_licences')
            ->andReturn($formService);

        $this->mockFormHelper->shouldReceive('populateFormTable')
            ->once()
            ->with(m::any(), $tableBuilder);

        $this->mockFormHelper->shouldReceive('setFormActionFromRequest')
            ->once()
            ->with($form, $this->request);

        $this->mockScriptFactory->shouldReceive('loadFiles')
            ->once()
            ->with(['forms/filter', 'community-licence']);

        $this->sut->shouldReceive('render')
            ->once()
            ->with(
                'community_licences',
                $form,
                ['filterForm' => $filterForm, 'title' => 'lva.section.title.community_licences']
            )
            ->andReturn('RENDERED');

        $this->assertEquals('RENDERED', $this->sut->indexAction());

        $this->assertNotNull($capturedListQuery);
        $this->assertEquals($expectedListParams, $capturedListQuery->getArrayCopy());
    }
}

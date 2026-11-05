<?php

declare(strict_types=1);

namespace CommonTest\Controller\Lva;

use Common\Controller\Continuation\ChecklistController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateSmsNumber;
use Dvsa\Olcs\Transfer\Query\ContinuationDetail\LicenceChecklist;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Hamcrest\Core\IsEqual;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Service\AuthorizationService;

final class ChecklistControllerTest extends MockeryTestCase
{
    public $request;
    public $sut;
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $mockNiTextTranslationUtil = m::mock(NiTextTranslation::class);
        $mockAuthService = m::mock(AuthorizationService::class);
        $mockFormServiceManager = m::mock(FormServiceManager::class);
        $mockTranslationHelper = m::mock(TranslationHelperService::class);
        $this->mockController(ChecklistController::class, [
            $mockNiTextTranslationUtil,
            $mockAuthService,
            $mockFormServiceManager,
            $mockTranslationHelper,
        ]);
        $mockTranslationHelper->shouldReceive('translateReplace')->andReturn('translated');
        $mockTranslationHelper->shouldReceive('translate')
            ->andReturnUsing(
                static fn($input) => $input
            );
    }

    protected function mockController(string $className, array $constructorParams = []): void
    {
        $this->request = m::mock(\Laminas\Http\Request::class)->makePartial();

        // If constructor params are provided, pass them to the mock, otherwise mock without them
        if ($constructorParams !== []) {
            $this->sut = m::mock($className, $constructorParams)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        } else {
            $this->sut = m::mock($className)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        }

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->request);
    }

    private function indexData(): array
    {
        return [
            'licence' => [
                'id' => 77,
                'licNo' => 'OB1',
                'licenceType' => ['id' => 'ltyp_sn', 'description' => 'SN'],
                'goodsOrPsv' => ['id' => 'lcat_gv', 'description' => 'Goods'],
                'trafficArea' => ['id' => 'B'],
                'organisation' => [
                    'type' => ['id' => 'org_t_o', 'description' => 'Other'],
                    'organisationPersons' => [],
                    'name' => 'Org',
                ],
                'tradingNames' => [],
                'tachographInsName' => null,
                'correspondenceCd' => [],
                'licenceVehicles' => [],
                'operatingCentres' => [],
                'tmLicences' => [],
                'workshops' => [],
                'safetyInsVaries' => false,
            ],
            'id' => 99,
            'isMixedWithLgv' => false,
            'canHaveTrailers' => false,
            'tachographInsName' => null,
            'ocChanges' => 0,
            'applicableAuthProperties' => [],
            'tmChanges' => 0,
            'hasConditionsUndertakings' => false,
        ];
    }

    private function prepareIndex(array $post, $commandResponse, $form)
    {
        $this->sut->shouldReceive('params')->with('continuationDetailId')->andReturn(99);
        $this->sut->shouldReceive('handleQuery')->andReturn(
            m::mock()->shouldReceive('isOk')->andReturn(true)->getMock()
                ->shouldReceive('getResult')->andReturn($this->indexData())->getMock()
        );
        $this->request->setMethod('POST');
        $this->request->setPost(new \Laminas\Stdlib\Parameters($post));
        $this->sut->shouldReceive('getForm')->andReturn($form);
        if ($commandResponse !== null) {
            $this->sut->shouldReceive('handleCommand')->once()
                ->with(IsEqual::equalTo(UpdateSmsNumber::create(['id' => 77, 'smsNumber' => '+447700900123'])))
                ->andReturn($commandResponse);
        } else {
            $this->sut->shouldReceive('handleCommand')->never();
        }
    }

    private function aForm(bool $valid)
    {
        $form = m::mock(\Common\Form\Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->andReturn($valid);
        $form->shouldReceive('get->get->getValue')->andReturn('+447700900123');
        return $form;
    }

    public function testValidPostSavesSmsThenRedirects(): void
    {
        $this->prepareIndex(
            ['smsNumber' => ['smsNumber' => '07700900123']],
            m::mock()->shouldReceive('isOk')->andReturn(true)->getMock(),
            $this->aForm(true)
        );
        $redirect = m::mock();
        $redirect->shouldReceive('toRoute')->once()
            ->with(ChecklistController::FINANCES_ROUTE, [], [], true)->andReturn('redirected');
        $this->sut->shouldReceive('redirect')->andReturn($redirect);

        $this->assertSame('redirected', $this->sut->indexAction());
    }

    public function testFailedSaveShowsErrorAndRedisplays(): void
    {
        $this->prepareIndex(
            ['smsNumber' => ['smsNumber' => '07700900123']],
            m::mock()->shouldReceive('isOk')->andReturn(false)->getMock(),
            $this->aForm(true)
        );
        $this->sut->shouldReceive('redirect')->never();
        $this->sut->shouldReceive('addErrorMessage')->once()->with('unknown-error');

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $this->sut->indexAction());
    }

    public function testGetDoesNotSave(): void
    {
        $this->sut->shouldReceive('params')->with('continuationDetailId')->andReturn(99);
        $this->sut->shouldReceive('handleQuery')->andReturn(
            m::mock()->shouldReceive('isOk')->andReturn(true)->getMock()
                ->shouldReceive('getResult')->andReturn($this->indexData())->getMock()
        );
        $this->request->setMethod('GET');
        $this->sut->shouldReceive('getForm')->andReturn(m::mock(\Common\Form\Form::class));
        $this->sut->shouldReceive('handleCommand')->never();
        $this->sut->shouldReceive('redirect')->never();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $this->sut->indexAction());
    }

    public function testInvalidPostDoesNotSave(): void
    {
        $this->prepareIndex(['smsNumber' => ['smsNumber' => '']], null, $this->aForm(false));
        $this->sut->shouldReceive('redirect')->never();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $this->sut->indexAction());
    }

    public function testUsersAction(): void
    {
        $continuationId = 99;

        $this->sut->shouldReceive('params')->with('continuationDetailId')->once()->andReturn($continuationId);

        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->with(IsEqual::equalTo(LicenceChecklist::create(['id' => $continuationId])))
            ->andReturn($this->aMockResultWithUserData());

        $view = $this->sut->usersAction();
        $this->assertEquals('layouts/simple', $view->getTemplate());

        $userSectionView = $view->getChildren()[0];
        $this->assertEquals('pages/continuation-section', $userSectionView->getTemplate());

        $expected =  [
            'licNo' => '1234',
            'data' =>
                 [
                    0 =>
                         [
                            0 =>
                                 [
                                    'value' => 'continuations.users-section.table.name',
                                    'header' => true,
                                ],
                            1 =>
                                 [
                                    'value' => 'continuations.users-section.table.email',
                                    'header' => true,
                                ],
                            2 =>
                                 [
                                    'value' => 'continuations.users-section.table.permission',
                                    'header' => true,
                                ],
                        ],
                    1 =>
                         [
                            0 =>
                                 [
                                    'value' => 'Test1 Test1',
                                ],
                            1 =>
                                 [
                                    'value' => 'test1@test.com',
                                ],
                            2 =>
                                 [
                                    'value' => 'role.operator',
                                ],
                        ],
                    2 =>
                         [
                            0 =>
                                 [
                                    'value' => 'Test2 Test2',
                                ],
                            1 =>
                                 [
                                    'value' => 'test2@test.com',
                                ],
                            2 =>
                                 [
                                    'value' => 'role.operator',
                                ],
                        ],
                ],
            'totalMessage' => 'continuations.users-section-header',
            'totalCount' => 2,
        ];

        $this->assertEquals($expected, $userSectionView->getVariables());
    }

    /**
     * @return m\LegacyMockInterface|m\MockInterface
     */
    private function aMockResultWithUserData()
    {
        return m::mock()->shouldReceive('isOk')->andReturn(true)->getMock()->shouldReceive('getResult')->andReturn(
            [
                'licence' =>
                    [
                        'licNo' => '1234',
                        'organisation' =>
                            [
                                'organisationUsers' =>
                                    [
                                        [
                                            'user' =>
                                                [
                                                    'contactDetails' =>
                                                        [
                                                            'emailAddress' => 'test1@test.com',
                                                            'person' =>
                                                                [
                                                                    'familyName' => 'Test1',
                                                                    'forename' => 'Test1',
                                                                ],
                                                        ],
                                                    'id' => 543,
                                                    'roles' =>
                                                        [
                                                            [
                                                                'description' => 'Operator',
                                                                'id' => 27,
                                                                'role' => 'operator',
                                                            ],
                                                        ],
                                                ]
                                        ],
                                        [
                                            'user' =>
                                                [
                                                    'contactDetails' =>
                                                        [
                                                            'emailAddress' => 'test2@test.com',
                                                            'person' =>
                                                                [
                                                                    'familyName' => 'Test2',
                                                                    'forename' => 'Test2',
                                                                ],
                                                        ],
                                                    'id' => 544,
                                                    'roles' =>
                                                        [
                                                            [
                                                                'description' => 'Operator',
                                                                'id' => 27,
                                                                'role' => 'operator',
                                                            ],
                                                        ],

                                                ]
                                        ]
                                    ]
                            ]
                    ],

            ]
        )->getMock();
    }
}

<?php

/**
 * Fees action trait tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Traits;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Common\BusinessService\Response;

/**
 * Fees action trait tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 *
 * @NOTE I have removed a test from this class, as it isn't testing what it appeared to be testing
 */
class FeesActionTraitTest extends AbstractHttpControllerTestCase
{
    protected $sut;

    protected $sm;

    public function setUpAction()
    {
        $this->sut = m::mock('\Olcs\Controller\Licence\LicenceController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);

        // stub search form
        $this->sm->setService(
            'Helper\Form',
            m::mock()
                ->shouldReceive('createForm')
                ->with('HeaderSearch', false, false)
                ->andReturn(
                    m::mock()->shouldReceive('bind')->getMock()
                )
                ->getMock()
        );
    }

    /**
     * Test edit fee action with form alteration
     *
     * @group feesTrait
     * @dataProvider feeStatusesProvider
     */
    public function testEditFeeActionWithFormAlteration(
        $statusId,
        $statusDescription,
        $paymentMethodId,
        $paymentMethodDescription
    ) {
        $this->markTestSkipped('TODO');
        $this->setUpAction();

        $feeId = 1;
        $feeDetails = [
            'id' => 1,
            'description' => 'desc',
            'amount' => 123.12,
            'invoicedDate' => '2014-01-01 10:10:10',
            'receiptNo' => '123',
            'receivedAmount' => 123.12,
            'receivedDate' => '2014-01-01 10:10:10',
            'waiveReason' => 'waive reason',
            'version' => 1,
            'feeStatus' => [
                'id' => $statusId,
                'description' => $statusDescription
            ],
            'paymentMethod' => [
                'id' => $paymentMethodId,
                'description' => $paymentMethodDescription,
            ],
            'lastModifiedBy' => [
                'id' => 1,
                'name' => 'Some User'
            ],
            'payingInSlipNumber' => '1234',
            'payerName' => 'P. Ayer',
            'chequePoNumber' => '234567',
        ];

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('fee', null)
                ->andReturn($feeId)
                ->getMock()
            );

        $mockFeeService = m::mock()
            ->shouldReceive('getFee')
            ->with($feeId)
            ->andReturn($feeDetails)
            ->getMock();

        switch ($statusId) {
            case 'lfs_ot':
                $mockFeeForm = m::mock()
                    ->shouldReceive('remove')
                    ->andReturn(null)
                    ->shouldReceive('get')
                    ->with('form-actions')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('remove')
                            ->with('approve')
                            ->andReturn(null)
                            ->shouldReceive('remove')
                            ->with('reject')
                            ->andReturn(null)
                            ->getMock()
                    )
                    ->shouldReceive('get')
                    ->with('fee-details')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('get')
                            ->with('waiveReason')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setAttribute')
                                    ->with('disabled', 'disabled')
                                    ->andReturn(null)
                                    ->getMock()
                                    ->shouldReceive('setValue')
                                    ->with($feeDetails['waiveReason'])
                                    ->andReturn(null)
                                    ->getMock()
                            )
                            ->shouldReceive('get')
                            ->with('id')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setValue')
                                    ->with($feeDetails['id'])
                                    ->andReturn(null)
                                    ->getMock()
                            )
                            ->shouldReceive('get')
                            ->with('version')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setValue')
                                    ->with($feeDetails['version'])
                                    ->andReturn(null)
                                    ->getMock()
                            )
                            ->getMock()
                    )
                    ->getMock();
                break;
            case 'lfs_wr':
                $mockFeeForm = m::mock()
                    ->shouldReceive('remove')
                    ->andReturn(null)
                    ->shouldReceive('get')
                    ->with('form-actions')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('remove')
                            ->with('recommend')
                            ->andReturn(null)
                            ->getMock()
                    )
                    ->shouldReceive('get')
                    ->with('fee-details')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('get')
                            ->with('waiveReason')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setAttribute')
                                    ->with('disabled', 'disabled')
                                    ->andReturn(null)
                                    ->getMock()
                                    ->shouldReceive('setValue')
                                    ->with($feeDetails['waiveReason'])
                                    ->andReturn(null)
                                    ->getMock()
                            )
                            ->shouldReceive('get')
                            ->with('id')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setValue')
                                    ->with($feeDetails['id'])
                                    ->andReturn(null)
                                    ->getMock()
                            )
                            ->shouldReceive('get')
                            ->with('version')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setValue')
                                    ->with($feeDetails['version'])
                                    ->andReturn(null)
                                    ->getMock()
                            )
                            ->getMock()
                    )
                    ->getMock();
                break;
            case 'lfs_w':
                $mockFeeForm = m::mock()
                    ->shouldReceive('remove')
                    ->andReturn(null)
                    ->shouldReceive('remove')
                    ->with('form-actions')
                    ->andReturn(null)
                    ->shouldReceive('get')
                    ->with('fee-details')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('get')
                            ->with('waiveReason')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setAttribute')
                                    ->with('disabled', 'disabled')
                                    ->andReturn(null)
                                    ->getMock()
                                    ->shouldReceive('setValue')
                                    ->with($feeDetails['waiveReason'])
                                    ->andReturn(null)
                                    ->getMock()
                            )
                            ->shouldReceive('get')
                            ->with('id')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setValue')
                                    ->with($feeDetails['id'])
                                    ->andReturn(null)
                                    ->getMock()
                            )
                            ->shouldReceive('get')
                            ->with('version')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('setValue')
                                    ->with($feeDetails['version'])
                                    ->andReturn(null)
                                    ->getMock()
                            )
                            ->getMock()
                    )
                    ->getMock();
                break;
            case 'lfs_pd':
            case 'lfs_cn': // no form for paid and cancelled statues
                $mockFeeForm = null;
                break;
            default:
                break;
        }

        $this->sut
            ->shouldReceive('getForm')
            ->with('fee')
            ->andReturn($mockFeeForm);

        $this->sm->setService('Olcs\Service\Data\Fee', $mockFeeService);

        $response = $this->sut->editFeeAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function feeStatusesProvider()
    {
        return [
            ['lfs_ot', 'Outstanding', null, null],
            ['lfs_wr', 'Waive recommended', null, null],
            ['lfs_w', 'Waived', null, null],
            ['lfs_cn', 'Cancelled', null, null],
            ['lfs_pd', 'Paid', 'fpm_cash', 'Cash'],
            ['lfs_pd', 'Paid', 'fpm_cheque', 'Cheque'],
            ['lfs_pd', 'Paid', 'fpm_po', 'Postal Order'],
            ['lfs_pd', 'Paid', 'fpm_card_offline', 'Card'],
        ];
    }

    /**
     * Test add fee action GET
     *
     * @group feesTrait
     */
    public function testEditAddFeeActionGet()
    {
        $this->markTestSkipped('TODO');
        $this->setUpAction();

        // mocks
        $mockCreateFeeForm = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockDetailsFieldset = m::mock();
        $mockCreatedDateField = m::mock();

        // expectations
        $this->sut
            ->shouldReceive('getForm')
            ->with('create-fee')
            ->andReturn($mockCreateFeeForm);

        $mockCreateFeeForm
            ->shouldReceive('get')
            ->with('fee-details')
            ->andReturn($mockDetailsFieldset);
        $mockDetailsFieldset
            ->shouldReceive('get')
            ->with('createdDate')
            ->andReturn($mockCreatedDateField);
        $mockFormHelper
            ->shouldReceive('setDefaultDate')
            ->with($mockCreatedDateField)
            ->once();

        $this->sut->addFeeAction();
    }

    /**
     * Test add fee action POST with successful response from Business Service
     *
     * @group feesTrait
     */
    public function testEditAddFeeActionPostSuccess()
    {
        $this->markTestSkipped('TODO');
        $this->setUpAction();

        // stub data
        $postData = [
            'fee-details' => [
                'id' => '',
                'version' => '',
                'feeType' => '20051',
                'createdDate' => [
                    'day' => '15',
                    'month' => '04',
                    'year' => '2015',
                ],
                'amount' => '123.45',
            ],
        ];

        $userId = 101;

        // mocks
        $mockCreateFeeForm = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockRequest = m::mock();
        $mockBsm = m::mock();
        $this->sm->setService('BusinessServiceManager', $mockBsm);
        $mockFeeBusinessService = m::mock();

        // expectations
        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('getForm')
            ->with('create-fee')
            ->andReturn($mockCreateFeeForm);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $mockCreateFeeForm
            ->shouldReceive('remove')
            ->with('csrf')
            ->andReturnSelf()
            ->shouldReceive('setData')
            ->with($postData)
            ->once()
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getData')
            ->once()
            ->andReturn(
                [
                    'fee-details' => [
                        'id' => '',
                        'version' => '',
                        'feeType' => '20051',
                        'createdDate' => '2015-040-15',
                        'amount' => '123.45',
                    ]
                ]
            );

        $this->sut
            ->shouldReceive('getLoggedInUser')
            ->andReturn($userId);
        $mockBsm
            ->shouldReceive('get')
            ->with('Fee')
            ->andReturn($mockFeeBusinessService);
        $mockFeeBusinessService
            ->shouldReceive('process')
            ->with(
                [
                    'fee-details' => [
                        'id' => '',
                        'version' => '',
                        'feeType' => '20051',
                        'createdDate' => '2015-040-15',
                        'amount' => '123.45',
                    ],
                    'user' => $userId,
                ]
            )
            ->andReturn(new Response(Response::TYPE_SUCCESS));

        $this->sut
            ->shouldReceive('redirectToList')
            ->shouldReceive('getResponse->getContent')->andReturn('REDIRECT');

        $this->sut->addFeeAction();
    }

    /**
     * Test add fee action POST with failure response from Business Service
     *
     * @group feesTrait
     */
    public function testEditAddFeeActionPostFail()
    {
        $this->markTestSkipped('TODO');
        $this->setUpAction();

        // mocks
        $mockCreateFeeForm = m::mock();
        $mockFormHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $mockRequest = m::mock();
        $mockBsm = m::mock();
        $this->sm->setService('BusinessServiceManager', $mockBsm);
        $mockFeeBusinessService = m::mock();

        // expectations
        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('getForm')
            ->with('create-fee')
            ->andReturn($mockCreateFeeForm);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn([]);

        $mockCreateFeeForm
            ->shouldReceive('remove')
            ->with('csrf')
            ->andReturnSelf()
            ->shouldReceive('setData')
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn([]);

        $this->sut
            ->shouldReceive('getLoggedInUser');

        $mockBsm
            ->shouldReceive('get')
            ->with('Fee')
            ->andReturn($mockFeeBusinessService);
        $mockFeeBusinessService
            ->shouldReceive('process')
            ->andReturn(new Response(Response::TYPE_FAILED));

        $this->sut
            ->shouldReceive('redirectToList')
            ->shouldReceive('getResponse->getContent')->andReturn('REDIRECT');

        $this->sut->addFeeAction();
    }

    public function testAddFeeActionPostCancel()
    {
        $this->markTestSkipped('TODO');
        $this->setUpAction();

        // stub data
        $postData = [
            'form-actions' => [
                'cancel' => '',
            ],
        ];

        // mocks
        $mockCreateFeeForm = m::mock();
        $mockRequest = m::mock();
        $mockRedirect = m::mock();

        // expectations
        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('getForm')
            ->with('create-fee')
            ->andReturn($mockCreateFeeForm);

        $mockRequest
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($postData);

        $this->sut
            ->shouldReceive('redirectToList')
            ->andReturn($mockRedirect);

        // assertions
        $this->assertSame($mockRedirect, $this->sut->addFeeAction());
    }
}

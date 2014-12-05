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

/**
 * Fees action trait tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 *
 * @NOTE I have removed a test from this class, as it isn't testing what it appeared to be testing
 */
class FeesActionTraitTest extends AbstractHttpControllerTestCase
{
    protected $post = [];

    protected $mockRedirect;

    /**
     * Set up
     */
    public function setUpAction()
    {
        $this->sut = m::mock('\Olcs\Controller\Licence\LicenceController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);
    }

    /**
     * Test edit fee action with form alteration
     *
     * @group feesTrait
     * @dataProvider feeStatusesProvider
     * @return array
     */
    public function testEditFeeActionWithFormAlteration($statusId, $statusDescription)
    {
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
                'id' => 'fpm_cash',
                'description' => 'Cash'
            ],
            'lastModifiedBy' => [
                'id' => 1,
                'name' => 'Some User'
            ]
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
            ['lfs_ot', 'Outstanding'],
            ['lfs_wr', 'Waive recommended'],
            ['lfs_w', 'Waived'],
            ['lfs_pd', 'Paid'],
            ['lfs_cn', 'Cancelled']
        ];
    }
}

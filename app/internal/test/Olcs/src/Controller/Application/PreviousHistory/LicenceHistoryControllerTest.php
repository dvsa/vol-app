<?php

/**
 * LicenceHistoryControllerTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\PreviousHistory;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * LicenceHistoryControllerTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceHistoryControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\Common\Controller\Application\PreviousHistory\LicenceHistoryController';

    protected $defaultRestResponse = array();

    protected $post = array(
        'dataLicencesCurrent' => array(
            'id' => '',
            'version' => '2',
            'prevHasLicence' => 'Y'
        ),
        'table-licences-current' => array(
            'rows' => 1
        ),
        'dataLicencesApplied' => array(
            'prevHadLicence' => 'N'
        ),
        'table-licences-applied' => array(
            'rows' => 0
        ),
        'dataLicencesRevoked' => array(
            'prevBeenRevoked' => 'N'
        ),
        'table-licences-revoked' => array(
            'rows' => 0
        ),
        'dataLicencesRefused' => array(
            'prevBeenRefused' => 'N',
        ),
        'table-licences-refused' => array(
            'rows' => 0
        ),
        'dataLicencesDisqualified' => array(
            'prevBeenDisqualifiedTc' => 'N',
        ),
        'table-licences-disqualified' => array(
            'rows' => 0
        ),
        'dataLicencesPublicInquiry' => array(
            'prevBeenAtPi' => 'N'
        ),
        'table-licences-public-inquiry' => array(
            'rows' => 0
        ),
        'dataLicencesHeld' => array(
            'prevPurchasedAssets' => 'N'
        ),
        'table-licences-held' => array(
            'rows' => 0
        ),
        'form-actions' => array(
            'submit' => ''
        ),
        'js-submit' => '1'
    );

    /**
     * @var bool
     */
    private $willSurrender;

    /**
     * Test back button
     */
    public function testBackButton()
    {
        $this->setUpAction('index', null, array('form-actions' => array('back' => 'Back')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction('index', null, $this->post);
        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a response not a view
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud add current licence action
     */
    public function testIndexActionWithCrudAddCurrentLicenceSubmit()
    {
        unset($this->post['form-actions']);
        $this->willSurrender = true;
        $this->post['table-licences-current'] = array('action' => 'Add', 'rows' => 0);
        $this->setUpAction('index', null, $this->post);
        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a response not a view
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud edit current licence action
     */
    public function testIndexActionWithCrudEditCurrentLicenceSubmit()
    {
        unset($this->post['form-actions']);
        $this->post['table-licences-current'] = array('action' => 'Edit', 'rows' => 1);
        $this->setUpAction('index', null, $this->post);
        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a response not a view
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with crud delete current licence action
     */
    public function testIndexActionWithCrudDeleteCurrentLicenceSubmit()
    {
        unset($this->post['form-actions']);
        $this->post['table-licences-current'] = array('action' => 'Delete', 'rows' => 1);
        $this->setUpAction('index', null, $this->post);
        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a response not a view
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesCurrentAddAction
     */
    public function testTableLicencesCurrentAddAction()
    {
        $this->setUpAction('table-licences-current-add');
        $response = $this->controller->tableLicencesCurrentAddAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesCurrentEditAction with willSurrender flag
     */
    public function testTableLicencesWillSurrenderCurrentEditAction()
    {
        $this->willSurrender = true;
        $this->setUpAction('table-licences-current-edit');
        $response = $this->controller->tableLicencesCurrentEditAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesCurrentEditAction
     */
    public function testTableLicencesCurrentEditAction()
    {
        $this->willSurrender = false;
        $this->setUpAction('table-licences-current-edit');
        $response = $this->controller->tableLicencesCurrentEditAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesCurrentDeleteAction
     */
    public function testTableLicencesCurrentDeleteAction()
    {
        $this->setUpAction('table-licences-current-delete');
        $response = $this->controller->tableLicencesCurrentDeleteAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesAppliedAddAction
     */
    public function testTableLicencesAppliedAddAction()
    {
        $this->setUpAction('table-licences-applied-add');
        $response = $this->controller->tableLicencesAppliedAddAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesAppliedEditAction
     */
    public function testTableLicencesAppliedEditAction()
    {
        $this->setUpAction('table-licences-applied-edit');
        $response = $this->controller->tableLicencesAppliedEditAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesAppliedDeleteAction
     */
    public function testTableLicencesAppliedDeleteAction()
    {
        $this->setUpAction('table-licences-applied-delete');
        $response = $this->controller->tableLicencesAppliedDeleteAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesRefusedAddAction
     */
    public function testTableLicencesRefusedAddAction()
    {
        $this->setUpAction('table-licences-refused-add');
        $response = $this->controller->tableLicencesRefusedAddAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesRefusedEditAction
     */
    public function testTableLicencesRefusedEditAction()
    {
        $this->setUpAction('table-licences-refused-edit');
        $response = $this->controller->tableLicencesRefusedEditAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesRefusedDeleteAction
     */
    public function testTableLicencesRefusedDeleteAction()
    {
        $this->setUpAction('table-licences-refused-delete');
        $response = $this->controller->tableLicencesRefusedDeleteAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesRevokedAddAction
     */
    public function testTableLicencesRevokedAddAction()
    {
        $this->setUpAction('table-licences-revoked-add');
        $response = $this->controller->tableLicencesRevokedAddAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesRevokedEditAction
     */
    public function testTableLicencesRevokedEditAction()
    {
        $this->setUpAction('table-licences-revoked-edit');
        $response = $this->controller->tableLicencesRevokedEditAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesRevokedDeleteAction
     */
    public function testTableLicencesRevokedDeleteAction()
    {
        $this->setUpAction('table-licences-revoked-delete');
        $response = $this->controller->tableLicencesRevokedDeleteAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesPublicInquiryAddAction
     */
    public function testTableLicencesPublicInquiryAddAction()
    {
        $this->setUpAction('table-licences-public-inquiry-add');
        $response = $this->controller->tableLicencesPublicInquiryAddAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesPublicInquiryEditAction
     */
    public function testTableLicencesPublicInquiryEditAction()
    {
        $this->setUpAction('table-licences-public-inquiry-edit');
        $response = $this->controller->tableLicencesPublicInquiryEditAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesPublicInquiryDeleteAction
     */
    public function testTableLicencesPublicInquiryDeleteAction()
    {
        $this->setUpAction('table-licences-public-inquiry-delete');
        $response = $this->controller->tableLicencesPublicInquiryDeleteAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesDisqualifiedAddAction
     */
    public function testTableLicencesDisqualifiedAddAction()
    {
        $this->setUpAction('table-licences-disqualified-add');
        $response = $this->controller->tableLicencesDisqualifiedAddAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesDisqualifiedEditAction
     */
    public function testTableLicencesDisqualifiedEditAction()
    {
        $this->setUpAction('table-licences-Disqualified-edit');
        $response = $this->controller->tableLicencesDisqualifiedEditAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesDisqualifiedDeleteAction
     */
    public function testTableLicencesDisqualifiedDeleteAction()
    {
        $this->setUpAction('table-licences-disqualified-delete');
        $response = $this->controller->tableLicencesDisqualifiedDeleteAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesHeldAddAction
     */
    public function testTableLicencesHeldAddAction()
    {
        $this->setUpAction('table-licences-held-add');
        $response = $this->controller->tableLicencesHeldAddAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesHeldEditAction
     */
    public function testTableLicencesHeldEditAction()
    {
        $this->setUpAction('table-licences-held-edit');
        $response = $this->controller->tableLicencesHeldEditAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesHeldDeleteAction
     */
    public function testTableLicencesHeldDeleteAction()
    {
        $this->setUpAction('table-licences-held-delete');
        $response = $this->controller->tableLicencesHeldDeleteAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test tableLicencesCurrentAddAction with submit
     */
    public function testTableLicencesCurrentAddActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-current-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'CURRENT',
                    'licNo' => '1',
                    'holderName' => 'current',
                    'willSurrender' => 'Y'
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesCurrentAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesCurrentEditAction with submit
     */
    public function testTableLicencesCurrentEditActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-current-edit',
            1,
            array(
                'data' => array(
                    'id' => '1',
                    'version' => '',
                    'previousLicenceType' => 'CURRENT',
                    'licNo' => '1',
                    'holderName' => 'current',
                    'willSurrender' => 'Y'
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesCurrentEditAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesAppliedAddAction with submit
     */
    public function testTableLicencesAppliedAddActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-applied-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'APPLIED',
                    'licNo' => '1',
                    'holderName' => 'appllied'
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesAppliedAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesAppliedEditAction with submit
     */
    public function testTableLicencesAppliedEditActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-applied-edit',
            1,
            array(
                'data' => array(
                    'id' => '1',
                    'version' => '',
                    'previousLicenceType' => 'APPLIED',
                    'licNo' => '1',
                    'holderName' => 'applied',
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesAppliedEditAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesRevokedAddAction with submit
     */
    public function testTableLicencesRevokedAddActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-revoked-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'REVOKED',
                    'licNo' => '1',
                    'holderName' => 'revoked'
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesRevokedAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesRevokedEditAction with submit
     */
    public function testTableLicencesRevokedEditActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-revoked-edit',
            1,
            array(
                'data' => array(
                    'id' => '1',
                    'version' => '',
                    'previousLicenceType' => 'REVOKED',
                    'licNo' => '1',
                    'holderName' => 'revoked',
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesRevokedEditAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesRefusedAddAction with submit
     */
    public function testTableLicencesRefusedAddActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-refused-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'REFUSED',
                    'licNo' => '1',
                    'holderName' => 'refused'
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesRefusedAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesRefusedEditAction with submit
     */
    public function testTableLicencesRefusedEditActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-refused-edit',
            1,
            array(
                'data' => array(
                    'id' => '1',
                    'version' => '',
                    'previousLicenceType' => 'REFUSED',
                    'licNo' => '1',
                    'holderName' => 'refused',
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesRefusedEditAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesPublicInquiryAddAction with submit
     */
    public function testTableLicencesPublicInquiryAddActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-public-inquiry-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'PUBLICINQUIRY',
                    'licNo' => '1',
                    'holderName' => 'publicinquiry'
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesPublicInquiryAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesPublicInquiryEditAction with submit
     */
    public function testTableLicencesPublicInquiryEditActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-public-inquiry-edit',
            1,
            array(
                'data' => array(
                    'id' => '1',
                    'version' => '',
                    'previousLicenceType' => 'PUBLICINQUIRY',
                    'licNo' => '1',
                    'holderName' => 'publicinquiry',
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesPublicInquiryEditAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesDisqualifiedAddAction with submit
     */
    public function testTableLicencesDisqualifiedAddActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-disqualified-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'DISQUALIFIED',
                    'licNo' => '1',
                    'holderName' => 'disqualified',
                    'disqualificationDate' => array('month' => '01', 'day' => '01', 'year' => '2010'),
                    'disqualificationLength' => '1'
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesDisqualifiedAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesDisqualifiedEditAction with submit
     */
    public function testTableLicencesDisqualifiedEditActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-disqualified-edit',
            1,
            array(
                'data' => array(
                    'id' => '1',
                    'version' => '',
                    'previousLicenceType' => 'DISQUALIFIED',
                    'licNo' => '1',
                    'holderName' => 'disqualified',
                    'disqualificationDate' => array('month' => '01', 'day' => '01', 'year' => '2010'),
                    'disqualificationLength' => '1'
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesDisqualifiedAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesHeldAddAction with submit
     */
    public function testTableLicencesHeldAddActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-held-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'HELD',
                    'licNo' => '1',
                    'holderName' => 'held',
                    'purchaseDate' => array('month' => '01', 'day' => '01', 'year' => '2010')
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesHeldAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesHeldEditAction with submit
     */
    public function testTableLicencesHeldEditActionWithSubmit()
    {
        $this->setUpAction(
            'table-licences-held-edit',
            1,
            array(
                'data' => array(
                    'id' => '1',
                    'version' => '',
                    'previousLicenceType' => 'HELD',
                    'licNo' => '1',
                    'holderName' => 'held',
                    'purchaseDate' => array('month' => '01', 'day' => '01', 'year' => '2010')
                ),
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesHeldEditAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test TableLicencesCurrentEditAction with cancel
     */
    public function testTableLicencesCurrentEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('table-licences-current-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesCurrentEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test TableLicencesAppliedEditAction with cancel
     */
    public function testTableLicencesAppliedEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('table-licences-applied-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesAppliedEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test TableLicencesRefusedEditAction with cancel
     */
    public function testTableLicencesRefusedEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('table-licences-refused-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesRefusedEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test TableLicencesRevokedEditAction with cancel
     */
    public function testTableLicencesRevokedEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('table-licences-revoked-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesRevokedEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test TableLicencesPublicInquiryEditAction with cancel
     */
    public function testTableLicencesPublicInquiryEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('table-licences-public-inquiry-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesPublicInquiryEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test TableLicencesDisqualifiedEditAction with cancel
     */
    public function testTableLicencesDisqualifiedEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('table-licences-disqualified-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesDisqualifiedEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test TableLicencesHeldEditAction with cancel
     */
    public function testTableLicencesHeldEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('table-licences-held-edit', 1, $post);

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesHeldEditAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesCurrentAddActionAddAnother
     */
    public function testTableLicencesCurrentAddActionAddAnother()
    {
        $this->setUpAction(
            'table-licences-current-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'CURRENT',
                    'licNo' => '1',
                    'holderName' => 'current',
                    'willSurrender' => 'Y'
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesCurrentAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesAppliedAddActionAddAnother
     */
    public function testTableLicencesAppliedAddActionAddAnother()
    {
        $this->setUpAction(
            'table-licences-applied-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'APPLIED',
                    'licNo' => '1',
                    'holderName' => 'applied',
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesAppliedAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesRefusedAddActionAddAnother
     */
    public function testTableLicencesRefusedAddActionAddAnother()
    {
        $this->setUpAction(
            'table-licences-refused-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'REFUSED',
                    'licNo' => '1',
                    'holderName' => 'refused',
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesRefusedAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesRevokedAddActionAddAnother
     */
    public function testTableLicencesRevokedAddActionAddAnother()
    {
        $this->setUpAction(
            'table-licences-revoked-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'REVOKED',
                    'licNo' => '1',
                    'holderName' => 'revoked',
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesRevokedAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesPublicInquiryAddActionAddAnother
     */
    public function testTableLicencesPublicInquiryAddActionAddAnother()
    {
        $this->setUpAction(
            'table-licences-public-inquiry-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'PUBLICINQUIRY',
                    'licNo' => '1',
                    'holderName' => 'publicinquiry',
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesPublicInquiryAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesDisqualifiedAddActionAddAnother
     */
    public function testTableLicencesDisqualifiedAddActionAddAnother()
    {
        $this->setUpAction(
            'table-licences-disqualified-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'DISQUALIFIED',
                    'licNo' => '1',
                    'holderName' => 'disqualified',
                    'disqualificationDate' => array('month' => '01', 'day' => '01', 'year' => '2010'),
                    'disqualificationLength' => '1'
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesDisqualifiedAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test tableLicencesHeldAddActionAddAnother
     */
    public function testTableLicencesHeldAddActionAddAnother()
    {
        $this->setUpAction(
            'table-licences-held-add',
            null,
            array(
                'data' => array(
                    'id' => '',
                    'version' => '',
                    'previousLicenceType' => 'HELD',
                    'licNo' => '1',
                    'holderName' => 'held',
                    'purchaseDate' => array('month' => '01', 'day' => '01', 'year' => '2010'),
                ),
                'form-actions' => array(
                    'addAnother' => 'Add another'
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->tableLicencesHeldAddAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Mock the rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    protected function mockRestCalls($service, $method, $data = array(), $bundle = array())
    {
        if ($service == 'Application' && $method == 'GET' && $bundle == ApplicationController::$licenceDataBundle) {

            return $this->getLicenceData('goods');
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }
        $previousLicenceBundle = array(
            'properties' => array(
                'id',
                'version',
                'licNo',
                'holderName',
                'willSurrender',
                'purchaseDate',
                'disqualificationDate',
                'disqualificationLength',
                'previousLicenceType'
             )
        );
        if ($service == 'PreviousLicence' && $method == 'GET' && $bundle == $previousLicenceBundle) {
            return array(
                'id' => 1,
                'version' => 1,
                'licNo' => 'ln',
                'holderName' => 'hn',
                'willSurrender' => $this->willSurrender,
                'purchaseDate' => '',
                'disqualificationDate' => '',
                'disqualificationLength' => '1',
                'previousLicenceType' => 'CURRENT'
            );
        }

        if ($service == 'PreviousLicence' && $method == 'POST') {
            return array('id' => 1);
        }
        if ($service == 'PreviousLicence' && $method == 'PUT') {
            return array(
                'id' => 1,
                'version' => 1,
                'licNo' => 'ln',
                'holderName' => 'hn',
                'willSurrender' => true,
                'purchaseDate' => array('month' => '1', 'day' => '1', 'year' => '2010'),
                'disqualificationDate' => array('month' => '1', 'day' => '1', 'year' => '2010'),
                'disqualificationLength' => '1',
                'previousLicenceType' => 'CURRENT'
            );
        }

        if ($service == 'Application' && $method == 'GET') {
            return array(
                'id' => 1,
                'version' => 1,
                'prevHasLicence' => 'Y',
                'prevHadLicence' => 'N',
                'prevBeenRevoked' => 'N',
                'prevBeenRefused' => 'N',
                'prevBeenDisqualifiedTc' => 'N',
                'prevBeenAtPi' => 'N',
                'prevPurchasedAssets' => ''
            );
        }

    }
}

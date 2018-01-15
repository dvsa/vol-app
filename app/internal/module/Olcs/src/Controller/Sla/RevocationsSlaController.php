<?php


namespace Olcs\Controller\Sla;


use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\UpdateProposeToRevokeSla;
use Dvsa\Olcs\Transfer\Query\Cases\ProposeToRevoke\ProposeToRevokeByCase;
use Olcs\Controller\AbstractInternalController;
use Olcs\Form\Model\Form\RevocationsSla;

class RevocationsSlaController extends AbstractInternalController
{

    protected $formClass = RevocationsSla::class;

    protected $itemDto = ProposeToRevokeByCase::class;

    protected $updateCommand = UpdateProposeToRevokeSla::class;

    protected $addContentTitle = 'Add In Office Revocation Sla Target Dates';

    protected $editContentTitle = 'Edit In Office Revocation Sla Target Dates';

    protected $defaultData = [
        'case' => 'route'
    ];
    protected $mapperClass = \Olcs\Data\Mapper\RevocationsSla::class;

    protected $itemParams = ['case'];
    protected $redirectConfig = [
        'edit' => [
            'route' => 'processing_in_office_revocation',
            'action' => 'details'
        ]
     ];


    public function editAction()
    {
        return parent::editAction();
    }
}

<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva;
use Common\Controller\Lva\Traits\CrudTableTrait;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Olcs\Data\Mapper\Lva\PhoneContact as PhoneContactMapper;
use Olcs\Form\Model\Form\Lva\PhoneContact as AddEditForm;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Http\Response;

/**
 * Abstract class for internal module
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class AbstractAddressesController extends Lva\AbstractAddressesController
{
    use CrudTableTrait;

    const MODE_ADD = 'ADD';
    const MODE_EDIT = 'EDIT';

    private static $mapAddEditCmd = [
        self::MODE_ADD => TransferCmd\ContactDetail\PhoneContact\Create::class,
        self::MODE_EDIT => TransferCmd\ContactDetail\PhoneContact\Update::class,
    ];

    /**
     * Process crud action - Add
     *
     * @return \Common\View\Model\Section|Response
     * @throws \Exception
     */
    public function addAction()
    {
        return $this->processAddEdit(self::MODE_ADD);
    }

    /**
     * Process crud action - Edit
     *
     * @return \Common\View\Model\Section|Response
     * @throws \Exception
     */
    public function editAction()
    {
        return $this->processAddEdit(self::MODE_EDIT);
    }

    /**
     * Procees Add or Edit action
     *
     * @param string $mode Mode
     *
     * @return \Common\View\Model\Section|Response
     * @throws \Exception
     */
    private function processAddEdit($mode)
    {
        if (!in_array($mode, [self::MODE_ADD, self::MODE_EDIT], true)) {
            throw new \Exception('Invalid mode');
        }

        $this->initHelpers();

        $this->section = 'phone_contact';

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        /** @var \Zend\Form\Form $form */
        $form = $this->hlpForm->createFormWithRequest(AddEditForm::class, $request);
        if ($mode === self::MODE_EDIT) {
            $form->get('form-actions')->remove('addAnother');
        }

        if (!$request->isPost()) {
            $apiData = [];

            if ($mode === self::MODE_ADD) {
                //  get api data
                $response = $this->handleQuery(
                    TransferQry\Licence\Addresses::create(['id' => $this->getLicenceId()])
                );

                if (!$response->isOk()) {
                    return $this->notFoundAction();
                }

                $apiData = [
                    'contactDetails' => [
                        'id' => $response->getResult()['correspondenceCd']['id'],
                    ],
                ];
            } elseif ($mode === self::MODE_EDIT) {
                $response = $this->handleQuery(
                    TransferQry\ContactDetail\PhoneContact\Get::create(['id' => $this->params('child_id')])
                );

                $apiData = $response->getResult();
            }

            $form->setData(PhoneContactMapper::mapFromResult($apiData));
        }

        if ($request->isPost()) {
            $form->setData(
                (array)$request->getPost()
            );

            if ($form->isValid()) {
                $commandData = PhoneContactMapper::mapFromForm($form->getData());

                /** @var TransferCmd\CommandInterface $cmdClass $cmdClass */
                $cmdClass = self::$mapAddEditCmd[$mode];
                $response = $this->handleCommand($cmdClass::create($commandData));

                if ($response->isOk()) {
                    return $this->handlePostSave();
                }

                if ($response->isServerError()) {
                    $this->hlpFlashMsgr->addUnknownError();
                } elseif ($response->isClientError()) {
                    $flashErrors = PhoneContactMapper::mapFromErrors($form, $response->getResult());

                    foreach ($flashErrors as $error) {
                        $this->hlpFlashMsgr->addErrorMessage($error);
                    }
                }
            }
        }

        return $this->render(strtolower($mode . '_phone_contact'), $form);
    }

    /**
     * Process Delete crud action
     *
     * @return bool
     */
    protected function delete()
    {
        $this->section = 'phone_contact';

        $response = $this->handleCommand(
            TransferCmd\ContactDetail\PhoneContact\Delete::create(
                [
                    'id' => $this->params()->fromRoute('child_id')
                ]
            )
        );

        return $response->isOk();
    }

    /**
     * Check is form Valid, or ignore and do crud action
     *
     * @param Form  $form     Form
     * @param array $formData Form data
     *
     * @return bool
     */
    protected function isValid(Form $form, array $formData)
    {
        if (null !== $this->getCrudAction([$formData['table']])) {
            $this->hlpForm->disableValidation($form->getInputFilter());

            return true;
        }

        return parent::isValid($form, $formData);
    }

    /**
     * Save form or process Crud action
     *
     * @param array $formData Form Data
     *
     * @return array|bool|null|Response
     */
    protected function save(array $formData)
    {
        $crudAction = $this->getCrudAction([isset($formData['table']) ? $formData['table'] : null]);

        $response = parent::save($formData);
        if (
            $response !== true
            || $crudAction === null
        ) {
            return $response;
        }

        return $this->handleCrudAction($crudAction);
    }
}

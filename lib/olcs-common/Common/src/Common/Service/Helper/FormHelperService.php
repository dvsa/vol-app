<?php

namespace Common\Service\Helper;

use Laminas\Form\Annotation\AnnotationBuilder;
use Common\Form\Elements\Types\Address;
use Common\Service\Data\AddressDataService;
use Laminas\Form\Element;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use Laminas\Http\Request;
use Laminas\I18n\Validator\PostCode as PostcodeValidator;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\ValidatorChain;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 *           All validations to do with empty fields must be done as a validator
 *           within the fieldsets.  AllowEmpty is deprecated, so we use the NotEmpty
 *           validator.  All instances of AllowEmpty have been removed here for this
 *           primary reason.
 *
 * Form Helper Service
 */
class FormHelperService
{
    public const ALTER_LABEL_RESET = 0;

    public const ALTER_LABEL_APPEND = 1;

    public const ALTER_LABEL_PREPEND = 2;

    public function __construct(private AnnotationBuilder $formAnnotationBuilder, private array $config, private AuthorizationService $authorizationService, private RendererInterface $viewRenderer, private AddressDataService $addressData, private AddressHelperService $addressHelper, private DateHelperService $dateHelper, private TranslationHelperService $translationHelper)
    {
    }

    /**
     * Create a form
     *
     * @param string $formName    Form Name
     * @param bool   $addCsrf     Is need add CSRF field
     * @param bool   $addContinue Is need add Continue button
     *
     * @return \Common\Form\Form
     */
    public function createForm($formName, $addCsrf = true, $addContinue = true)
    {
        $class = class_exists($formName) ? $formName : $this->findForm($formName);

        /** @var \Common\Form\Form $form */
        $form = $this->formAnnotationBuilder->createForm($class);

        //  add CSRF element
        if ($addCsrf) {
            $config = [
                'type' => \Laminas\Form\Element\Csrf::class,
                'name' => 'security',
                'attributes' => [
                    'class' => 'js-csrf-token',
                ],
                'options' => [
                    'csrf_options' => [
                        'messageTemplates' => [
                            'notSame' => 'csrf-message',
                        ],
                        'timeout' => $this->config['csrf']['timeout'],
                    ],
                ],
            ];
            $form->add($config);
        }

        //  add button "Continue" element
        if ($addContinue) {
            $config = [
                'type' => \Laminas\Form\Element\Button::class,
                'name' => 'form-actions[continue]',
                'options' => [
                    'label' => 'Continue',
                ],
                'attributes' => [
                    'type' => 'submit',
                    'class' => 'govuk-visually-hidden',
                    'style' => 'display: none;',
                    'id' => 'hidden-continue',
                ],
            ];
            $form->add($config);
        }

        if ($this->authorizationService->isGranted('internal-user') && (!$this->authorizationService->isGranted('internal-edit') && !$form->getOption('bypass_auth'))) {
            $form->setOption('readonly', true);
        }

        return $form;
    }

    /**
     * Set Form Action From Request
     *
     * @param \Laminas\Form\FormInterface $form    Form
     * @param \Laminas\Http\Request       $request Request
     */
    public function setFormActionFromRequest($form, $request): void
    {
        if (!$form->hasAttribute('action')) {
            $url = $request->getUri()->getPath();
            $query = $request->getUri()->getQuery();

            if ($query !== '') {
                $url .= '?' . $query;
            } elseif (str_ends_with($url, '/')) {
                // @NOTE Had to add the following check in, as the trailing space hack was breaking filter forms
                if (strtoupper($form->getAttribute('method')) === 'GET') {
                    $url .= '?i=e';
                } else {
                    // WARNING: As rubbish as this looks, do *not* remove
                    // the trailing space. When rendering forms in modals,
                    // IE strips quote marks off attributes wherever possible.
                    // This means that an action of /foo/bar/baz/ will render
                    // without quotes, and the trailing slash will self-close
                    // and completely destroy the form
                    $url .= ' ';
                }
            }

            $form->setAttribute('action', $url);
        }
    }

    /**
     * Create Form With Request
     *
     * @param string             $formName Form name
     * @param \Laminas\Http\Request $request  Request
     *
     * @return FormInterface
     */
    public function createFormWithRequest($formName, $request)
    {
        $form = $this->createForm($formName);

        $this->setFormActionFromRequest($form, $request);

        return $form;
    }

    /**
     * Find form
     *
     * @param string $formName Form Name
     *
     * @return string
     */
    private function findForm($formName)
    {
        foreach (['Olcs', 'Common', 'Admin', 'Permits'] as $namespace) {
            $class = $namespace . '\Form\Model\Form\\' . $formName;

            if (class_exists($class)) {
                return $class;
            }
        }

        throw new \RuntimeException('Form does not exist: ' . $formName);
    }

    /**
     * Check for address lookups
     * Returns true if an address search is present, false otherwise
     *
     * @param Form $form Form
     * @param \Laminas\Http\Request       $request Request
     *
     * @return boolean
     */
    public function processAddressLookupForm(Form $form, Request $request)
    {
        $processed = false;
        $modified = false;
        $fieldsets = $form->getFieldsets();
        $post = (array)$request->getPost();

        foreach ($fieldsets as $fieldset) {
            if ($result = $this->processAddressLookupFieldset($fieldset, $post, $form)) {
                // @NOTE we can't just return true here, as any other address lookups need processing also
                $processed = true;

                if (is_array($result)) {
                    $modified = true;
                    $post = $result;
                }
            }
        }

        /**
         * A postcode -> address lookup will have modified the array of
         * POST data at an unknown level of nesting, so we need to make
         * one top-level call to re-populate the form data if so
         */
        if ($modified) {
            $form->setData($post);
        }

        return $processed;
    }

    /**
     * Process an address lookup fieldset
     *
     * @param \Laminas\Form\Fieldset      $fieldset Fieldset
     * @param array                    $post     Post data
     * @param \Laminas\Form\FormInterface $form     Form
     *
     * @return bool|array
     */
    private function processAddressLookupFieldset($fieldset, $post, $form)
    {
        $name = $fieldset->getName();

        if (!($fieldset instanceof Address)) {
            $data = $post[$name] ?? [];
            $processed = false;
            $modified = false;

            foreach ($fieldset->getFieldsets() as $fieldset) {
                if ($result = $this->processAddressLookupFieldset($fieldset, $data, $form)) {
                    $processed = true;

                    if (is_array($result)) {
                        $modified = true;
                        $post[$name] = $result;
                    }
                }
            }

            if ($modified) {
                return $post;
            }

            return $processed;
        }

        // If we have clicked the find address button
        if (isset($post[$name]['searchPostcode']['search']) && !empty($post[$name]['searchPostcode']['search'])) {
            $this->processPostcodeSearch($fieldset, $post, $name);
            return true;
        }

        // If we have selected an address
        if (isset($post[$name]['searchPostcode']['select']) && !empty($post[$name]['searchPostcode']['select'])) {
            $this->removeAddressSelectFields($fieldset);

            // manipulate the current level of post data, bearing in mind
            // we could be nested at this point...
            $post[$name] = $this->processAddressSelect($post, $name);

            // ... meaning we have to return the current level of post data so
            // it can bubble all the way back up to the top
            return $post;
        }

        $this->removeAddressSelectFields($fieldset);
        return false;
    }

    /**
     * Process postcode lookup
     *
     * @param \Laminas\Form\Fieldset $fieldset Fieldset
     * @param array               $post     Post data
     * @param string              $name     Field Name
     *
     * @return bool
     */
    private function processPostcodeSearch($fieldset, $post, $name)
    {
        $postcode = trim($post[$name]['searchPostcode']['postcode']);

        // If we haven't entered a postcode
        if ($postcode === '' || $postcode === '0') {
            $this->removeAddressSelectFields($fieldset);

            $fieldset->get('searchPostcode')->setMessages(['Please enter a postcode']);

            return false;
        }

        try {
            $addressList = $this->addressData->getAddressesForPostcode($postcode);
        } catch (\Exception) {
            // RestClient / ResponseHelper throw root exceptions :(
            $fieldset->get('searchPostcode')->setMessages(['postcode.error.not-available']);
            $this->removeAddressSelectFields($fieldset);
            return false;
        }

        // If we haven't found any addresses
        if (empty($addressList)) {
            $this->removeAddressSelectFields($fieldset);

            $fieldset->get('searchPostcode')->setMessages(['postcode.error.no-addresses-found']);

            return false;
        }

        $fieldset->get('searchPostcode')->get('addresses')->setValueOptions(
            $this->addressHelper->formatAddressesForSelect($addressList)
        );

        return true;
    }

    /**
     * Process address select
     *
     * @param array  $post Post data
     * @param string $name Name (unused)
     *
     * @return array
     */
    private function processAddressSelect($post, $name)
    {
        $address = $this->addressData->getAddressForUprn($post[$name]['searchPostcode']['addresses']);

        return $this->addressHelper->formatPostalAddress($address);
    }

    /**
     * Remove address select fields
     *
     * @param \Laminas\Form\Fieldset $fieldset Fieldset
     */
    private function removeAddressSelectFields($fieldset): void
    {
        $fieldset->get('searchPostcode')->remove('addresses');
        $fieldset->get('searchPostcode')->remove('select');
    }

    /**
     * Alter an elements label
     *
     * @param \Laminas\Form\Element $element Element
     * @param string             $label   Label text
     * @param int                $type    Alter type
     */
    public function alterElementLabel($element, $label, $type = self::ALTER_LABEL_RESET): void
    {
        if (in_array($type, [self::ALTER_LABEL_APPEND, self::ALTER_LABEL_PREPEND], false)) {
            $oldLabel = $element->getLabel();

            $label = $type == self::ALTER_LABEL_APPEND ? $oldLabel . $label : $label . $oldLabel;
        }

        $element->setLabel($label);
    }

    /**
     * When passed something like
     * $form, 'data->registeredAddress', this method will remove the element from the form and input filter
     *
     * @param \Laminas\Form\FormInterface $form             Form
     * @param string                   $elementReference Element ref
     *
     * @return $this
     */
    public function remove($form, $elementReference)
    {
        $filter = $form->getInputFilter();

        $this->removeElement($form, $filter, $elementReference);

        return $this;
    }

    /**
     * Remove element
     *
     * @param \Laminas\Form\FormInterface $form             Form
     * @param InputFilterInterface     $filter           Filter
     * @param string                   $elementReference Element ref
     */
    private function removeElement($form, InputFilterInterface $filter, $elementReference): void
    {
        [$form, $filter, $name] = $this->getElementAndInputParents($form, $filter, $elementReference);

        $form->remove($name);
        $filter->remove($name);
    }

    /**
     * Grab the parent input filter and fieldset from the top level form and input filter using the -> notation
     * i.e. data->field would return the data fieldset, data input filter and the string field
     *
     * @param \Laminas\Form\FormInterface $form             Form
     * @param InputFilterInterface     $filter           Filter
     * @param string                   $elementReference Element ref
     *
     * @return array
     */
    public function getElementAndInputParents($form, InputFilterInterface $filter, $elementReference)
    {
        if (str_contains($elementReference, '->')) {
            [$container, $elementReference] = explode('->', $elementReference, 2);

            return $this->getElementAndInputParents(
                $form->get($container),
                $filter->get($container),
                $elementReference
            );
        }

        return [$form, $filter, $elementReference];
    }

    /**
     * Disable empty validation
     *
     * @param Fieldset $form Form fieldset
     * @param InputFilter                                  $filter Filter
     */
    public function disableEmptyValidation(Fieldset $form, InputFilter $filter = null): void
    {
        if (!$filter instanceof \Laminas\InputFilter\InputFilter) {
            $filter = $form->getInputFilter();
        }

        /** @var \Laminas\Form\ElementInterface $element */
        foreach ($form->getElements() as $key => $element) {
            $value = $element->getValue();

            if (empty($value) || $element instanceof Checkbox) {
                $filter->get($key)
                    ->setRequired(false)
                    ->setValidatorChain(
                        new ValidatorChain()
                    );
            }
        }

        foreach ($form->getFieldsets() as $fieldset) {
            if ($filter->has($fieldset->getName())) {
                $this->disableEmptyValidation($fieldset, $filter->get($fieldset->getName()));
            }
        }
    }

    /**
     * Disable empty validation on a single element
     *
     * @param \Laminas\Form\FormInterface $form      Form
     * @param string                   $reference Element Ref
     */
    public function disableEmptyValidationOnElement($form, $reference): void
    {
        /** @var InputFilterInterface $filter */
        [, $filter, $name] = $this->getElementAndInputParents($form, $form->getInputFilter(), $reference);
        $filter->get($name)->setRequired(false);
    }


    /**
     * @param \Common\Service\Table\TableBuilder|\Mockery\LegacyMockInterface&\Mockery\MockInterface&\Common\Service\Table\TableBuilder $table
     */
    public function populateFormTable(Fieldset $fieldset, $table, $tableFieldsetName = null): void
    {
        $fieldset->get('table')->setTable($table, $tableFieldsetName);
        $fieldset->get('rows')->setValue(count($table->getRows()));
    }

    /**
     * Recurse through the form and the input filter to disable the final result
     *
     * @param \Laminas\Form\FormInterface      $form      Form
     * @param string                        $reference Ref
     * @param \Laminas\InputFilter\InputFilter $filter    Filter
     *
     * @return null
     */
    public function disableElement($form, $reference, $filter = null)
    {
        if ($filter === null) {
            $filter = $form->getInputFilter();
        }

        if (str_contains($reference, '->')) {
            [$index, $reference] = explode('->', $reference, 2);

            return $this->disableElement($form->get($index), $reference, $filter->get($index));
        }

        $element = $form->get($reference);

        if ($element instanceof DateSelect) {
            $this->disableDateElement($element);
        } else {
            $element->setAttribute('disabled', 'disabled');
        }

        $filter->get($reference)->setRequired(false);

        return null;
    }

    /**
     * Disable date element
     *
     * @param \Laminas\Form\Element\DateSelect $element Element
     */
    public function disableDateElement($element): void
    {
        $element->getDayElement()->setAttribute('disabled', 'disabled');
        $element->getMonthElement()->setAttribute('disabled', 'disabled');
        $element->getYearElement()->setAttribute('disabled', 'disabled');
    }

    /**
     * Enable date element
     *
     * @param \Laminas\Form\Element\DateSelect $element Element
     */
    public function enableDateElement($element): void
    {
        $element->getDayElement()->removeAttribute('disabled');
        $element->getMonthElement()->removeAttribute('disabled');
        $element->getYearElement()->removeAttribute('disabled');
    }

    /**
     * Enable DateTime element
     *
     * @param \Laminas\Form\Element\DateTimeSelect $element Element
     */
    public function enableDateTimeElement($element): void
    {
        $element->getDayElement()->removeAttribute('disabled');
        $element->getMonthElement()->removeAttribute('disabled');
        $element->getYearElement()->removeAttribute('disabled');
        $element->getHourElement()->removeAttribute('disabled');
        $element->getMinuteElement()->removeAttribute('disabled');
    }

    /**
     * Disable all elements recursively
     *
     * @param \Laminas\Form\Fieldset $elements Elements
     */
    public function disableElements($elements): void
    {
        if ($elements instanceof Fieldset) {
            foreach ($elements->getElements() as $element) {
                $this->disableElements($element);
            }

            foreach ($elements->getFieldsets() as $fieldset) {
                $this->disableElements($fieldset);
            }

            return;
        }

        if ($elements instanceof DateSelect) {
            $this->disableDateElement($elements);
            return;
        }

        if ($elements instanceof Element) {
            $elements->setAttribute('disabled', 'disabled');
        }
    }

    /**
     * Enable all elements recursively
     *
     * @param \Laminas\Form\Fieldset $elements Elements
     */
    public function enableElements($elements): void
    {
        if ($elements instanceof Fieldset) {
            foreach ($elements->getElements() as $element) {
                $this->enableElements($element);
            }

            foreach ($elements->getFieldsets() as $fieldset) {
                $this->enableElements($fieldset);
            }

            return;
        }

        if ($elements instanceof DateSelect) {
            $this->enableDateElement($elements);
            return;
        }

        if ($elements instanceof Element) {
            $elements->removeAttribute('disabled');
        }
    }

    /**
     * Disable field validation
     *
     * @param \Laminas\InputFilter\InputFilter $inputFilter Input Filter
     */
    public function disableValidation($inputFilter): void
    {
        if ($inputFilter instanceof InputFilter) {
            foreach ($inputFilter->getInputs() as $input) {
                $this->disableValidation($input);
            }

            return;
        }

        if ($inputFilter instanceof Input) {
            $inputFilter->setRequired(false);
            $inputFilter->setValidatorChain(new ValidatorChain());
        }
    }

    /**
     * Lock the element
     *
     * @param \Laminas\Form\Element $element Element
     * @param string             $message Message
     */
    public function lockElement(Element $element, $message): void
    {
        $lockView = new ViewModel(
            ['message' => $this->translationHelper->translate($message)]
        );
        $lockView->setTemplate('partials/lock');

        $label = $this->translationHelper->translate($element->getLabel());

        $element->setLabel($label . $this->viewRenderer->render($lockView));
        $element->setLabelOption('disable_html_escape', true);

        $attributes = $element->getLabelAttributes();

        if (!isset($attributes['class'])) {
            $attributes['class'] = '';
        }

        $element->setLabelAttributes($attributes);
    }

    /**
     * Remove a list of form fields
     *
     * @param Form $form Form
     * @param string                   $fieldset Name of Fieldset
     * @param array                    $fields   Names of Fields
     */
    public function removeFieldList(Form $form, $fieldset, array $fields): void
    {
        foreach ($fields as $field) {
            $this->remove($form, $fieldset . '->' . $field);
        }
    }

    /**
     * Check for company number lookups
     *
     * @param Form $form Form
     * @param array                    $data            Data
     * @param string                   $detailsFieldset Name of Details fieldset
     * @param string                   $addressFieldset Name of Address fieldset
     *
     * @NOTE Doesn't quite adhere to the same interface as the other process*LookupForm
     * methods as it already expects the presence of a company number field to have been
     * determined, and it expects an array of data rather than a request
     */
    public function processCompanyNumberLookupForm(Form $form, $data, $detailsFieldset, $addressFieldset = null): void
    {
        if (empty($data) && !isset($data['results'])) {
            $this->setCompanyNotFoundError($form, $detailsFieldset);
            return;
        }

        $result = $data['results'][0];
        $form->get($detailsFieldset)->get('name')->setValue($result['company_name'] ?? '');
        if (!$addressFieldset) {
            return;
        }
        if (!isset($result['registered_office_address'])) {
            return;
        }
        $this->populateRegisteredAddressFieldset(
            $form->get($addressFieldset),
            $result['registered_office_address']
        );
    }

    public function setCompanyNotFoundError($form, $detailsFieldset): void
    {
        $message = 'company_number.search_no_results.error';
        $this->setCompaniesHouseFormMessage($form, $detailsFieldset, $message);
    }

    /**
     * @psalm-param 'data' $detailsFieldset
     */
    public function setInvalidCompanyNumberErrors($form, $detailsFieldset): void
    {
        $message = 'company_number.length.validation.error';
        $this->setCompaniesHouseFormMessage($form, $detailsFieldset, $message);
    }

    /**
     * Remove a value option from an element
     *
     * @param \Laminas\Form\Element\(Select|Radio) $element Select element or a Radio group
     * @param string $index Index
     */
    public function removeOption(Element $element, $index): void
    {
        $options = $element->getValueOptions();

        if (isset($options[$index])) {
            unset($options[$index]);
            $element->setValueOptions($options);
        }
    }

    /**
     * Set current option of element
     *
     * @param \Laminas\Form\Element\(Select|Radio) $element Select element or a Radio group
     * @param string $index Index
     */
    public function setCurrentOption(Element $element, $index): void
    {
        $options = $element->getValueOptions();

        if (isset($options[$index])) {
            if (is_array($options[$index])) {
                $options[$index]['label'] = $this->translationHelper->translate($options[$index]['label']) . ' ' .
                    $this->translationHelper->translate('current.option.suffix');
            } else {
                $options[$index] = $this->translationHelper->translate($options[$index]) . ' ' .
                    $this->translationHelper->translate('current.option.suffix');
            }

            $element->setValueOptions($options);
        }
    }

    /**
     * Remove Validator
     *
     * @param \Laminas\Form\FormInterface $form           Form
     * @param string                   $reference      Field Ref
     * @param string                   $validatorClass Validator Class
     */
    public function removeValidator(FormInterface $form, $reference, $validatorClass): void
    {
        /** @var InputFilterInterface $filter */
        [, $filter, $field] = $this->getElementAndInputParents($form, $form->getInputFilter(), $reference);

        /** @var ValidatorChain $validatorChain */
        $validatorChain = $filter->get($field)->getValidatorChain();
        $newValidatorChain = new ValidatorChain();

        foreach ($validatorChain->getValidators() as $validator) {
            if (!($validator['instance'] instanceof $validatorClass)) {
                $newValidatorChain->attach($validator['instance']);
            }
        }

        $filter->get($field)->setValidatorChain($newValidatorChain);
    }

    /**
     * Attach Validator
     *
     * @param \Laminas\Form\FormInterface           $form      Form
     * @param string                             $reference Field Ref
     * @param \Laminas\Validator\ValidatorInterface $validator Validator Class
     */
    public function attachValidator(FormInterface $form, $reference, $validator): void
    {
        /** @var InputFilterInterface $filter */
        [, $filter, $field] = $this->getElementAndInputParents($form, $form->getInputFilter(), $reference);

        /** @var ValidatorChain $validatorChain */
        $validatorChain = $filter->get($field)->getValidatorChain();

        $validatorChain->attach($validator);
    }

    /**
     * Get Validator
     *
     * @param \Laminas\Form\FormInterface $form           Form
     * @param string                   $reference      Field Ref
     * @param string                   $validatorClass Validator Class
     */
    public function getValidator(FormInterface $form, $reference, $validatorClass)
    {
        /** @var InputFilterInterface $filter */
        [, $filter, $field] = $this->getElementAndInputParents($form, $form->getInputFilter(), $reference);

        /** @var ValidatorChain $validatorChain */
        $validatorChain = $filter->get($field)->getValidatorChain();

        foreach ($validatorChain->getValidators() as $validator) {
            if ($validator['instance'] instanceof $validatorClass) {
                return $validator['instance'];
            }
        }

        return null;
    }

    /**
     * Set appropriate default values on date fields
     *
     * @param \Laminas\Form\Element $field Field
     *
     * @return \Laminas\Form\Element
     */
    public function setDefaultDate($field)
    {
        // default to the current date if it is not set
        $currentValue = $field->getValue();
        $currentValue = trim($currentValue, '-'); // date element returns '--' when empty!
        if ($currentValue === '' || $currentValue === '0') {
            $today = $this->dateHelper->getDateObject();
            $field->setValue($today);
        }

        return $field;
    }

    /**
     * Populate an address fieldset using Companies House address data
     *
     * @param \Laminas\Form\Fieldset $fieldset address fieldset
     * @param array               $data     Companies House 'AddressLine' data
     *
     * @return \Laminas\Form\Fieldset
     */
    protected function populateRegisteredAddressFieldset($fieldset, $data)
    {
        $postal_code = $data['postal_code'] ?? '';
        $postcodeValidator = new PostcodeValidator(['locale' => 'en-GB']);
        $addressData['postcode'] = $postcodeValidator->isValid($postal_code) ? $postal_code : '';

        $mappedFields = [
            'locality' => 'town',
            'address_line_1' => 'addressLine1',
            'address_line_2' => 'addressLine2',
            'address_line_3' => 'addressLine3',
            'address_line_4' => 'addressLine4',
        ];
        foreach ($mappedFields as $key => $value) {
            $addressData[$value] = $data[$key] ?? '';
        }

        foreach ($addressData as $field => $value) {
            $fieldset->get($field)->setValue($value);
        }

        return $fieldset;
    }

    /**
     * Save form state data
     *
     * @param Form $form Form
     * @param array                    $data The form data to save
     */
    public function saveFormState(Form $form, $data): void
    {
        $sessionContainer = new \Laminas\Session\Container('form_state');
        $sessionContainer->offsetSet($form->getName(), $data);
    }

    /**
     * Restore form state
     *
     * @param Form $form Form
     */
    public function restoreFormState(Form $form): void
    {
        $sessionContainer = new \Laminas\Session\Container('form_state');
        if ($sessionContainer->offsetExists($form->getName())) {
            $form->setData($sessionContainer->offsetGet($form->getName()));
        }
    }

    /**
     * Remove Value Option
     *
     * @param \Laminas\Form\Element\(Select|Radio) $element Element (Select|Radio)
     * @param string $key Key
     */
    public function removeValueOption(Element $element, $key): void
    {
        $options = $element->getValueOptions();

        unset($options[$key]);

        $element->setValueOptions($options);
    }

    /**
     * @param $form
     * @param $detailsFieldset
     */
    protected function setCompaniesHouseFormMessage($form, $detailsFieldset, string $message): void
    {
        $form->get($detailsFieldset)->get('companyNumber')->setMessages(
            [
                'company_number' => [$this->translationHelper->translate($message)],
            ]
        );
    }
}

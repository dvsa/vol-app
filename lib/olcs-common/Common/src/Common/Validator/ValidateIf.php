<?php

namespace Common\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorPluginManagerAwareInterface;
use Laminas\Validator\ValidatorPluginManager;

/**
 * Class ValidateIf
 * @package Common\Validator
 */
class ValidateIf extends AbstractValidator implements ValidatorPluginManagerAwareInterface
{
    public const NO_CONTEXT = 'no_context';

    protected $messageTemplates = [
        self::NO_CONTEXT         => 'Context field was not found in the input',
    ];

    /**
     * @var string
     */
    protected $contextField = '';

    /**
     * @var array
     */
    protected $contextValues = [];

    /**
     * @var bool
     */
    protected $contextTruth = true;

    /**
     * @internal This is out of scope from ZF 2.4+.  This is only used for the custom validator.
     *           There is no need to remove this for compatibility.
     *
     * @var bool
     */
    protected $allowEmpty = false;

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * @var ValidatorPluginManager
     */
    protected $validatorPluginManager;

    /**
     * @var ValidatorChain
     */
    protected $validatorChain;

    /**
     * @var string
     */
    private $injectPostData;

    /**
     * @param array $contextValues
     * @return $this
     */
    public function setContextValues($contextValues)
    {
        $this->contextValues = (array) $contextValues;
        return $this;
    }

    /**
     * @return array
     */
    public function getContextValues()
    {
        return $this->contextValues;
    }

    /**
     * @param string $contextField
     * @return $this
     */
    public function setContextField($contextField)
    {
        $this->contextField = $contextField;
        return $this;
    }

    /**
     * @return string
     */
    public function getContextField()
    {
        return $this->contextField;
    }

    /**
     * @param boolean $contextTruth
     * @return $this
     */
    public function setContextTruth($contextTruth)
    {
        $this->contextTruth = $contextTruth;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getContextTruth()
    {
        return $this->contextTruth;
    }

    /**
     * @param boolean $allowEmpty
     */
    public function setAllowEmpty($allowEmpty): static
    {
        $this->allowEmpty = $allowEmpty;
        return $this;
    }

    /**
     * @return boolean
     */
    public function allowEmpty()
    {
        return $this->allowEmpty;
    }

    /**
     * @return $this
     */
    public function setValidators(array $validators)
    {
        $this->validators = $validators;
        return $this;
    }

    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * @param \Laminas\Validator\ValidatorChain $validatorChain
     * @return $this
     */
    public function setValidatorChain($validatorChain)
    {
        $this->validatorChain = $validatorChain;
        return $this;
    }

    /**
     * @param string $injectPostData
     */
    private function setInjectPostData($injectPostData): void
    {
        $this->injectPostData = $injectPostData;
    }

    /**
     * @return \Laminas\Validator\ValidatorChain
     */
    public function getValidatorChain()
    {
        if (is_null($this->validatorChain)) {
            $this->validatorChain = new ValidatorChain();
            $this->validatorChain->setPluginManager($this->getValidatorPluginManager());
            foreach ($this->getValidators() as $validator) {
                $this->validatorChain->attachByName(
                    $validator['name'],
                    $validator['options'] ?? [],
                    $validator['break_chain_on_failure'] ?? false
                );
            }
        }

        return $this->validatorChain;
    }

    /**
     * @return $this
     */
    #[\Override]
    public function setValidatorPluginManager(ValidatorPluginManager $validatorPluginManager)
    {
        $this->validatorPluginManager = $validatorPluginManager;
        return $this;
    }

    /**
     * @return ValidatorPluginManager
     */
    #[\Override]
    public function getValidatorPluginManager()
    {
        return $this->validatorPluginManager;
    }


    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @param null $context
     * @return bool
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        $this->injectPostData($context);

        if (array_key_exists($this->getContextField(), $context)) {
            if ((in_array($context[$this->getContextField()], $this->getContextValues()) ^ $this->getContextTruth()) === 0) {
                if ($this->allowEmpty() && empty($value)) {
                    return true;
                }

                $result = $this->getValidatorChain()->isValid($value, $context);
                if (!$result) {
                    $this->abstractOptions['messages'] = $this->getValidatorChain()->getMessages();
                }

                return $result;
            }
            return true;
        }

        $this->error(self::NO_CONTEXT);
        return false;
    }

    /**
     * Inject some POST data into the context
     *
     * @param array $context Context, POST data is inserted into
     */
    private function injectPostData(&$context = null): void
    {
        if (!empty($this->injectPostData)) {
            // insert data from POST into context
            $tmpPost = $_POST;
            $inputs = explode('->', $this->injectPostData);
            // set default value for context, in case it doesn't exists in POST data
            $context[end($inputs)] = null;
            foreach ($inputs as $name) {
                if (isset($tmpPost[$name])) {
                    $tmpPost = $tmpPost[$name];
                    if (is_scalar($tmpPost)) {
                        $context[$name] = $tmpPost;
                    }
                }
            }
        }
    }

    /**
     * @param array $options
     * @return AbstractValidator
     */
    #[\Override]
    public function setOptions($options = [])
    {
        if (isset($options['context_field'])) {
            $this->setContextField($options['context_field']);
        }

        if (isset($options['context_truth'])) {
            $this->setContextTruth($options['context_truth']);
        }

        if (isset($options['context_values'])) {
            $this->setContextValues($options['context_values']);
        }

        if (isset($options['allow_empty'])) {
            $this->setAllowEmpty($options['allow_empty']);
        }

        if (isset($options['inject_post_data'])) {
            $this->setInjectPostData($options['inject_post_data']);
        }

        return parent::setOptions($options);
    }
}

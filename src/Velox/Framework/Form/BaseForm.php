<?php
namespace Velox\Framework\Form;


use Velox\Framework\Http\ParameterBag;
use Velox\Framework\Http\Request;

class BaseForm {
    protected $fields = array();
    protected $entities = array();
    protected $nonMappedValues = array();
    protected $validationErrors = array();
    protected $validators = array();

    public function setFields(array $fields) {
        $this->fields = $fields;
        return $this;
    }

    public function getFields() {
        return $this->fields;
    }

    public function addField(FormField $field) {
        $this->fields[] = $field;
        return $this;
    }

    public function getField($fieldName) {
        foreach ($this->fields as $f) {
            if ($f->getFieldName() == $fieldName)
                return $f;
        }
        return null;
    }

    public function setEntity($entityInstance, $name = null) {
        if (is_null($name))
            $name = ltrim(strrchr(get_class($entityInstance), '\\'), '\\');
        $this->entities[$name] = clone $entityInstance;
    }

    public function getEntity($name) {
        if (array_key_exists($name, $this->entities))
            return $this->entities[$name];
        return null;
    }

    public function get($fieldName) {
        $field = $this->getField($fieldName);
        if (is_null($field))
            return null;

        if ($field->isMapped()) {
            $entityName = $field->getEntityName();
            if (!array_key_exists($entityName, $this->entities))
                throw new \LogicException(sprintf('Form: Entity "%s" is not set', $entityName));
            $entity = $this->entities[$entityName];
            return $entity->{'get'.ucfirst($field->getPropertyName())}();
        } else {
            return isset($this->nonMappedValues[$fieldName]) ? $this->nonMappedValues[$fieldName] : null;
        }
    }

    public function set($fieldName, $value) {
        $field = $this->getField($fieldName);
        if (is_null($field))
            return;

        if ($field->isMapped()) {
            $entityName = $field->getEntityName();
            if (!array_key_exists($entityName, $this->entities))
                throw new \LogicException(sprintf('Form: Entity "%s" is not set', $entityName));
            $entity = $this->entities[$entityName];
            $entity->{'set'.ucfirst($field->getPropertyName())}($value);
        } else {
            $this->nonMappedValues[$fieldName] = $value;
        }
    }

    public function validate() {
        $isValid = true;
        $this->validationErrors = array();
        foreach ($this->fields as $f) {
            $validators = $f->getValidators();
            foreach ($validators as $validator) {
                $result = call_user_func($validator, $this);
                if (!is_null($result) && $result !== false) {
                    $this->validationErrors[$f->getFieldName()] = $result;
                    $isValid = false;
                    break;
                }
            }
        }

        if ($isValid) {
            foreach ($this->validators as $validator) {
                $result = call_user_func($validator, $this);
                if (!is_null($result) && $result !== false) {
                    $this->validationErrors['_form'] = $result;
                    $isValid = false;
                    break;
                }
            }
        }

        return $isValid;
    }

    public function getValidationErrors() {
        return $this->validationErrors;
    }

    public function getError($fieldName) {
        return array_key_exists($fieldName, $this->validationErrors) ? $this->validationErrors[$fieldName] : '';
    }

    public function hasError($fieldName) {
        return array_key_exists($fieldName, $this->validationErrors);
    }

    public function formatError($fieldName, $format = '%s') {
        if (array_key_exists($fieldName, $this->validationErrors))
            return sprintf($format, $this->validationErrors[$fieldName]);
        return '';
    }

    public function getValidators() {
        return $this->validators;
    }

    public function setValidators(array $validators) {
        $this->validators = $validators;
        return $this;
    }

    public function addValidator(\Closure $validator) {
        $this->validators[] = $validator;
        return $this;
    }

    public function handle(ParameterBag $pb) {
        foreach ($this->fields as $f) {
            $fieldName = $f->getFieldName();
            $v = $pb->get($fieldName, null);
            if (!is_null($v))
                $this->set($fieldName, $v);
        }
    }
}
<?php
namespace Velox\Framework\Form;

class FormField {
    protected $fieldName;
    protected $entityName;
    protected $propertyName;
    protected $validators = [];

    public function __construct($fieldName, $entityName = null, $propertyName = null) {
        $this->fieldName = $fieldName;
        $this->entityName = $entityName;
        $this->propertyName = $propertyName;
    }

    public function getFieldName() {
        return $this->fieldName;
    }

    public function setFieldName($fieldName) {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function getEntityName() {
        return $this->entityName;
    }

    public function setEntityName($entityName) {
        $this->entityName = $entityName;
        return $this;
    }

    public function getPropertyName() {
        return $this->propertyName;
    }

    public function setPropertyName($propertyName) {
        $this->propertyName = $propertyName;
        return $this;
    }

    public function isMapped() {
        return !is_null($this->entityName) && !is_null($this->propertyName);
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
}
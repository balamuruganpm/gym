<?php
/**
 * Validator Core Engine
 * Provides server-side validation rules and data sanitization.
 */

class Validator {
    private $data;
    private $errors = [];

    public function __construct($data) {
        $this->data = $data;
    }

    public static function make($data, $rules) {
        $validator = new self($data);
        $validator->validate($rules);
        return $validator;
    }

    public function validate($rules) {
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = isset($this->data[$field]) ? $this->data[$field] : null;

            foreach ($fieldRules as $rule) {
                $ruleParams = [];
                if (strpos($rule, ':') !== false) {
                    list($rule, $paramStr) = explode(':', $rule, 2);
                    $ruleParams = explode(',', $paramStr);
                }

                switch ($rule) {
                    case 'required':
                        if ($value === null || trim($value) === '') {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
                        }
                        break;

                    case 'email':
                        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->addError($field, 'Please enter a valid email address.');
                        }
                        break;

                    case 'numeric':
                        if ($value !== null && $value !== '' && !is_numeric($value)) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a number.');
                        }
                        break;

                    case 'min':
                        $min = (int)$ruleParams[0];
                        if (is_numeric($value) && $value < $min) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min}.");
                        } elseif (is_string($value) && strlen($value) < $min) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.");
                        }
                        break;

                    case 'max':
                        $max = (int)$ruleParams[0];
                        if (is_numeric($value) && $value > $max) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max}.");
                        } elseif (is_string($value) && strlen($value) > $max) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters.");
                        }
                        break;

                    case 'date':
                        if ($value && !strtotime($value)) {
                            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is not a valid date.');
                        }
                        break;

                    case 'in':
                        if ($value && !in_array($value, $ruleParams)) {
                            $this->addError($field, 'The selected ' . str_replace('_', ' ', $field) . ' is invalid.');
                        }
                        break;
                }
            }
        }
    }

    public function passes() {
        return empty($this->errors);
    }

    public function fails() {
        return !$this->passes();
    }

    public function errors() {
        return $this->errors;
    }

    public function firstError() {
        if (empty($this->errors)) {
            return null;
        }
        $first = reset($this->errors);
        return is_array($first) ? $first[0] : $first;
    }

    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Sanitize string for output
     */
    public static function clean($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
}

<?php
declare(strict_types=1);

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $message = 'Tato položka je povinná.'): self
    {
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function email(string $field, string $message = 'Neplatná e-mailová adresa.'): self
    {
        if (isset($this->errors[$field])) {
            return $this; // Skip if already failed required validation
        }
        if (isset($this->data[$field]) && trim((string)$this->data[$field]) !== '') {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    public function phone(string $field, string $message = 'Neplatné telefonní číslo.'): self
    {
        if (isset($this->errors[$field])) {
            return $this;
        }
        if (isset($this->data[$field]) && trim((string)$this->data[$field]) !== '') {
            // Regex for czech/slovak phone numbers with optional +420 prefix and spaces
            $phone = str_replace(' ', '', (string)$this->data[$field]);
            if (!preg_match('/^(\+420|\+421)?\d{9}$/', $phone)) {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    public function zip(string $field, string $message = 'Neplatné PSČ.'): self
    {
        if (isset($this->errors[$field])) {
            return $this;
        }
        if (isset($this->data[$field]) && trim((string)$this->data[$field]) !== '') {
            $zip = str_replace(' ', '', (string)$this->data[$field]);
            if (!preg_match('/^\d{5}$/', $zip)) {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

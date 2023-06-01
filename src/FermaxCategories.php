<?php

declare(strict_types=1);

namespace Orbitadigital\Fermax;

use Orbitadigital\Odfiles\JsonImporter;

class FermaxCategories
{
    private $data = [];
    private $name = _PS_CORE_DIR_ . '/import/Fermax/data/FermaxJsonCategories.json';
    private $lastError = '';
    private $originalData = [];

    public function __construct()
    {
        $json = new JsonImporter($this->name);
        $this->originalData = $json->read();
        $this->data = $this->originalData;
    }

    /**
     * get $lastError
     * 
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * function to get id_category_default or create it
     * 
     * @param string $category
     * 
     * @return int 
     */
    public function get(string $category): int
    {
        if (empty($category)) {
            return 0;
        }

        if (!isset($this->data[$category])) {
            $this->data[$category] = 0;
        }

        return (int) $this->data[$category];
    }

    /**
     * function to update json
     * 
     * @return bool
     */
    public function save(): bool
    {
        if ($this->originalData == $this->data) {
            return true;
        }

        $json = new JsonImporter($this->name);
        if (!$json->save($this->data, $this->name)) {
            $this->lastError = $json->getLastError();
            return false;
        }

        return true;
    }
}

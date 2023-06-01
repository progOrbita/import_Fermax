<?php

declare(strict_types=1);

namespace Orbitadigital\Fermax;

use Orbitadigital\Fermax\ReadFile;
use Orbitadigital\Fermax\FermaxCategories;
use Orbitadigital\Odfiles\JsonImporter;
use Tools;

class Fermax
{
    private $header;
    private $key;
    private $name;
    private $lastError;
    private $multilingualFields = ['name', 'description', 'description_short'];
    private $gallery = ['img_main', 'img_1', 'img_2', 'img_3', 'img_4', 'img_5', 'img_6'];

    public function __construct(array $header, string $name, string $key)
    {
        $this->header = $header;
        $this->name = $name;
        $this->key = $key;
    }

    /**
     * function to get last error
     * 
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * function to get fermax data
     * 
     * @param bool $reload
     * 
     * @return array
     */
    public function getData(bool $reload = false): array
    {
        $fileName = _PS_CORE_DIR_ . '/import/Fermax/data/Fermax_' . Date('d-m-Y') . '.json';
        $json = new JsonImporter($fileName);
        if ($json->validateFile() && !$reload) {
            $data = $json->read();
            if (empty($data)) {
                $this->lastError = $json->getLastError();
            }

            return $data;
        }

        $data = $this->processData();
        if (empty($data)) {
            return $data;
        }

        if (!$json->save($data, $fileName)) {
            $this->lastError = $json->getLastError();
        }

        return $data;
    }

}

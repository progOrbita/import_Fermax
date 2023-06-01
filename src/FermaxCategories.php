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

}

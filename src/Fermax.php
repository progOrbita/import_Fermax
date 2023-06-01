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

}

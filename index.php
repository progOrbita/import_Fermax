<?php

declare(strict_types=1);


namespace Orbitadigital\Fermax;

include_once('../../config/config.inc.php');
require_once __DIR__ . '/vendor/autoload.php';
require_once '../files/vendor/autoload.php';
require_once '../arbol-categorias/src/Categories.php';

use Orbitadigital\Odfiles\Table;
use Tools;
use Db;
use Throwable;
use Categories;
use Orbitadigital\Fermax\FermaxTranslate;


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

$header = [
    "COD",
    "REFERENCIA",
    "NOMBRE",
    "categoria",
    "sub categoria",
    "DESCRIPCIÓN",
    "DETALLES TÉCNICOS",
    "IMAGEN PRINCIPAL",
    "VISTA LATERAL",
    "VISTA TRASERA",
    "IMAGEN DE COTAS",
    "ESQUEMA DE INSTALACIÓN UNIFILAR",
    "ESQUEMA DE CABLEADO",
    "OTRAS IMÁGENES",
    "EAN",
    "ALTO Embalaje (mm)",
    "ANCHO Embalaje (mm)",
    "PROFUNDIDAD Embalaje (mm)",
    "Peso producto embalado (kg)",
    "DECLARACION DE CONFORMIDAD",
    "FICHA TÉCNICA",
    "PVPR",
    "PVP",
    "HS Codes",
    "NFAM"
];

$new_header = [
    "reference",
    "supplier_reference",
    "name",
    "category",
    "subcategory",
    "description_short",
    "description",
    "img_main",
    "img_1",
    "img_2",
    "img_3",
    "img_4",
    "img_5",
    "img_6",
    "ean13",
    "height",
    "width",
    "depth",
    "weight",
    "DECLARACION DE CONFORMIDAD",
    "FICHA TÉCNICA",
    "minimun_price",
    "price",
    "HS Codes",
    "NFAM"
];


$fermax = new Fermax([$new_header, $header], __DIR__ . '/data/fermax (5).xlsx', 'reference');
$fermax_data = $fermax->getData((bool)Tools::getValue('reload', false));

if (empty($fermax_data)) {
    echo $fermax->getLastError();
    die;
}

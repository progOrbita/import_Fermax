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
$ps_data = Db::getInstance()->executeS('SELECT `id_product`, `reference` FROM `ps_product` WHERE `id_supplier` IN (4,5,6)');
if ($ps_data === false) {
    echo 'consulta erronea a la db';
    die;
}

$ps_data = array_combine(array_column($ps_data, 'reference'), array_column($ps_data, 'id_product'));
$supplier_references = array_unique(array_merge(array_keys($ps_data), array_keys($fermax_data)));
$res = [];
$cat = new Categories(1);
$translate = new FermaxTranslate(['name', 'description_short', 'description'], 'es');
foreach ($supplier_references as $reference) {
    try {
        if (!isset($ps_data[$reference])) {
                $res[] = ['reference' => $reference, 'res' => Actions::create($fermax_data[$reference], (bool)Tools::getValue('write', false))];

            continue;
        }

        if (!isset($fermax_data[$reference])) {
            $res[] = ['reference' => "" . $reference . "", 'res' => Actions::discontinue((int) $ps_data[$reference], (bool)Tools::getValue('write', false))];
            continue;
        }

        $res[] = ['reference' => $reference, 'res' => Actions::update((int) $ps_data[$reference], $fermax_data[$reference], (bool)Tools::getValue('write', false))];
    } catch (Throwable $e) {
        $res[] = ['reference' => $reference, 'res' => 'Excepción capturada: ' .  $e->getMessage()];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js">
    </script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js">
    </script>
    <title>Fermax</title>
</head>

<body>
    <?php
    echo Table::makeTable($res, ['Refencia de proveedor', 'Resultado']);
    echo 'Total de caracteres a traducir: ' . $translate->totalCount();
    ?>
</body>

<script>
    $(document).ready(function() {
        $('#data').DataTable({
            paging: false
        });
    });
</script>

</html>
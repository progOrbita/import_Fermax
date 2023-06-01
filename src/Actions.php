<?php

declare(strict_types=1);

namespace Orbitadigital\Fermax;

use Orbitadigital\Fermax\FermaxTranslate;
use Product;
use StockAvailable;
use Image;
use AdminImportControllerCore;

class Actions extends AdminImportControllerCore
{
    /**
     * make some product discontinued
     * 
     * @param int $idProduct
     * 
     * @return string
     */
    public static function discontinue(int $idProduct, bool $write = false): string
    {
        $product = new Product($idProduct);
        if ($product->id_category_default <= 0) {
            return 'El producto no existe';
        }

        if ($product->id_category_default == 10) {
            return 'El producto ya estaba descatalogado';
        }

        if (!$write) {
            return 'Producto a descatalogar';
        }

        $product->available_for_order = false;
        $product->visibility = 'none';
        $product->quantity = 0;
        StockAvailable::setQuantity($idProduct, 0, 0);
        $product->id_category_default = 10;
        $product->updateCategories([2, 10]);
        $product->show_price = false;
        if (!$product->save()) {
            return 'Error al descatalogar';
        }

        return 'Descatalogado correctamente';
    }

    /**
     * update ps product data 
     * 
     * @param int $idProduct
     * @param array $productData
     * 
     * @return string
     */
    public static function update(int $idProduct, array $productData, bool $write = false): string
    {
        global $cat;
        $changes = false;
        $product = new Product($idProduct);
        $product->loadStockData();
        if ($productData['quantity'] != $product->quantity) {
            $changes[] = 'quantity (' . $product->quantity . '->' . $productData['quantity'] . ')';
            $write && StockAvailable::setQuantity($idProduct, 0, $productData['quantity']);
            $product->quantity = $productData['quantity'];
        }

        if ($productData['id_supplier'] > 0 && $productData['supplier_reference']) {
            $product->addSupplierReference($productData['id_supplier'], 0, $productData['supplier_reference']);
        }

        if ($product->id_supplier != $productData['id_supplier']) {
            $changes[] = 'id_supplier (' . $product->id_supplier . '->' . $productData['id_supplier'] . ')';
        }

        if ($product->id_category_default == 10) {
            if ($productData['id_category_default'] < 1) {
                return 'No se ha podido recatalogar por problemas en la relacion de las categorias';
            }

            $product->available_for_order = true;
            $product->visibility = 'both';
            $product->show_price = true;
            $product->id_category_default = $productData['id_category_default'];
            $product->updateCategories($cat->getCacheCategories($productData['id_category_default']));
            $changes[] = 'Producto a recatalogar';
        }

        if (!$changes) {
            return 'Sin cambios';
        }

        if (!$write) {
            return 'Datos a actualizar: ' . implode(',', $changes);
        }

        if (!$product->update()) {
            return 'Error con la actualizacion';
        }

        return 'Actualizado: ' . implode(',', $changes);
    }

}

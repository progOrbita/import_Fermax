<?php

declare(strict_types=1);

namespace Orbitadigital\Fermax;

use Product;
use StockAvailable;
use Image;
use AdminImportControllerCore;
use Db;
use SpecificPriceRule;

class Actions extends AdminImportControllerCore
{
    /**
     * make some product discontinued
     * 
     * @param int $idProduct
     * @param bool $write
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
     * @param bool $write
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

        if ($product->id_supplier != $productData['id_supplier']) {
            $changes[] = 'id_supplier (' . $product->id_supplier . '->' . $productData['id_supplier'] . ')';
            if ($write) {
                if (!Db::getInstance()->delete('product_supplier', ' id_product=' . $product->id)) {
                    return 'Error con el delete de suppliers';
                }

                $product->addSupplierReference($productData['id_supplier'], 0, $productData['supplier_reference']);
                $product->id_supplier = $productData['id_supplier'];
                SpecificPriceRule::applyAllRules([(int)$product->id]);
            }
        }

        if ($product->id_category_default == 10) {
            if ($productData['id_category_default'] < 1) {
                return 'No se ha podido recatalogar por problemas en la relacion de las categorias';
            }

            $product->available_for_order = true;
            $product->visibility = 'both';
            $product->show_price = true;
            $product->id_category_default = $productData['id_category_default'];
            $write && $product->updateCategories($cat->getCacheCategories($productData['id_category_default']));
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

    /**
     * function to create product
     * 
     * @param array $productData
     * @param bool $write
     * 
     * @return string
     */
    public static function create(array $productData, bool $write = false): string
    {
        global $cat;
        global $translate;

        $count = $translate->countTranslate($productData);
        if (!$write) {
            return 'Producto a crear. Caracteres a traducir: ' . $count;
        }

        $translate->getTranslate($productData);
        foreach ($productData['name'] as $key => $value) {
            $productData['name'][$key] = "Fermax " . $productData['supplier_reference'] . " " . $productData['name'][$key];
            $name = $productData['name'][$key];
            if (strlen($productData['name'][$key]) > 128) {
                $productData["name"][$key] = substr($productData["name"][$key], 0, 125) . '...';
            }

            $description_short = $productData['description_short'][$key];
            $productData['description_short'][$key] = $name . " " . strip_tags($productData['description_short'][$key]);
            if (strlen($productData["description_short"][$key]) > 807) {
                $productData["description_short"][$key] = substr($productData["description_short"][$key], 0, 804) . '...';
            }

            $productData['description'][$key] = "<h3>" . $name . "</h3>" . $description_short . $productData['description'][$key];
        }

        $product = new Product();
        foreach ($productData as $key => $value) {
            if (!property_exists(new Product, $key) || $key == 'category') {
                continue;
            }

            if (!empty($productData[$key])) {
                if (is_array($productData[$key])) {
                    $product->{$key} = $productData[$key];
                    continue;
                }

                $product->{$key} = $productData[$key];
            }
        }

        $product->id_tax_rules_group = 1;
        if (!$product->save()) {
            return 'Error creacion del producto';
        }

        StockAvailable::setQuantity($product->id, 0, $productData['quantity']);
        $product->updateCategories($cat->getCacheCategories($productData['id_category_default']));
        $product->addSupplierReference($productData['id_supplier'], 0, $productData['supplier_reference']);
        if (!empty($productData['gallery'])) {
            foreach ($productData['gallery'] as $img) {
                if (empty($img)) {
                    continue;
                }
            }

                if (!self::createImg((int)$product->id, $img, $productData['name'])) {
                    return 'Producto creado, pero error con el creado de imagenes';
                }
            }
        }

        return 'Producto creado correctamente';
    }

    /**
     * create img product
     * 
     * @param int $idProduct
     * @param string $url
     * @param array $legend
     * 
     * @return bool
     */
    public static function createImg(int $idProduct, string $url, array $legend): bool
    {
        $image = new Image();
        $image->id_product = (int) $idProduct;
        $image->position = Image::getHighestPosition($idProduct) + 1;
        $image->legend = $legend;

        if (!Image::getCover($image->id_product)) {
            $image->cover = 1;
        } else {
            $image->cover = 0;
        }

        if (($image->validateFieldsLang(false, true)) !== true) {
            return false;
        }

        if (!$image->add()) {
            return false;
        }

        return (parent::copyImg($idProduct, $image->id, $url));
    }
}

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

    /**
     * process xslx data
     * 
     * @return array
     */
    private function processData(): array
    {
        $data = ReadFile::get($this->header, $this->name, $this->key);
        if (empty($data)) {
            return [];
        }

        if (!is_array($data)) {
            $this->lastError = $data;
            return [];
        }

        $categories = new FermaxCategories();
        foreach ($data as &$product) {
            foreach ($this->multilingualFields as $field) {
                $product[$field] = ['es' => $product[$field]];
            }

            $product["reference"] = $product["reference"];
            $product["height"] = $product["height"] / 10;
            $product["width"] = $product["width"] / 10;
            $product["depth"] = $product["depth"] / 10;
            $product["weight"] = round((float) $product["weight"]);
            $product["description"]['es'] = Tools::purifyHtml($this->getHtml($product["description"]['es']));
            $product["description_short"]['es'] = $this->getHtml($product["description_short"]['es']);
            $product["description_short"]['es'] = Tools::purifyHtml($product["description_short"]['es']);
            $product['gallery'] = [];
            foreach ($this->gallery as $field) {
                $product[$field] = explode(',', $product[$field]);
                $product['gallery'] = array_merge($product['gallery'], $product[$field]);
            }

            $product['gallery'] = array_filter(array_unique($product['gallery']));
            $product['price'] = round((float)$product['price'], 2);
            $product['minimun_price'] = round((float)$product['minimun_price'], 2);
            $product['id_category_default'] = $categories->get(str_replace(['á', 'à', 'é', 'è', 'í', 'ì', 'ó', 'ò', 'ú', 'ù'], ['a', 'a', 'e', 'e', 'i', 'i', 'o', 'o', 'u', 'u'], trim(strtolower($product['category']))));
            $product['id_supplier'] = 4;
            $product['supplier_name'] = 'MetaluxGeneral';
            if (strtolower(trim($product['subcategory'])) === 'repuestos') {
                $product['id_supplier'] = 5;
                $product['supplier_name'] = 'MetaluxRepuesto';
            }

            if ($product['minimun_price'] > 0) {
                $product['id_supplier'] = 6;
                $product['supplier_name'] = 'MetaluxNeto';
                $product['price'] = $product['minimun_price'];
            }

            $product['wholesale_price'] = round((float)($product['price'] - ($product['price'] * 0.15)), 2);
            $product['visibility'] = 'both';
            $product['on_sale'] = true;
            $product['active'] = true;
            $product['show_price'] = true;
            $product['quantity'] = 0; //TODO
            $product['available_for_order'] = true;
            $product['id_manufacturer'] = 95;
        }

        $categories->save();
        return $data;
    }

    /**
     * fucntion to build html on string 
     * 
     * @param string $text
     * 
     * @return string
     */
    private function getHtml(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $text = '<p>' . implode('</p><p>', array_filter(explode("\n", $text))) . '</p>';
        $text = preg_replace("/<p>-([^<]*)<\/p>/Ui", '<li>$1</li>', $text);
        $text = preg_replace("/<p>•([^<]*)<\/p>/Ui", '<li>$1</li>', $text);
        $text = preg_replace("/<\/li><p>/Ui", "</li><br><p>", $text);
        return $text;
    }
}

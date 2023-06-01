<?php

declare(strict_types=1);

namespace Orbitadigital\Fermax;

use Language;

class FermaxTranslate
{
    private $langs;
    private $lang;
    private $fields;
    private $totalCount = 0;

    public function __construct(array $fields, string $lang)
    {
        $this->langs = array_column(Language::getIsoIds(), 'iso_code');
        $this->langs = Language::getIsoIds();
        $this->langs = array_combine(array_column($this->langs, 'id_lang'), array_column($this->langs, 'iso_code'));
        $this->fields = $fields;
        $this->lang = $lang;
    }

    /**
     * function to translate to multilingual fields
     * 
     * @param array &$product
     * 
     * @return void
     */
    public function getTranslate(array &$product): void
    {
        foreach ($this->fields as $field) {
            foreach ($this->langs as $key => $lang) {
                if ($lang == $this->lang) {
                    $product[$field][$key] = $product[$field][$this->lang];
                    continue;
                }

                //TODO translate
                $product[$field][$key] = $product[$field][$this->lang];
            }

            unset($product[$field][$this->lang]);
        }
    }

    /**
     * function to count number of characters to translate
     * 
     * @param array $product
     * 
     * @return int
     */
    public function countTranslate(array $product): int
    {
        $count = 0;
        foreach ($this->fields as $field) {
            $count += strlen($product[$field][$this->lang]);
        }

        $count = $count * count($this->langs);
        $this->totalCount += $count;
        return $count;
    }

}

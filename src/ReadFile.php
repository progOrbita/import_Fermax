<?php

declare(strict_types=1);

namespace Orbitadigital\Fermax;

require_once __DIR__ . '/../../files/vendor/autoload.php';


class ReadFile
{
    /**
     * function to read xlsx files
     * 
     * @param array $header
     * @param string $name
     * @param array $key
     * 
     * @return mixed
     */
    public static function get(array $header, string $name, string $key = '')
    {
        $old_header = $header[1];
        $new_header = $header[0];
        if ($xlsx = SimpleXLSX::parse($name)) {
            // foreach ($xlsx->rows(0, 150) as $row) {
            foreach ($xlsx->rows(0, 866) as $row) {
                $data[] = $row;
            }
        } else {
            echo SimpleXLSX::parseError();
        }

        if (empty($data)) {
            return [];
        }

        if ($data[0] != $old_header) {
            return 'Header incorrecto';
        }

        unset($data[0]);
        foreach ($data as &$row) {
            $row = array_combine($new_header, $row);
            $row['reference'] = 'fermax-' . $row['supplier_reference'];
        }

        if (!empty($key) && in_array($key, $new_header)) {
            $data = array_combine(array_column($data, $key), $data);
        }

        return $data;
    }
}

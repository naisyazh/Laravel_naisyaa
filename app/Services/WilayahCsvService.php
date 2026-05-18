<?php

namespace App\Services;

use RuntimeException;
use SplFileObject;

class WilayahCsvService
{
    public function getProvinces(): array
    {
        return $this->mapOptions($this->readRows('provinces.csv'));
    }

    public function getRegencies(string $provinceId): array
    {
        return $this->mapOptions($this->filterRows('regencies.csv', 'province_id', $provinceId));
    }

    public function getDistricts(string $regencyId): array
    {
        return $this->mapOptions($this->filterRows('districts.csv', 'regency_id', $regencyId));
    }

    public function getVillages(string $districtId): array
    {
        return $this->mapOptions($this->filterRows('villages.csv', 'district_id', $districtId));
    }

    protected function readRows(string $fileName): array
    {
        return iterator_to_array($this->iterateCsv($fileName), false);
    }

    protected function filterRows(string $fileName, string $column, string $value): array
    {
        $rows = [];

        foreach ($this->iterateCsv($fileName) as $row) {
            if (($row[$column] ?? null) === $value) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    protected function iterateCsv(string $fileName): \Generator
    {
        $path = storage_path('app/wilayah/' . $fileName);

        if (! is_file($path)) {
            throw new RuntimeException("File wilayah tidak ditemukan: {$fileName}");
        }

        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(';', '"', '\\');

        $headers = null;

        foreach ($file as $row) {
            if ($row === false || $row === [null]) {
                continue;
            }

            $row = array_map(function ($value) {
                return is_string($value) ? trim($value, "\" \t\n\r\0\x0B") : $value;
            }, $row);

            if ($headers === null) {
                $headers = $row;
                continue;
            }

            $row = array_pad($row, count($headers), null);
            $row = array_slice($row, 0, count($headers));
            $assoc = array_combine($headers, $row);

            if ($assoc === false) {
                continue;
            }

            yield $assoc;
        }
    }

    protected function mapOptions(array $rows): array
    {
        return array_values(array_map(function (array $row) {
            return [
                'id' => (string) ($row['id'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
            ];
        }, $rows));
    }
}

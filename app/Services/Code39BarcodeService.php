<?php

namespace App\Services;

use InvalidArgumentException;

class Code39BarcodeService
{
    /**
     * @var array<string, string>
     */
    private const ENCODINGS = [
        '0' => 'nnnwwnwnn',
        '1' => 'wnnwnnnnw',
        '2' => 'nnwwnnnnw',
        '3' => 'wnwwnnnnn',
        '4' => 'nnnwwnnnw',
        '5' => 'wnnwwnnnn',
        '6' => 'nnwwwnnnn',
        '7' => 'nnnwnnwnw',
        '8' => 'wnnwnnwnn',
        '9' => 'nnwwnnwnn',
        'A' => 'wnnnnwnnw',
        'B' => 'nnwnnwnnw',
        'C' => 'wnwnnwnnn',
        'D' => 'nnnnwwnnw',
        'E' => 'wnnnwwnnn',
        'F' => 'nnwnwwnnn',
        'G' => 'nnnnnwwnw',
        'H' => 'wnnnnwwnn',
        'I' => 'nnwnnwwnn',
        'J' => 'nnnnwwwnn',
        'K' => 'wnnnnnnww',
        'L' => 'nnwnnnnww',
        'M' => 'wnwnnnnwn',
        'N' => 'nnnnwnnww',
        'O' => 'wnnnwnnwn',
        'P' => 'nnwnwnnwn',
        'Q' => 'nnnnnnwww',
        'R' => 'wnnnnnwwn',
        'S' => 'nnwnnnwwn',
        'T' => 'nnnnwnwwn',
        'U' => 'wwnnnnnnw',
        'V' => 'nwwnnnnnw',
        'W' => 'wwwnnnnnn',
        'X' => 'nwnnwnnnw',
        'Y' => 'wwnnwnnnn',
        'Z' => 'nwwnwnnnn',
        '-' => 'nwnnnnwnw',
        '.' => 'wwnnnnwnn',
        ' ' => 'nwwnnnwnn',
        '$' => 'nwnwnwnnn',
        '/' => 'nwnwnnnwn',
        '+' => 'nwnnnwnwn',
        '%' => 'nnnwnwnwn',
        '*' => 'nwnnwnwnn',
    ];

    public function toDataUri(
        string $value,
        int $barHeight = 56,
        int $narrowWidth = 3,
        int $wideWidth = 8,
        int $quietZone = 30
    ): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(
            $this->toSvg($value, $barHeight, $narrowWidth, $wideWidth, $quietZone)
        );
    }

    public function toSvg(
        string $value,
        int $barHeight = 56,
        int $narrowWidth = 3,
        int $wideWidth = 8,
        int $quietZone = 30
    ): string
    {
        $value = strtoupper(trim($value));

        if ($value === '') {
            throw new InvalidArgumentException('Barcode value cannot be empty.');
        }

        $characters = str_split('*' . $value . '*');
        $gapWidth = $narrowWidth;
        $currentX = $quietZone;
        $bars = [];

        foreach ($characters as $characterIndex => $character) {
            $pattern = self::ENCODINGS[$character] ?? null;

            if ($pattern === null) {
                throw new InvalidArgumentException("Unsupported Code39 character [{$character}].");
            }

            foreach (str_split($pattern) as $elementIndex => $element) {
                $width = $element === 'w' ? $wideWidth : $narrowWidth;
                $isBar = $elementIndex % 2 === 0;

                if ($isBar) {
                    $bars[] = sprintf(
                        '<rect x="%d" y="0" width="%d" height="%d" fill="#000000" shape-rendering="crispEdges" />',
                        $currentX,
                        $width,
                        $barHeight
                    );
                }

                $currentX += $width;
            }

            if ($characterIndex !== array_key_last($characters)) {
                $currentX += $gapWidth;
            }
        }

        $totalWidth = $currentX + $quietZone;

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d" role="img" aria-label="Barcode %3$s" preserveAspectRatio="xMidYMid meet" shape-rendering="crispEdges"><rect width="100%%" height="100%%" fill="#ffffff" />%4$s</svg>',
            $totalWidth,
            $barHeight,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            implode('', $bars)
        );
    }
}

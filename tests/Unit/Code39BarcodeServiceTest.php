<?php

namespace Tests\Unit;

use App\Services\Code39BarcodeService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class Code39BarcodeServiceTest extends TestCase
{
    public function test_it_generates_svg_markup_for_barang_code(): void
    {
        $service = new Code39BarcodeService();

        $svg = $service->toSvg('BRG00001');

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('<rect', $svg);
        $this->assertStringContainsString('aria-label="Barcode BRG00001"', $svg);
        $this->assertStringContainsString('fill="#ffffff"', $svg);
        $this->assertStringContainsString('shape-rendering="crispEdges"', $svg);
    }

    public function test_it_rejects_unsupported_characters(): void
    {
        $service = new Code39BarcodeService();

        $this->expectException(InvalidArgumentException::class);

        $service->toSvg('INV#001');
    }
}

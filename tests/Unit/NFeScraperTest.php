<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NFe\SefazCeScraperService;
use Illuminate\Support\Facades\Http;

class NFeScraperTest extends TestCase
{
    public function test_it_can_parse_sefaz_ce_html_correctly()
    {
        $html = file_get_contents(base_path('tests/Fixtures/nfe_mock.html'));

        Http::fake([
            '*' => Http::response(['xml' => $html], 200)
        ]);

        $scraper = new SefazCeScraperService();
        $dto = $scraper->scrape('http://nfce.sefaz.ce.gov.br/pages/ShowNFCe.html?p=23260210557159000599655060000218319506768427|2|1|19|17.98|2b7030414f54656c66347838496c6a3362654e4a776f542b3365413d|1|94607C6C84228EBFC29D53D31F8AF363639D1CD9');

        $this->assertEquals('12.345.678/0001-90', $dto->supplier_cnpj);
        $this->assertCount(2, $dto->items);

        $this->assertEquals('FARINHA DE TRIGO DONA BENTA 1KG', $dto->items[0]->original_name);
        $this->assertEquals(2.50, $dto->items[0]->quantity);
        $this->assertEquals('UN', $dto->items[0]->uom_symbol);
        $this->assertEquals(4.99, $dto->items[0]->unit_price);
        $this->assertEquals(12.47, $dto->items[0]->total_price);

        $this->assertEquals('REFRIGERANTE COCA COLA 2L', $dto->items[1]->original_name);
        $this->assertEquals(1.00, $dto->items[1]->quantity);
        $this->assertEquals('CX', $dto->items[1]->uom_symbol);
        $this->assertEquals(14.00, $dto->items[1]->unit_price);
        $this->assertEquals(14.00, $dto->items[1]->total_price);
    }
}

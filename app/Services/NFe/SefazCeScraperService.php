<?php

namespace App\Services\NFe;

use App\DTOs\NFeImportDTO;
use App\DTOs\NFeItemDTO;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Exception;

class SefazCeScraperService
{
    /**
     * @param string $url The NFC-e URL from the QR Code
     * @return NFeImportDTO
     */
    public function scrape(string $url): NFeImportDTO
    {
        // For SEFAZ-CE, the URL contains a "p" parameter which needs to be parsed
        // and sent via POST to their internal API. 
        // We will build a helper method for that.
        $html = $this->fetchHtml($url);

        return $this->parseHtml($html);
    }

    protected function fetchHtml(string $url): string
    {
        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['query'])) {
            throw new Exception("URL inválida de NFC-e. Faltam parâmetros.");
        }

        parse_str($parsedUrl['query'], $queryParams);

        if (!isset($queryParams['p'])) {
            throw new Exception("Parâmetro 'p' não encontrado na URL da SEFAZ-CE.");
        }

        $p = str_replace('%7C', '|', $queryParams['p']);
        $params = explode('|', $p);

        if (count($params) < 2) {
            throw new Exception("O parâmetro 'p' está mal formatado.");
        }

        $versao = $params[1];

        if ($versao == '2') {
            if (substr($params[0], 34, 1) === '9') {
                $postData = [
                    "chave_acesso" => $params[0] ?? '',
                    "versao_qrcode" => $params[1] ?? '',
                    "tipo_ambiente" => $params[2] ?? '',
                    "dia_data_emissao" => $params[3] ?? '',
                    "valor_total_nfce" => $params[4] ?? '',
                    "digVal" => $params[5] ?? '',
                    "identificador_csc" => $params[6] ?? '',
                    "codigo_hash" => $params[7] ?? '',
                ];
            } else {
                $postData = [
                    "chave_acesso" => $params[0] ?? '',
                    "versao_qrcode" => $params[1] ?? '',
                    "tipo_ambiente" => $params[2] ?? '',
                    "identificador_csc" => $params[3] ?? '',
                    "codigo_hash" => $params[4] ?? '',
                ];
            }

            $apiUrl = 'http://nfce.sefaz.ce.gov.br/nfce/api/notasFiscal/qrcodev2/';
        } else {
            // Simplified fallback for V1 or others - typically CE returns html if fetched with standard headers 
            // but we follow their exact frontend logic
            throw new Exception("Versão de QR Code não suportada pelo scraper no momento (apenas v2 é suportado).");
        }

        // We make the post request to the API
        $response = Http::post($apiUrl, $postData);

        if (!$response->successful()) {
            throw new Exception("Falha ao comunicar com a SEFAZ-CE (HTTP {$response->status()}).");
        }

        $data = $response->json();

        if (isset($data['erro']) && !empty($data['erro'])) {
            throw new Exception("SEFAZ retornou erro: " . $data['erro']);
        }

        if (!isset($data['xml']) || empty($data['xml'])) {
            throw new Exception("A SEFAZ não retornou o HTML da nota.");
        }

        return $data['xml']; // It actually returns HTML string inside 'xml' key
    }

    protected function parseHtml(string $html): NFeImportDTO
    {
        $crawler = new Crawler($html);

        // Find general info (fallback if needed)
        $accessKey = '';
        $cnpjElements = $crawler->filter('.text')->extract(['_text']);
        // Extractor heuristic: searching for CNPJ pattern inside the HTML
        $cnpj = null;
        foreach ($cnpjElements as $text) {
            if (preg_match('/CNPJ\s*:\s*([\d\.\-\/]+)/i', $text, $matches)) {
                $cnpj = trim($matches[1]);
                break;
            }
        }

        // Get the items table
        $items = collect();
        $crawler->filter('table#tabResult tr')->each(function (Crawler $node) use ($items) {
            try {
                // Name
                $txtTit = $node->filter('span.txtTit');
                if ($txtTit->count() === 0)
                    return;
                $name = trim($txtTit->text());

                // Quantity
                $qtyRaw = $node->filter('span.Rqtd')->count() ? $node->filter('span.Rqtd')->text() : '1';
                $qtyRaw = preg_replace('/[^0-9,.]/', '', $qtyRaw);
                $quantity = $qtyRaw === '' ? 1.0 : (float) str_replace(',', '.', str_replace('.', '', $qtyRaw));

                // UoM
                $uomRaw = $node->filter('span.RUN')->count() ? $node->filter('span.RUN')->text() : 'UN';
                preg_match('/UN:\s*([A-Za-z]+)/', $uomRaw, $uMatches);
                $uom = isset($uMatches[1]) ? trim($uMatches[1]) : trim(str_replace('UN:', '', $uomRaw));

                // Unit Price
                $vlUnitRaw = $node->filter('span.RvlUnit')->count() ? $node->filter('span.RvlUnit')->text() : '0';
                $vlUnitRaw = preg_replace('/[^0-9,.]/', '', $vlUnitRaw);
                $unitPrice = $vlUnitRaw === '' ? 0.0 : (float) str_replace(',', '.', str_replace('.', '', $vlUnitRaw));

                // Total Price
                $toTalRaw = $node->filter('span.valor')->count() ? $node->filter('span.valor')->text() : '0';
                $toTalRaw = preg_replace('/[^0-9,.]/', '', $toTalRaw);
                $totalPrice = $toTalRaw === '' ? 0.0 : (float) str_replace(',', '.', str_replace('.', '', $toTalRaw));

                $items->push(new NFeItemDTO(
                    original_name: $name,
                    quantity: $quantity,
                    uom_symbol: empty($uom) ? 'UN' : $uom,
                    unit_price: $unitPrice,
                    total_price: $totalPrice
                ));
            } catch (Exception $e) {
                // Skip unparsable rows
            }
        });

        return new NFeImportDTO(
            access_key: $accessKey,
            supplier_name: null, // Left empty for manual input or separate scraping logic
            supplier_cnpj: $cnpj,
            issue_date: null,
            items: $items
        );
    }
}

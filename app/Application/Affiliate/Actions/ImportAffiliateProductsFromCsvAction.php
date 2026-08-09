<?php

namespace App\Application\Affiliate\Actions;

use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\IntegrationLog;
use League\Csv\Reader;
use Throwable;

class ImportAffiliateProductsFromCsvAction
{
    /**
     * Colunas aceitas no CSV (cabeçalho na primeira linha): external_product_id,
     * name, description, category, brand, price, original_price,
     * commission_percentage, estimated_commission, affiliate_url, image_url,
     * availability — apenas `name` e `affiliate_url` são obrigatórias.
     *
     * @return array{processed: int, failed: int, errors: list<string>}
     */
    public function handle(AffiliateProgram $program, string $filePath): array
    {
        $startedAt = microtime(true);
        $processed = 0;
        $errors = [];

        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            foreach ($csv->getRecords() as $line => $record) {
                $result = $this->importRow($program, $record);

                if ($result === null) {
                    $processed++;
                } else {
                    $errors[] = 'Linha '.($line + 2).": {$result}";
                }
            }
        } catch (Throwable $exception) {
            $errors[] = "Falha ao ler o arquivo CSV: {$exception->getMessage()}";
        }

        $failed = count($errors);

        IntegrationLog::create([
            'application_id' => $program->application_id,
            'integration' => 'affiliate.csv_import',
            'status' => $failed === 0 ? IntegrationLog::STATUS_SUCCESS : ($processed === 0 ? IntegrationLog::STATUS_FAILED : IntegrationLog::STATUS_PARTIAL),
            'message' => "Import de produtos do programa '{$program->name}' via CSV.",
            'items_processed' => $processed,
            'items_failed' => $failed,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'context' => $errors === [] ? null : ['errors' => array_slice($errors, 0, 50)],
            'occurred_at' => now(),
        ]);

        return ['processed' => $processed, 'failed' => $failed, 'errors' => $errors];
    }

    /**
     * @param  array<string, string|null>  $record
     */
    private function importRow(AffiliateProgram $program, array $record): ?string
    {
        $name = trim((string) ($record['name'] ?? ''));
        $affiliateUrl = trim((string) ($record['affiliate_url'] ?? ''));

        if ($name === '') {
            return "campo 'name' é obrigatório.";
        }

        if ($affiliateUrl === '' || filter_var($affiliateUrl, FILTER_VALIDATE_URL) === false) {
            return "campo 'affiliate_url' é obrigatório e deve ser uma URL válida.";
        }

        foreach (['price', 'original_price', 'commission_percentage', 'estimated_commission'] as $numericField) {
            $value = $record[$numericField] ?? null;

            if ($value !== null && $value !== '' && ! is_numeric($value)) {
                return "campo '{$numericField}' deve ser numérico.";
            }
        }

        $externalProductId = trim((string) ($record['external_product_id'] ?? ''));

        $attributes = [
            'application_id' => $program->application_id,
            'affiliate_program_id' => $program->id,
            'name' => $name,
            'description' => $this->nullableString($record['description'] ?? null),
            'category' => $this->nullableString($record['category'] ?? null),
            'brand' => $this->nullableString($record['brand'] ?? null),
            'price' => $this->nullableNumeric($record['price'] ?? null),
            'original_price' => $this->nullableNumeric($record['original_price'] ?? null),
            'commission_percentage' => $this->nullableNumeric($record['commission_percentage'] ?? null),
            'estimated_commission' => $this->nullableNumeric($record['estimated_commission'] ?? null),
            'affiliate_url' => $affiliateUrl,
            'image_url' => $this->nullableString($record['image_url'] ?? null),
            'availability' => $this->nullableString($record['availability'] ?? null) ?? AffiliateProduct::AVAILABILITY_UNKNOWN,
            'last_checked_at' => now(),
        ];

        if ($externalProductId !== '') {
            AffiliateProduct::updateOrCreate(
                ['affiliate_program_id' => $program->id, 'external_product_id' => $externalProductId],
                $attributes + ['external_product_id' => $externalProductId]
            );
        } else {
            AffiliateProduct::create($attributes);
        }

        return null;
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableNumeric(?string $value): ?float
    {
        return ($value === null || $value === '') ? null : (float) $value;
    }
}

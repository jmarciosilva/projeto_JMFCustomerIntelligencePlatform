<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateConversion;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\Campaign;
use App\Models\IntegrationLog;
use Carbon\Carbon;
use League\Csv\Reader;

class ImportAffiliateConversionsFromCsvAction
{
    public function execute(string $filePath, Application $application, AffiliateProgram $program): array
    {
        $csv = Reader::createFromPath($filePath);
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($records as $index => $row) {
            try {
                $this->processRow($row, $application, $program);
                $successful++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = 'Linha '.($index + 2).': '.$e->getMessage();
            }
        }

        IntegrationLog::create([
            'application_id' => $application->id,
            'integration' => 'affiliate_conversions_csv',
            'status' => $failed === 0 ? 'success' : 'partial',
            'message' => ! empty($errors) ? implode("\n", $errors) : null,
            'items_processed' => $successful,
            'items_failed' => $failed,
            'occurred_at' => now(),
        ]);

        return [
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    private function processRow(array $row, Application $application, AffiliateProgram $program): void
    {
        $orderReference = trim($row['order_reference'] ?? '');
        if (empty($orderReference)) {
            throw new \Exception('order_reference é obrigatório');
        }

        $productName = trim($row['product_name'] ?? '');
        if (empty($productName)) {
            throw new \Exception('product_name é obrigatório');
        }

        $orderDateStr = trim($row['order_date'] ?? '');
        if (empty($orderDateStr)) {
            throw new \Exception('order_date é obrigatório');
        }

        try {
            $orderDate = Carbon::createFromFormat('Y-m-d', $orderDateStr);
        } catch (\Exception $e) {
            throw new \Exception('order_date inválida: use formato Y-m-d');
        }

        $productPrice = floatval($row['product_price'] ?? 0);
        $commissionRate = floatval($row['commission_rate'] ?? 0);
        $commissionValue = floatval($row['commission_value'] ?? 0);

        $affiliateProduct = $program->products()
            ->where('name', 'like', "%$productName%")
            ->first();

        if (! $affiliateProduct) {
            throw new \Exception("Produto '$productName' não encontrado no programa");
        }

        $campaignId = null;
        if (! empty($row['campaign_name'])) {
            $campaign = Campaign::where('application_id', $application->id)
                ->where('name', 'ilike', $row['campaign_name'])
                ->first();
            $campaignId = $campaign?->id;
        }

        AffiliateConversion::updateOrCreate(
            ['order_reference' => $orderReference],
            [
                'application_id' => $application->id,
                'affiliate_product_id' => $affiliateProduct->id,
                'affiliate_program_id' => $program->id,
                'campaign_id' => $campaignId,
                'affiliate_link_id' => null,
                'order_date' => $orderDate,
                'product_price' => $productPrice,
                'commission_rate' => $commissionRate,
                'commission_value' => $commissionValue,
                'status' => 'pending',
                'notes' => trim($row['notes'] ?? ''),
            ]
        );
    }
}

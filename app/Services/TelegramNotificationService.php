<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    public function sendSalePaid(Sale $sale): void
    {
        if (! config('services.telegram.enabled')) {
            return;
        }

        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (! $token || ! $chatId) {
            return;
        }

        $sale->loadMissing('outlet', 'cashier', 'payments');

        $paymentSummary = $sale->payments
            ->groupBy('method')
            ->map(fn ($rows) => $rows->sum('amount'))
            ->map(fn ($amount, $method) => strtoupper((string) $method).': Rp '.number_format((float) $amount, 0, ',', '.'))
            ->values()
            ->implode(', ');

        $message = implode("\n", array_filter([
            '✅ Transaksi Selesai',
            'No. Struk: '.$sale->receipt_number,
            'Outlet: '.($sale->outlet?->name ?? '-'),
            'Kasir: '.($sale->cashier?->name ?? '-'),
            'Waktu: '.$sale->paid_at?->format('Y-m-d H:i'),
            'Total: Rp '.number_format((float) $sale->grand_total, 0, ',', '.'),
            $paymentSummary ? 'Pembayaran: '.$paymentSummary : null,
        ]));

        try {
            $response = Http::timeout(3)
                ->retry(1, 100)
                ->post('https://api.telegram.org/bot'.$token.'/sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $message,
                ]);

            if ($response->failed()) {
                Log::warning('Telegram notification failed.', [
                    'sale_id' => $sale->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Telegram notification failed.', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

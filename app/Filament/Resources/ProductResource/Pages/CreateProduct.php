<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * Create a single product and attach it to the selected outlets.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $user = Auth::user();

        if (! $user) {
            throw ValidationException::withMessages([
                'outlet_ids' => 'User tidak terautentikasi.',
            ]);
        }

        $allowedOutletIds = $user->outlets()
            ->where('is_active', true)
            ->pluck('outlets.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $requestedOutletIds = collect($data['outlet_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $selectedOutletIds = array_values(array_intersect($requestedOutletIds, $allowedOutletIds));

        if (empty($selectedOutletIds)) {
            throw ValidationException::withMessages([
                'outlet_ids' => 'Pilih minimal satu outlet yang valid.',
            ]);
        }

        // Ensure the created product appears immediately in the current outlet context.
        Session::put('active_outlet_id', $selectedOutletIds[0]);

        $tagIds = collect($data['tags'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $baseData = Arr::except($data, [
            'outlet_ids',
            'tags',
        ]);

        $baseData['has_variants'] = ! empty($data['variants'] ?? []);

        return DB::transaction(function () use ($baseData, $selectedOutletIds, $tagIds): Product {
            $product = Product::create($baseData);
            $product->outlets()->sync($selectedOutletIds);
            if (! empty($tagIds)) {
                $product->tags()->sync($tagIds);
            }

            return $product;
        });
    }
}

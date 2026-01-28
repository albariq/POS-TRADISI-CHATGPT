<?php

namespace App\Http\Controllers;

use App\Models\PricingSetting;
use App\Support\OutletContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PricingSettingController extends Controller
{
    private array $sizes = [100, 200, 500, 1000];

    public function index()
    {
        $outletId = OutletContext::id();

        $settings = PricingSetting::where('outlet_id', $outletId)
            ->orderBy('grams')
            ->get();

        return view('pricing-settings.index', [
            'settings' => $settings,
            'sizes' => $this->sizes,
        ]);
    }

    public function create()
    {
        return view('pricing-settings.create', [
            'sizes' => $this->sizes,
        ]);
    }

    public function store(Request $request)
    {
        $outletId = OutletContext::id();

        $data = $request->validate([
            'grams' => [
                'required',
                'integer',
                Rule::in($this->sizes),
                Rule::unique('pricing_settings')->where('outlet_id', $outletId),
            ],
            'packaging_cost' => ['required', 'integer', 'min:0'],
            'markup' => ['required', 'numeric', 'min:0', 'max:5'],
        ]);

        PricingSetting::create([
            'outlet_id' => $outletId,
            'grams' => $data['grams'],
            'packaging_cost' => $data['packaging_cost'],
            'markup' => $data['markup'],
        ]);

        return redirect()->route('pricing-settings.index');
    }

    public function edit(PricingSetting $pricingSetting)
    {
        $this->authorizeOutlet($pricingSetting, OutletContext::id());

        return view('pricing-settings.edit', [
            'pricingSetting' => $pricingSetting,
            'sizes' => $this->sizes,
        ]);
    }

    public function update(Request $request, PricingSetting $pricingSetting)
    {
        $this->authorizeOutlet($pricingSetting, OutletContext::id());

        $data = $request->validate([
            'grams' => [
                'required',
                'integer',
                Rule::in($this->sizes),
                Rule::unique('pricing_settings')->where('outlet_id', $pricingSetting->outlet_id)->ignore($pricingSetting->id),
            ],
            'packaging_cost' => ['required', 'integer', 'min:0'],
            'markup' => ['required', 'numeric', 'min:0', 'max:5'],
        ]);

        $pricingSetting->update($data);

        return redirect()->route('pricing-settings.index');
    }

    public function destroy(PricingSetting $pricingSetting)
    {
        $this->authorizeOutlet($pricingSetting, OutletContext::id());

        $pricingSetting->delete();

        return redirect()->route('pricing-settings.index');
    }

    protected function authorizeOutlet(PricingSetting $pricingSetting, ?int $outletId): void
    {
        if (! $outletId || $pricingSetting->outlet_id !== $outletId) {
            abort(403);
        }
    }
}

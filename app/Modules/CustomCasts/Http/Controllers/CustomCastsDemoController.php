<?php

namespace App\Modules\CustomCasts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CustomCasts\CustomCastsServiceProvider;
use App\Modules\CustomCasts\Models\Product;
use App\Modules\CustomCasts\ValueObjects\Money;
use Illuminate\Http\JsonResponse;
use Laravel\Pennant\Feature;

class CustomCastsDemoController extends Controller
{
    public function show(): JsonResponse
    {
        if (! Feature::active(CustomCastsServiceProvider::SLUG)) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $product = Product::create([
            'name' => 'Demo Widget',
            'price' => new Money(1999, 'USD'),
        ]);

        $roundTripped = Product::findOrFail($product->id);

        return response()->json([
            'name' => $roundTripped->name,
            'price' => [
                'formatted' => $roundTripped->price->format(),
                'amount_in_cents' => $roundTripped->price->amountInCents,
                'currency' => $roundTripped->price->currency,
            ],
        ]);
    }
}

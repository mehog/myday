<?php

namespace App\Http\Controllers;

use App\PlanTier;
use App\Services\Dodo\DodoCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class DodoCheckoutController extends Controller
{
    public function __invoke(Request $request, DodoCheckoutService $checkoutService): RedirectResponse
    {
        $validated = $request->validate([
            'tier' => ['required', 'string', Rule::enum(PlanTier::class)],
        ]);

        $tier = PlanTier::from($validated['tier']);

        try {
            $result = $checkoutService->createCheckout($request->user(), $tier);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->to('/app/pricing')
                ->with('error', __('pricing.error_checkout_failed'));
        }

        return redirect()->away($result['checkout_url']);
    }
}

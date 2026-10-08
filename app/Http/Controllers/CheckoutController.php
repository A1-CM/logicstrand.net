<?php

namespace App\Http\Controllers;

use App\Models\PlanAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request, string $plan): View|RedirectResponse
    {
        $selected = config("plans.$plan");
        abort_unless(is_array($selected), 404);

        if (! $request->user()) {
            $request->session()->put('checkout.plan', $plan);

            return redirect()->guest(route('register'));
        }

        if (! $request->user()->hasVerifiedEmail()) {
            $request->session()->put('checkout.plan', $plan);

            return redirect()->route('verification.notice');
        }

        $access = $request->user()->planAccess;

        if ($plan === 'sandbox' && $access) {
            return redirect()->route('pricing')->with('toast', [
                'type' => 'info',
                'message' => 'Sandbox is available once for a new account.',
            ]);
        }

        $request->session()->put('checkout.plan', $plan);

        return view('checkout', compact('plan', 'selected', 'access'));
    }

    public function complete(Request $request, string $plan): RedirectResponse
    {
        $selected = config("plans.$plan");
        abort_unless(is_array($selected), 404);

        $rawCardNumber = $request->input('card_number');
        $cardNumber = is_string($rawCardNumber)
            ? (preg_replace('/[ -]/', '', $rawCardNumber) ?? '')
            : '';
        $validator = Validator::make([
            'cardholder' => $request->input('cardholder'),
            'card_number' => $cardNumber,
            'expiry' => $request->input('expiry'),
            'cvc' => $request->input('cvc'),
        ], [
            'cardholder' => ['required', 'string', 'min:2', 'max:80'],
            'card_number' => ['required', 'in:4242424242424242'],
            'expiry' => ['required', 'regex:/^(0[1-9]|1[0-2])\/([0-9]{2})$/'],
            'cvc' => ['required', 'regex:/^[0-9]{3,4}$/'],
        ], [
            'card_number.in' => 'Please check the card number.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $expiry = $request->input('expiry');

            if (is_string($expiry) && preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry, $matches)) {
                $lastDay = now()->setDate(2000 + (int) $matches[2], (int) $matches[1], 1)->endOfMonth();

                if ($lastDay->isPast()) {
                    $validator->errors()->add('expiry', 'Enter a future expiry date.');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->only('cardholder', 'expiry'));
        }

        $activated = DB::transaction(function () use ($request, $plan): bool {
            $user = $request->user();
            $access = PlanAccess::where('user_id', $user->id)->lockForUpdate()->first();
            $now = now();

            if ($plan === 'sandbox') {
                if ($access) {
                    return false;
                }

                $trialEndsAt = $now->copy()->addDays(7);
                PlanAccess::updateOrCreate(['user_id' => $user->id], [
                    'plan' => 'sandbox',
                    'trial_started_at' => $now,
                    'period_started_at' => $now,
                    'trial_ends_at' => $trialEndsAt,
                    'access_ends_at' => $trialEndsAt,
                    'card_last_four' => '4242',
                ]);

                return true;
            }

            $periodStart = $access?->plan === $plan && $access->isActive()
                ? $access->access_ends_at->copy()
                : $now;

            PlanAccess::updateOrCreate(['user_id' => $user->id], [
                'plan' => $plan,
                'period_started_at' => $periodStart,
                'trial_started_at' => $access?->trial_started_at,
                'trial_ends_at' => $access?->trial_ends_at,
                'access_ends_at' => $periodStart->addMonth(),
                'card_last_four' => '4242',
            ]);

            return true;
        });

        if (! $activated) {
            return redirect()->route('pricing')->with('toast', [
                'type' => 'info',
                'message' => 'Sandbox is available once for a new account.',
            ]);
        }

        $request->user()->unsetRelation('planAccess');
        $request->session()->forget('checkout.plan');

        return redirect()->route($plan === 'sandbox' ? 'sandbox.index' : 'dashboard')->with('toast', [
            'type' => 'success',
            'message' => $plan === 'sandbox' ? 'Your seven day Sandbox trial is ready.' : $selected['name'].' access is ready.',
        ]);
    }
}

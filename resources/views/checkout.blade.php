<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => 'Checkout'])
    <meta name="description" content="Complete your LogicStrand plan selection.">
</head>
<body class="site-body checkout-body">
    <header class="checkout-header site-container">
        <a href="{{ route('home') }}" class="site-brand" aria-label="LogicStrand home"><img class="brand-lockup" src="{{ asset('images/logicstrand/logo.webp') }}" alt="strand" width="3168" height="899"></a>
        <a href="{{ route('pricing') }}" class="auth-back">← <span>Back to pricing</span></a>
    </header>
    <main class="checkout-layout site-container">
        <div class="checkout-main">
            <span class="section-kicker">YOUR NEXT THREAD STARTS HERE</span>
            <h1>Make room for<br><em>clearer answers.</em></h1>
            <p class="checkout-intro">You're choosing {{ $selected['name'] }} for {{ $plan === 'sandbox' ? 'seven days' : 'the next month' }}. Complete the details below to activate your workspace.</p>

            <form method="POST" action="{{ route('checkout.complete', $plan) }}" class="checkout-form" autocomplete="off">
                @csrf
                <div class="checkout-form-heading"><span>01</span><div><h2>Card details</h2><p>Review the card details below to continue.</p></div></div>
                <label for="cardholder">Name on card</label>
                <input id="cardholder" name="cardholder" type="text" value="{{ old('cardholder', auth()->user()->name) }}" maxlength="80" required>
                @error('cardholder') <p class="field-error">{{ $message }}</p> @enderror

                <label for="card_number">Card number</label>
                <div class="checkout-card-input"><input id="card_number" name="card_number" type="text" inputmode="numeric" value="4242 4242 4242 4242" maxlength="23" required aria-describedby="card-hint"><span>◈</span></div>
                <small id="card-hint" class="checkout-field-note">Card ending in 4242</small>
                @error('card_number') <p class="field-error">{{ $message }}</p> @enderror

                <div class="checkout-form-row">
                    <div><label for="expiry">Expiry</label><input id="expiry" name="expiry" type="text" inputmode="numeric" value="{{ old('expiry', now()->addYears(4)->format('m/y')) }}" placeholder="MM/YY" maxlength="5" required>@error('expiry') <p class="field-error">{{ $message }}</p> @enderror</div>
                    <div><label for="cvc">Security code</label><input id="cvc" name="cvc" type="password" inputmode="numeric" value="123" placeholder="•••" maxlength="4" required>@error('cvc') <p class="field-error">{{ $message }}</p> @enderror</div>
                </div>

                <div class="checkout-form-heading checkout-agreement"><span>02</span><div><h2>Confirm access</h2><p>Your {{ $selected['name'] }} access begins as soon as you continue.</p></div></div>
                <div class="checkout-total"><span>{{ $selected['name'] }} · {{ $plan === 'sandbox' ? '7 days' : 'one month' }}</span><strong>&#36;{{ $selected['price'] }}</strong></div>
                <button type="submit" class="button button-blue checkout-submit">Activate {{ $selected['name'] }} <span aria-hidden="true"><i class="fa-solid fa-arrow-right icon-arrow-up-right" aria-hidden="true"></i></span></button>
                <p class="checkout-footnote">No automatic renewal. Your access end date will appear in the dashboard.</p>
            </form>
        </div>
        <aside class="checkout-side">
            <img src="{{ asset('images/logicstrand/checkout-workspace.webp') }}" alt="A person using a laptop while reviewing documents" width="1774" height="1680">
            <div class="checkout-side-copy"><span>THE LOGICSTRAND WAY</span><h2>Every answer<br>has a thread.</h2><p>Bring your sources together. Explore what they say. Keep the evidence close enough to inspect.</p></div>
            <div class="checkout-side-bottom"><span>✳</span><p><strong>{{ $selected['name'] }} access</strong><br>{{ $selected['features'][1] }} · {{ $selected['features'][2] }}</p></div>
        </aside>
    </main>
    @include('partials.site-footer')
    <x-toast-stack />
    @include('partials.cookie-consent')
</body>
</html>

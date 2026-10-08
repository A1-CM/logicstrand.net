@if (! in_array(request()->cookie('logicstrand_cookie_consent'), ['optional', 'essential'], true))
    <aside class="cookie-consent" role="region" aria-labelledby="cookie-consent-title">
        <div class="cookie-consent-copy">
            <span class="cookie-consent-icon" aria-hidden="true"><i class="fa-solid fa-cookie-bite"></i></span>
            <div>
                <h2 id="cookie-consent-title">Your cookie choice</h2>
                <p>Essential cookies keep sign-in and security working. Optional analytics and marketing cookies are currently off.</p>
                <a href="{{ route('privacy') }}#cookies">Cookie details</a>
            </div>
        </div>
        <div class="cookie-consent-actions">
            <form method="POST" action="{{ route('cookie-consent.store') }}">@csrf<input type="hidden" name="choice" value="essential"><button class="cookie-button cookie-button-secondary" type="submit">Essential only</button></form>
            <form method="POST" action="{{ route('cookie-consent.store') }}">@csrf<input type="hidden" name="choice" value="optional"><button class="cookie-button cookie-button-primary" type="submit">Allow optional</button></form>
        </div>
    </aside>
@endif

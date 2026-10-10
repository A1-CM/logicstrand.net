<footer class="site-footer">
    <div class="site-container footer-inner">
        <a href="{{ route('home') }}" class="site-brand"><img class="brand-lockup" src="{{ asset('images/logicstrand/logo.webp') }}" alt="strand" width="3168" height="899"></a>
        <span>Knowledge, connected with clarity.</span>
        <span>© {{ date('Y') }} LogicStrand</span>
        <nav class="footer-legal" aria-label="Legal">
            <a href="{{ route('privacy') }}">Privacy Policy</a>
            <a href="{{ route('terms') }}">Terms &amp; Conditions</a>
        </nav>
    </div>
</footer>

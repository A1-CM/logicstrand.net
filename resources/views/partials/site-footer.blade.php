<footer class="site-footer">
    <div class="site-container footer-inner">
        <a href="{{ route('home') }}" class="site-brand"><x-app-logo-icon class="brand-mark" /><span>LogicStrand<span class="brand-period">.</span></span></a>
        <span>Knowledge, connected with clarity.</span>
        <span>© {{ date('Y') }} LogicStrand</span>
        <nav class="footer-legal" aria-label="Legal">
            <a href="{{ route('privacy') }}">Privacy Policy</a>
            <a href="{{ route('terms') }}">Terms &amp; Conditions</a>
        </nav>
    </div>
</footer>

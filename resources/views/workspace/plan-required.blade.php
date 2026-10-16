<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Explore plans · LogicStrand</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="workspace-gate-page">
    <main class="workspace-gate-card">
        <a class="workspace-gate-brand" href="{{ route('dashboard') }}" aria-label="LogicStrand home">
            <img src="{{ asset('images/logicstrand/logo.webp') }}" alt="LogicStrand" width="3168" height="899">
        </a>
        <span class="page-eyebrow">SANDBOX ACCESS</span>
        <h1>Your Sandbox is ready to explore.</h1>
        <p>This trial gives you a guided preview of LogicStrand. Choose a plan to unlock documents, questions, and your saved answer history.</p>
        <a class="button button-primary" href="{{ route('pricing') }}">Explore plans <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        <a class="workspace-gate-back" href="{{ route('dashboard') }}">Back to your Sandbox</a>
    </main>
</body>
</html>

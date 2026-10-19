<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agenda em tempo real</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-clinic-id="{{ $clinic->id }}" data-user-id="{{ auth()->id() }}">
    <main>
        <h1>{{ $clinic->name }}</h1>
        <p>Usuários conectados: <strong data-online-count>0</strong></p>
        <ul data-notifications></ul>
        <ul data-appointments>
            @foreach ($appointments as $appointment)
                <li data-appointment-id="{{ $appointment->id }}">
                    <strong>#{{ $appointment->id }}</strong>
                    <span data-status>{{ $appointment->status }}</span>
                </li>
            @endforeach
        </ul>
    </main>
</body>
</html>

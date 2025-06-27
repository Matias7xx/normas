<!DOCTYPE html>
<html lang="pt_BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Sistema Doc PCPB') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo-pc.png') }}">

    <link rel="stylesheet" href="{{asset('/plugins/fontawesome-free/css/all.min.css')}}">
    
    <!-- Meta tags -->
    <meta name="description" content="Sistema de Consulta de Normas - Polícia Civil da Paraíba">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- CSS com Laravel Mix -->
    <link href="{{ mix('css/public.css') }}" rel="stylesheet">
    
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
    
    <!-- JS com Laravel Mix -->
    <script src="{{ mix('js/public.js') }}" defer></script>
</body>
</html>
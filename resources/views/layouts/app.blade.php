<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload XML</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #toast-container > .toast {
            margin-top: 4rem !important;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container">
        <a class="navbar-brand" style="color: yellow; font-size: 1.6rem" href="#">
            WMS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('/b') ? 'active' : '' }}" href="{{ route('notaFiscal.index') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::currentRouteName() == 'notaFiscal.index' ? 'active' : '' }}"
                       href="{{ route('notaFiscal.index') }}">Importar XML</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::currentRouteName() == 'import.index' ? 'active' : '' }}"
                       href="{{ route('import.index') }}">Importar Produto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('/a') ? 'active' : '' }}" href="{{ route('notaFiscal.index') }}">Produto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Tributação</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-5">
    @yield('content')
    <script>
        // Personalizando a posição do Toastr
        toastr.options = {
            "positionClass": "toast-top-right",  // Muda a posição para o canto superior direito
            "closeButton": true,  // Ativa o botão de fechar
            "timeOut": "3000",    // Tempo de exibição da notificação
            "extendedTimeOut": "1000"
        };
        // Exibindo mensagens de sucesso ou erro
        @if (session('success'))
        toastr.success("{{ session('success') }}");
        @elseif (session('error'))
        toastr.error("{{ session('error') }}");
        @endif
    </script>
</div>
</body>
</html>


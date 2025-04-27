<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Produtos</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #444;
            padding: 4px;
            text-align: left;
        }
        th {
            background: #eee;
        }
        .text-end { text-align: right }
    </style>
</head>
<body>
<h2>Relatório de Produtos</h2>
@if($busca)
    <p>Filtro de busca: <strong>{{ $busca }}</strong></p>
@endif

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Descrição</th>
        <th>Fornecedor</th>
        <th>Cod.Barras</th>
        <th>ERP</th>
        <th>NCM</th>
        <th>CEST</th>
        <th>Estoque</th>
        <th>Preço</th>
    </tr>
    </thead>
    <tbody>
    @foreach($produtos as $p)
        <tr>
            <td>{{ $p->id }}</td>
            <td>{{ $p->descricao }}</td>
            <td>{{ $p->fornecedor }}</td>
            <td>{{ $p->codigo_barras }}</td>
            <td>{{ $p->codigo_erp }}</td>
            <td>{{ $p->ncm }}</td>
            <td>{{ $p->cest }}</td>
            <td>{{ $p->estoque }}</td>
            <td class="text-end">
                R$ {{ number_format($p->preco, 2, ',', '.') }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>

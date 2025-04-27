@extends('layouts.app')
@section('title', 'Listar produtos')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3 m-1">Buscar Produto</h5>
            <form id="FormBusca" action="{{ route('produto.listar') }} " method="GET">
                <div class="row">
                    <div class="col-8">
                        <div class="input-group  mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Informe Produto</span>
                            </div>
                            <input type="text" id="busca" name="busca" class="form-control"
                                   aria-label="Código de barras"
                                   aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary " id="buscar"> Buscar</button>
                        <a href="{{ route('produto.index') }}"
                           class="btn btn-success" id="buscar">Cadastrar</a>
                        <a href="{{ route('produto.gerarPdf', ['busca' => request('busca')]) }}"
                           class="btn btn-secondary" id="buscar"> Gerar relatório</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
    <div class="container mt-4">
        <h1>Lista de Produtos</h1>

        <table class="table table-striped table-hover">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Fornecedor</th>
                <th>Cod.Barras</th>
                <th>Cod. ERP</th>
                <th>NCM</th>
                <th>Cest</th>
                <th>Estoque</th>
                <th style="min-width: 120px;">Preço</th>

            </tr>
            </thead>
            <tbody>
            @foreach ($produtos as $produto)
                <tr>
                    <td>{{ $produto->id }}</td>
                    <td>{{ $produto->descricao }}</td>
                    <td>{{ $produto->fornecedor }}</td>
                    <td>{{ $produto->codigo_barras }}</td>
                    <td>{{ $produto->codigo_erp }}</td>
                    <td>{{ $produto->ncm }}</td>
                    <td>{{ $produto->cest }}</td>
                    <td>{{ $produto->estoque }}</td>
                    <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div>
                Mostrando {{ $produtos->firstItem() }} até {{ $produtos->lastItem() }} de {{ $produtos->total() }}
                resultados
            </div>
            {{ $produtos->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection

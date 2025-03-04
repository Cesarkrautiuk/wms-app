@extends('layouts.app')
@section('title', 'Cadastro de tributação')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3 m-1">Cadastro de produto</h5>
            <form id="FormProduto" action="{{ route('produto.salvar') }} " method="POST">
                @csrf
                <div class="row">
                    <div class="col-3">
                        <div class="input-group  mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Código ERP</span>
                            </div>
                            <input type="text" id="codigoERP" name="codigoERP" class="form-control"
                                   aria-label="Código ERP"
                                   aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group col-3 mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Descrição</span>
                            </div>
                            <input type="text" id="descricao" name="descricao" class="form-control"
                                   aria-label="Descrição"
                                   aria-describedby="basic-addon1">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="input-group col mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Código de barras</span>
                            </div>
                            <input type="text" id="codigoBarras" name="codigoBarra" class="form-control"
                                   aria-label="Código de barras"
                                   aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text" for="inputGroupSelectFornecedor">Fornecedor</label>
                            </div>
                            <select name="fornecedor" class="form-control" id="inputGroupSelectFornecedor">
                                <option selected>Koloss</option>
                                <option value="BELLIZ">BELLIZ</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="input-group col mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">NCM</span>
                            </div>
                            <input type="text" name="ncm" id="ncm" class="form-control"
                                   aria-describedby="basic-addon1">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3" id="clearButton">Salvar</button>
                <button type="button" class="btn btn-secondary mt-3" id="saveButton">Cancelar</button>
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#codigoERP').on('blur', function () {
                var codigoERP = $(this).val().trim();
                if (codigoERP !== "") {
                    $.ajax({
                        url: '/buscar-produto/' + codigoERP, // monta a URL com o parâmetro {id}
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            if (response) {
                                $('#codigo').val(response.codigo);
                                $('#descricao').val(response.descricao);
                                $('#codigoBarras').val(response.codigoBarras);
                                $('#situacao').val(response.situacao);
                                $('#inputGroupSelectFornecedor').val(response.fornecedor);
                                $('#inputGroupSelectTributacao').val(response.tributacao);
                                $('#ncm').val(response.ncm);
                                $('#cest').val(response.cest);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Erro ao buscar os dados do produto: ", error);
                        }
                    });
                }
            });
        });
    </script>
@endsection

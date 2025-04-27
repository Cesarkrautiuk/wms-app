@extends('layouts.app')
@section('title', 'Cadastro de tributação')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3 m-1">Cadastro Tributação</h5>
            <form id="FormProduto" action="{{ route('produto.salvar') }} " method="POST">
                @csrf
                <div class="row">
                    <div class="col-2">
                        <div class="input-group  mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Código</span>
                            </div>
                            <input type="text" id="codigo" name="codigo" class="form-control"
                                   aria-label="Código"
                                   aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Descrição</span>
                            </div>
                            <input type="text" id="descricao" name="descricao" class="form-control"
                                   aria-label="Descrição"
                                   aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group  mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">MVA</span>
                            </div>
                            <input type="text" name="ncm" id="mva" class="form-control"
                                   aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group col mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">ICMS</span>
                            </div>
                            <input type="text" name="ncm" id="icms" class="form-control"
                                   aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group col mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">ICMS ST</span>
                            </div>
                            <input type="text" name="ncm" id="icmsSt" class="form-control"
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
                        url: '/buscar-produto/' + codigoERP,
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

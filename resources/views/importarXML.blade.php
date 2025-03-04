@extends('layouts.app')
@section('title', 'Importar XML')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3 m-1">Importar XML</h5>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="row mt-3 ">
                    <div class="col">
                        <div class="input-group mb-3">
                            <div class="input-group mb-3">
                            <span class="input-group-text"
                                  id="inputGroup-sizing-default">Desconto</span>
                                <input type="text" name="desconto" class="form-control"
                                       aria-label="Sizing example input"
                                       aria-describedby="inputGroup-sizing-default">
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="input-group mb-3">
                        <span class="input-group-text"
                              id="inputGroup-sizing-default">Bonificação</span>
                            <input type="text" class="form-control" name="bonificação" aria-label="Sizing example input"
                                   aria-describedby="inputGroup-sizing-default">
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="file" class="form-control bg-dark" accept=".xml" required name="xml_file"
                           id="inputGroupFile02">
                    <button class="btn btn-outline-secondary" type="submit" id="inputGroupFileAddon04">Enviar</button>
                </div>
            </form>
            <div>
                <button type="button" class="btn btn-primary mt-3" id="clearButton">Limpar</button>
                <button type="button" class="btn btn-secondary mt-3" id="saveButton">Salvar</button>
            </div>
        </div>
    </div>
    <div class="card mt-3 p-2 d-none" id="dadosNotaCard">
        <div class="card-body">
            <h5 class="card-title">Dados da nota</h5>
            <div class="row">
                <div class="col-6">
                    <span id="emitente-info"></span>
                </div>
                <div class="col">
                    <span id="nf-info"></span>
                </div>
                <div class="col">
                    <span id="total-info"></span>
                </div>
                <div class="col">
                    <span id="total_geral_icms-info"></span>
                </div>
            </div>
            <div class="row mt-3 mb-2 ">
                <div class="col-6">
                    <span id="total_bonificação-info"></span>
                </div>
                <div class="col-2">
                    <span id="total_desconto-info"></span>
                </div>
                <div class="col-4">
                    <span id="duplicatas"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-2 p-2 d-none" id="dadosTabelaCard">
        <table class="table table-dark table-sm text-center mt-4" id="dataTable">
            <thead>
            <tr>
                <th scope="col">Codigo</th>
                <th scope="col">Descrição</th>
                <th scope="col">Codigo barras</th>
                <th scope="col">Ncm</th>
                <th scope="col">QTA</th>
                <th scope="col">Preço</th>
                <th scope="col">Total</th>
                <th scope="col">Total ICMS</th>
                <th scope="col">Preço final</th>
                <th scope="col">Preço c/ desc.</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            var responseData = null;
            $('#uploadForm').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: '{{ route('importarXML') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        responseData = response;
                        // Limpa os elementos para os novos dados
                        $('#emitente-info').html('Fornecedor: ' + response.emitente);
                        $('#nf-info').html('NF: ' + response.numero_nota);
                        $('#total-info').html('Total: ' + response.total_nf);
                        $('#total_geral_icms-info').html('Imposto: ' + response.total_geral_icms);
                        $('#total_bonificação-info').html('Bonificação: ' + response.total_bonificacao);
                        $('#total_desconto-info').html('Desconto: ' + response.total_desconto);

                        if (response.duplicatas && response.duplicatas.length > 0) {
                            var prazos = response.duplicatas.map(function (duplicata) {
                                return duplicata.dias_ate_vencimento;
                            });
                            var prazoFormatado = prazos.join('/');
                            $('#duplicatas').append('<span>Prazo: ' + prazoFormatado + '</span>');
                        }

                        // Mostra as seções apenas se houver dados
                        if (response.emitente && response.numero_nota) {
                            $('#dadosNotaCard').removeClass('d-none');
                        }

                        if (response.produtos && response.produtos.length > 0) {
                            $('#dadosTabelaCard').removeClass('d-none');
                            $('#dataTable tbody').empty();
                            response.produtos.forEach(function (item) {
                                var row = '<tr>' +
                                    '<td>' + item.codigo + '</td>' +
                                    '<td>' + item.descricao + '</td>' +
                                    '<td>' + item.codigo_barras + '</td>' +
                                    '<td>' + item.ncm + '</td>' +
                                    '<td>' + item.quantidade + '</td>' +
                                    '<td>' + item.valor_unitario + '</td>' +
                                    '<td>' + item.valor_total + '</td>' +
                                    '<td>' + item.total_ICMS + '</td>' +
                                    '<td>' + item.preco_final + '</td>' +
                                    '<td>' + item.preco_finalDesconto + '</td>' +
                                    '</tr>';
                                $('#dataTable tbody').append(row);
                            });
                        } else {
                            alert('Nenhum dado de produto retornado.');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro na requisição AJAX:", xhr);
                        let errorMessage = "Erro ao enviar o arquivo: " + error;

                        if (xhr.responseJSON) {
                            errorMessage += "\nMensagem do servidor: " + (xhr.responseJSON.message || "Erro desconhecido");
                            if (xhr.responseJSON.errors) {
                                errorMessage += "\nDetalhes: " + JSON.stringify(xhr.responseJSON.errors, null, 2);
                            }
                        } else {
                            errorMessage += "\nResposta do servidor: " + xhr.responseText;
                        }

                        alert(errorMessage);
                    }
                });
            });

            $('#clearButton').on('click', function () {
                $('#uploadForm')[0].reset();
                $('#emitente-info').html('');
                $('#nf-info').html('');
                $('#total-info').html('');
                $('#total_geral_icms-info').html('');
                $('#total_bonificação-info').html('');
                $('#total_desconto-info').html('');
                $('#duplicatas').html('');
                $('#dataTable tbody').empty();
                $('#dadosNotaCard').addClass('d-none');
                $('#dadosTabelaCard').addClass('d-none');
            });

            $('#saveButton').on('click', function () {
                if (!responseData) {
                    alert("Nenhum dado para salvar!");
                    return;
                }

                $.ajax({
                    url: '{{ route("salvarXML") }}', // Defina a rota no backend
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(responseData), // Enviar os dados em JSON
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Verifica o CSRF token
                    },
                    success: function (res) {
                        alert("Dados salvos com sucesso!");
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro ao salvar:", xhr);
                        alert("Erro ao salvar os dados: " + error);
                    }
                });
            });
        });
    </script>
@endsection

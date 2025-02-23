<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload XML</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <div class="card p-4">
        <h3 class="text-center">Enviar Arquivo XML</h3>
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <div class="row ">
                <div class="col">
                    <div class="input-group mb-3">
                        <div class="input-group mb-3">
                            <span class="input-group-text"
                                  id="inputGroup-sizing-default">Desconto</span>
                            <input type="text" name="desconto" class="form-control" aria-label="Sizing example input"
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
    </div>
    <div class="card mt-4 p-2">
        <div class="row">
            <div class="col">
                <span id="emitente-info"></span>
            </div>
            <div class="col">
                <span id="nf-info"></span>
            </div>
            <div class="col">
                <span id="total-info"></span>
            </div>
        </div>
        <div class="row mt-2 mb-2 ">
            <div class="col">
                <span id="total_geral_icms-info"></span>
            </div>
            <div class="col">
                <span id="total_bonificação-info"></span>
            </div>
            <div class="col">
                <span id="total_desconto-info"></span>
            </div>
        </div>
        <table class="table table-dark table-sm text-center" id="dataTable">
            <thead>
            <tr>
                <th scope="col">Codigo</th>
                <th scope="col">Descrição</th>
                <th scope="col">Codigo barras</th>
                <th scope="col">Ncm</th>
                <th scope="col">Quantidade</th>
                <th scope="col">Preço</th>
                <th scope="col">Total</th>
                <th scope="col">Total ICMS</th>
                <th scope="col">Preço final</th>
                <th scope="col">Preço com desconto </th>

            </tr>
            </thead>
            <tbody>
            <!-- Dados preenchidos por AJAX -->
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {
        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();  // Impede o envio tradicional do formulário

            var formData = new FormData(this);  // Cria o objeto FormData

            $.ajax({
                url: '{{ route('importarXML') }}',  // Rota para onde o arquivo será enviado
                type: 'POST',
                data: formData,
                processData: false,  // Evita que o jQuery processe os dados
                contentType: false,  // Impede que o jQuery defina o tipo de conteúdo
                success: function (response) {
                    // Verifique e exiba os dados do emitente (se necessário)
                    if (response.emitente) {
                        // Exemplo de como preencher um campo com os dados do emitente
                        $('#emitente-info').html('Emitente: ' + response.emitente);  // Exemplo de exibição do nome do emitente
                    } else {
                        alert('Dados do emitente não encontrados.');
                    }
                    if (response.numero_nota) {
                        // Exemplo de como preencher um campo com os dados do emitente
                        $('#nf-info').html('NF:: ' + response.numero_nota);  // Exemplo de exibição do nome do emitente
                    } else {
                        alert('Dados do emitente não encontrados.');
                    }
                    if (response.total_nf) {
                        // Exemplo de como preencher um campo com os dados do emitente
                        $('#total-info').html('Total NF: ' + response.total_nf);  // Exemplo de exibição do nome do emitente
                    } else {
                        alert('Dados do emitente não encontrados.');
                    }
                    if (response.total_geral_icms) {
                        // Exemplo de como preencher um campo com os dados do emitente
                        $('#total_geral_icms-info').html('Total ICMS: ' + response.total_geral_icms);  // Exemplo de exibição do nome do emitente
                    } else {
                        alert('Dados do emitente não encontrados.');
                    }
                    if (response.total_bonificacao) {
                        // Exemplo de como preencher um campo com os dados do emitente
                        $('#total_bonificação-info').html('Total Bonificação: ' + response.total_bonificacao);  // Exemplo de exibição do nome do emitente
                    } else {
                        alert('Dados do emitente não encontrados.');
                    }
                    if (response.total_desconto) {
                        // Exemplo de como preencher um campo com os dados do emitente
                        $('#total_desconto-info').html('Total Desconto: ' + response.total_desconto);  // Exemplo de exibição do nome do emitente
                    } else {
                        alert('Dados do emitente não encontrados.');
                    }

                    // Verifica se os dados de produtos existem
                    if (response.produtos && response.produtos.length > 0) {
                        $('#dataTable tbody').empty();  // Limpa a tabela antes de preencher

                        // Preenche a tabela com os dados dos produtos
                        response.produtos.forEach(function (item) {

                            var valorUnitarioFormatado = new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL'
                            }).format(item.valor_unitario);
                            var quantidadeFormatada = new Intl.NumberFormat('pt-BR').format(item.quantidade);

                            var row = '<tr>' +
                                '<td>' + item.codigo + '</td>' +
                                '<td>' + item.descricao + '</td>' +
                                '<td>' + item.codigo_barras + '</td>' +
                                '<td>' + item.ncm + '</td>' +
                                '<td>' + quantidadeFormatada + '</td>' +
                                '<td>' + valorUnitarioFormatado + '</td>' +
                                '<td>' + item.valor_total + '</td>' +
                                '<td>' + item.total_ICMS + '</td>' +
                                '<td>' + item.preco_final + '</td>' +
                                '<td>' + item.preco_finalDesconto + '</td>' +

                                '</tr>';
                            $('#dataTable tbody').append(row);  // Adiciona apenas uma vez
                        });
                    } else {
                        alert('Nenhum dado de produto retornado.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Erro na requisição AJAX:", xhr); // Mostra o erro completo no console
                    let errorMessage = "Erro ao enviar o arquivo: " + error;

                    if (xhr.responseJSON) {
                        errorMessage += "\nMensagem do servidor: " + (xhr.responseJSON.message || "Erro desconhecido");
                        if (xhr.responseJSON.errors) {
                            errorMessage += "\nDetalhes: " + JSON.stringify(xhr.responseJSON.errors, null, 2);
                        }
                    } else {
                        errorMessage += "\nResposta do servidor: " + xhr.responseText;
                    }

                    alert(errorMessage);  // Exibe um alerta com os detalhes do erro
                }
            });
        });
    });
</script>

</body>
</html>

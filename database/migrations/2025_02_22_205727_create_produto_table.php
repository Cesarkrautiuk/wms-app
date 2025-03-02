<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id(); // Cria a coluna de ID
            $table->string('descricao'); // Descrição do produto
            $table->string('fornecedor'); // Fornecedor do produto
            $table->enum('situacao', ['ativo', 'inativo']); // Situação do produto (ativo ou inativo)
            $table->string('ncm'); // NCM do produto
            $table->string('cest'); // CEST do produto
            $table->string('codigo_barras')->nullable(); // Código de barras (pode ser nulo)
            $table->string('codigo_erp')->nullable(); // Código ERP (pode ser nulo)
            $table->string('codigo_fornecedor')->nullable(); // Código do fornecedor (pode ser nulo)
            $table->decimal('preco', 10, 2); // Preço do produto com 2 casas decimais
            $table->foreignId('tributacao_id')->constrained('tributacoes')->onDelete('cascade');
            $table->timestamps(); // Timestamps para criação e atualização

            // Adiciona um índice único para o código ERP, caso necessário
            $table->unique('codigo_erp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};

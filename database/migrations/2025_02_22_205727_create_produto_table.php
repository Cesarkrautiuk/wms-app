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
            $table->string('descricao');
            $table->string('fornecedor');
            $table->enum('situacao', ['ativo', 'inativo']);
            $table->string('ncm');
            $table->string('cest');
            $table->string('codigo_barras')->nullable();
            $table->string('codigo_erp')->nullable();
            $table->string('codigo_fornecedor')->nullable();
            $table->decimal('preco', 10, 2);
            $table->foreignId('tributacao_id')->constrained('tributacoes')->onDelete('cascade');
            $table->timestamps();
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

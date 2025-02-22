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
        Schema::create('tributacoes', function (Blueprint $table) {
            $table->id(); // Coluna id (auto incremento)
            $table->decimal('MVA', 5, 2); // MVA (ex: 61.67%)
            $table->decimal('ICMS', 5, 2); // ICMS (ex: 12%)
            $table->decimal('ICMS_ST', 5, 2); // ICMS-ST (ex: 25%)
            $table->string('descricao'); // Descrição
            $table->timestamps(); // Colunas created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tributacoes');
    }
};

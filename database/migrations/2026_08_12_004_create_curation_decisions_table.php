<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Criar tabela curation_decisions.
     * Registra histórico (não UNIQUE) de decisões humanas sobre oportunidades.
     * Permite auditoria, revisão futura e aprendizado.
     */
    public function up(): void
    {
        Schema::create('curation_decisions', function (Blueprint $table) {
            $table->id()->primary();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('product_opportunity_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('decision', ['APPROVED', 'REJECTED']);
            $table->text('reason')->nullable()->comment('Motivo da decisão (para rejeições ou contexto)');
            $table->timestamp('decided_at');
            $table->timestamps();

            // Índices (sem UNIQUE — permite histórico)
            $table->index('application_id');
            $table->index('product_opportunity_id');
            $table->index('user_id');
            $table->index('decided_at');
            $table->index(['application_id', 'decided_at']);

            // Foreign keys
            $table->foreign('application_id')
                ->references('id')
                ->on('applications')
                ->onDelete('cascade');

            $table->foreign('product_opportunity_id')
                ->references('id')
                ->on('product_opportunities')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curation_decisions');
    }
};

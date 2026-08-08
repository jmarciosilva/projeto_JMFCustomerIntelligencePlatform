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
        Schema::table('contacts', function (Blueprint $table) {
            $table->integer('customer_score')->default(0)->after('lead_score');
            $table->string('segment')->nullable()->after('customer_score'); // vip, engaged, converted, inactive, new
            $table->timestamp('customer_score_computed_at')->nullable()->after('lead_score_computed_at');
            $table->index('customer_score');
            $table->index('segment');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['customer_score']);
            $table->dropIndex(['segment']);
            $table->dropColumn(['customer_score', 'segment', 'customer_score_computed_at']);
        });
    }
};

<?php

use App\Enums\SecretScopes;
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
        Schema::create('service_secrets', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->string('tenant');
            $table->integer('env');
            $table->integer('scope')->default(SecretScopes::DEFAULT);
            $table->string('secrets', 1000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_secrets');
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('model')->nullable();
            $table->longText('prompt')->nullable();
            $table->longText('response')->nullable();
            $table->string('status')->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
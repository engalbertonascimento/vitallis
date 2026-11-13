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
        Schema::create('networks', function (Blueprint $table) {
            $table->id();
    
            $table->string('name');
            $table->string('legal_name')->nullable();
    
            $table->string('document', 20)->nullable()->unique();
    
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
    
            $table->boolean('active')->default(true);
    
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('networks');
    }
};

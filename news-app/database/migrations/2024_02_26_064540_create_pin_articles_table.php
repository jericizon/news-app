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
        Schema::create('pin_articles', function (Blueprint $table) {
            $table->id();            
            $table->string('user_session');
            $table->mediumText('article_id');
            $table->string('title')->nullable();
            $table->string('url')->nullable();            
            $table->string('date')->nullable();
            $table->string('section_id')->nullable();
            $table->string('section_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pin_articles');
    }
};

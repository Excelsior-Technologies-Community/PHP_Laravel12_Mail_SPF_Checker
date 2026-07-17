<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_checks', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('domain');
            $table->string('mailserver')->nullable();

            $table->boolean('spf')->default(false);
            $table->boolean('dkim')->default(false);
            $table->boolean('dmarc')->default(false);
            $table->string('spf_grade')->default('None');
            $table->string('grade_label')->default('Poor');
            $table->integer('score')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_checks');
    }
};
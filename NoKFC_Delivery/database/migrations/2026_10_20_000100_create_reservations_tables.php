<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Рестораны сети (адреса заведений)
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->time('opening_time')->default('09:00:00');
            $table->time('closing_time')->default('23:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Столики в заведениях (вместимость от 2 до 6 мест)
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('table_number');
            $table->unsignedTinyInteger('seats'); // 2 to 6 seats
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['restaurant_id', 'table_number']);
        });

        // Бронирования столиков
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_table_id')->constrained()->cascadeOnDelete();
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('guest_count');
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('note')->nullable();
            $table->string('status')->default('confirmed'); // confirmed, completed, cancelled
            $table->timestamps();

            $table->index(['restaurant_table_id', 'reservation_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('restaurant_tables');
        Schema::dropIfExists('restaurants');
    }
};

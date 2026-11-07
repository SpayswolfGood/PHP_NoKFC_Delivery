<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        Schema::table('dishes', function (Blueprint $table) {
            $table->integer('preparation_time')->default(15);
        });

        Schema::create('dish_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->integer('amount')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_ingredient');
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('preparation_time');
        });
        Schema::dropIfExists('ingredients');
    }
};

<?php

use App\Enums\TravelOrderStatusEnum;
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
        Schema::create('travel_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('status')->default(TravelOrderStatusEnum::REQUESTED->value);
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('destination');
            $table->date('departure_date');
            $table->date('return_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_orders');
    }
};

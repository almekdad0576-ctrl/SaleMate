<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\OrderType;
use App\Enums\OrderStatus;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('no action');
            $table->date('order_date');
            $table->enum('order_type', OrderType::values());
            $table->integer('total_amount')->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('status', OrderStatus::values())->default(OrderStatus::PENDING->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

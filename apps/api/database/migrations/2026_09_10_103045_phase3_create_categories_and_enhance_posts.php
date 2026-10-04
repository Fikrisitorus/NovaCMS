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
        // Tabel kategori untuk blog
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Tabel pivot many-to-many antara post dan category
        Schema::create('category_post', function (Blueprint $table) {
            $table->foreignUuid('category_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('post_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'post_id']);
        });

        // Tambahkan kolom baru ke tabel posts
        Schema::table('posts', function (Blueprint $table) {
            $table->string('featured_image')->nullable()->after('slug');
            $table->text('excerpt')->nullable()->after('featured_image');
            $table->boolean('is_published')->default(false)->after('excerpt');
            $table->foreignUuid('website_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('author_id')->nullable()->after('website_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['website_id']);
            $table->dropForeign(['author_id']);
            $table->dropColumn(['featured_image', 'excerpt', 'is_published', 'website_id', 'author_id']);
        });

        Schema::dropIfExists('category_post');
        Schema::dropIfExists('categories');
    }
};

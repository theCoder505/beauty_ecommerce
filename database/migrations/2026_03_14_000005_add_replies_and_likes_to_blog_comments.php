<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_comments', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('blog_post_id')->constrained('blog_comments')->cascadeOnDelete();
            }
        });

        if (!Schema::hasTable('blog_comment_likes')) {
            Schema::create('blog_comment_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('blog_comment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['blog_comment_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comment_likes');
        Schema::table('blog_comments', function (Blueprint $table) {
            if (Schema::hasColumn('blog_comments', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};

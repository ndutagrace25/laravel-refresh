<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add Soft delete to all schemas
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('channels', function (Blueprint $table) {
            if (! Schema::hasColumn('channels', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('admins', function (Blueprint $table) {
            if (! Schema::hasColumn('admins', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('creator_libraries', function (Blueprint $table) {
            if (! Schema::hasColumn('creator_libraries', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('chat_galleries', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_galleries', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('chat_gallery_files', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_gallery_files', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('chat_rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_rooms', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('chat_room_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_room_entries', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('chat_room_reactions', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_room_reactions', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('chat_room_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_room_comments', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('creator_verifications', function (Blueprint $table) {
            if (! Schema::hasColumn('creator_verifications', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('forgot_passwords', function (Blueprint $table) {
            if (! Schema::hasColumn('forgot_passwords', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('live_gallery_tags', function (Blueprint $table) {
            if (! Schema::hasColumn('live_gallery_tags', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('live_schedule_galleries', function (Blueprint $table) {
            if (! Schema::hasColumn('live_schedule_galleries', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'deleted_at')) {
                $table->softDeletes();
            } 
        });
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            if (! Schema::hasColumn('personal_access_tokens', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        Schema::table('user_avatars', function (Blueprint $table) {
            if (! Schema::hasColumn('user_avatars', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        Schema::table('user_channels', function (Blueprint $table) {
            if (! Schema::hasColumn('user_channels', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        Schema::table('user_chat_room_follows', function (Blueprint $table) {
            if (! Schema::hasColumn('user_chat_room_follows', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        Schema::table('user_followers', function (Blueprint $table) {
            if (! Schema::hasColumn('user_followers', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        Schema::table('user_gallery_follows', function (Blueprint $table) {
            if (! Schema::hasColumn('user_gallery_follows', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        Schema::table('user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_profiles', 'deleted_at')) {
                $table->softDeletes();
            }
        });
      
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schemas', function (Blueprint $table) {
            //
        });
    }
};

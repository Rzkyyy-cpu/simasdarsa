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
        // Roles column is now included in the users table creation migration
        // This migration is kept for backwards compatibility but does nothing
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Since roles column is created in users table migration,
        // we don't drop it here to avoid breaking the schema
    }
};
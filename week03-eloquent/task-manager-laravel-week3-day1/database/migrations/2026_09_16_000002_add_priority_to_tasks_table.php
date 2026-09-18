<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ── Modifying an EXISTING table — a genuinely separate migration ────────
// This is deliberately its own file, not folded into the create_tasks
// migration above, specifically to demonstrate what "modifying an
// existing table" looks like as a real, ordered, reversible schema
// change — exactly the kind of migration you'd write once a table
// already has real data in it and starting over isn't an option.
// Schema::table() (not Schema::create()) is the signal: this migration
// ALTERS something that already exists, rather than building it from
// nothing.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH'])
                ->default('MEDIUM')
                ->after('status');
            // ->after('status') controls column ORDER in the table
            // definition — purely cosmetic for querying (column order
            // never affects correctness), but genuinely useful for
            // anyone reading the table's raw structure later.

            // ── A composite index ─────────────────────────────────────────
            // A single-column index on 'priority' alone would help a
            // query filtering ONLY by priority. This composite index is
            // for a more specific, realistic query: "give me every
            // IN_PROGRESS task at HIGH priority" — filtering on BOTH
            // columns together. A composite index on (status, priority)
            // serves that exact combined lookup far better than two
            // separate single-column indexes would.
            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        // Reverses ONLY this migration's specific change — not the whole
        // table. Run `php artisan migrate:rollback` and the 'priority'
        // column and its composite index disappear; 'tasks' itself,
        // created by the PREVIOUS migration, is untouched.
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status', 'priority']);
            $table->dropColumn('priority');
        });
    }
};

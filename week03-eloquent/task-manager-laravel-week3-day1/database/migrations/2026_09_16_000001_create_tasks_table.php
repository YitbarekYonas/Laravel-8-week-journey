<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ── Laravel 11's modern migration style — an anonymous class ────────────
// `return new class extends Migration { ... };` instead of a named class
// declaration. The migration's identity comes entirely from its FILENAME
// (the timestamp prefix, used to order migrations, plus the description),
// not from a class name — so there's genuinely nothing lost by not naming
// this class, and one less thing to keep in sync with the filename.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            // ── Column types, deliberately varied ────────────────────────
            $table->id();
            // id() is shorthand for an auto-incrementing BIGINT primary
            // key named 'id' — matches Task's existing property exactly.

            $table->string('title', 100);
            // A bounded VARCHAR — matches StoreTaskRequest's existing
            // 'max:100' validation rule (Week 2 Day 2). Worth keeping
            // these two limits in sync by hand for now; a real project
            // at scale might generate one from the other.

            $table->text('description')->nullable();
            // TEXT, not VARCHAR — unbounded length, for a field that
            // could reasonably be a full paragraph. New today; Task.php
            // doesn't have this property yet (see the README's scope
            // note on why that's fine for a migrations-only day).

            $table->enum('status', ['TODO', 'IN_PROGRESS', 'DONE'])
                ->default('TODO');
            // A genuine ENUM column — the database itself enforces which
            // values are valid, not just application code. Worth a
            // portability note: MySQL has native ENUM support; Postgres
            // doesn't, and Laravel emulates the same constraint there via
            // a CHECK constraint instead. Functionally equivalent, but
            // worth knowing if you ever inspect the raw generated SQL and
            // it looks different between database engines.

            $table->string('attachment_path')->nullable();
            // Unbounded default VARCHAR length, nullable — a task without
            // an attachment simply has no value here at all.

            $table->timestamps();
            // Laravel's shorthand for two columns at once: a nullable
            // created_at and a nullable updated_at, both TIMESTAMP.
            // Eloquent (Day 2) manages both automatically once Task
            // extends Model — nothing to do here today beyond having the
            // columns exist.

            // ── An index ──────────────────────────────────────────────────
            // Filtering tasks by status (exactly like this whole project's
            // existing GET /api/tasks?status= intent, and the Spring Boot
            // journey's equivalent findByStatus() queries) is a genuinely
            // common query pattern — this index is what keeps that lookup
            // fast as the table grows, instead of scanning every row.
            $table->index('status');
        });
    }

    public function down(): void
    {
        // Reversibility — `php artisan migrate:rollback` runs this.
        // Dropping the whole table is correct here since up() created it
        // from nothing; a modifying migration (see the next file) instead
        // reverses only the SPECIFIC change it made.
        Schema::dropIfExists('tasks');
    }
};

# Laravel Roadmap — Week 3, Day 1

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-1-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Migrations-orange.svg)]()

> **"A migration file is a promise about the schema, written in a language both a database and a version-control diff can understand."**

---

## ⚠️ A Different Kind of Scope Note

**The running application's behavior is completely unchanged today.** `Task.php` is untouched — still the same plain PHP class implementing `UrlRoutable`, still backed by an in-memory array that resets every request, exactly as Week 2 Day 2 described. Nothing queries the database this migration creates. That's deliberate, not an oversight: today's roadmap topic is specifically **migrations** — creating tables, column types, indexes, modifying existing tables — and Eloquent models (the thing that would actually *connect* `Task` to this new schema) is explicitly tomorrow's topic, Week 3 Day 2. Today builds the schema Day 2's Eloquent model will attach to; it doesn't attach anything yet.

---

## ⚠️ Same Setup Note as Weeks 1–2

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

---

## 🎯 Learning Objectives

- ✅ Create a real table via a migration, with a deliberately varied set of column types
- ✅ Add a genuine index, and understand what it's actually for
- ✅ Modify an *existing* table in a separate, later migration — not fold every change into one file
- ✅ Understand migration reversibility (`up()`/`down()`) as a real, working contract, not just convention

---

## 💡 What I Learned Today

### 1. Column Types, Deliberately Varied

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();                                              // auto-incrementing BIGINT PK
    $table->string('title', 100);                              // bounded VARCHAR
    $table->text('description')->nullable();                   // unbounded TEXT
    $table->enum('status', ['TODO', 'IN_PROGRESS', 'DONE'])
        ->default('TODO');                                     // a real ENUM column
    $table->string('attachment_path')->nullable();
    $table->timestamps();                                      // created_at + updated_at, one call

    $table->index('status');
});
```

`string('title', 100)` deliberately matches `StoreTaskRequest`'s existing `max:100` validation rule (Week 2 Day 2) — worth keeping the two in sync by hand for now.

**A real portability note on `enum()`:** MySQL has native `ENUM` column support; PostgreSQL doesn't, and Laravel emulates the same constraint there via a `CHECK` constraint instead. Functionally equivalent — the database still rejects an invalid value either way — but worth knowing if you ever inspect the raw generated SQL and it looks structurally different between engines.

### 2. An Index, With a Genuine Reason

```php
$table->index('status');
```

Filtering tasks by status — exactly this project's existing `GET /api/tasks?status=` intent, and the same query pattern the parallel Spring Boot journey's `findByStatus()` derived query methods serve — is a realistic, common lookup. An index on `status` is what keeps that filter fast as the table grows, instead of the database scanning every row to check each one's status.

### 3. Modifying an Existing Table — A Genuinely Separate Migration

```php
// A SECOND migration file, not folded into the first
Schema::table('tasks', function (Blueprint $table) {
    $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH'])
        ->default('MEDIUM')
        ->after('status');

    $table->index(['status', 'priority']);
});
```

`Schema::table()` (not `Schema::create()`) is the actual signal that this migration *alters* something that already exists, rather than building it from nothing — exactly the situation you'd be in adding a column to a table that already has real production data, where "just start over" isn't an option. This is also where the **composite index** on `(status, priority)` together earns its place: a single-column index on `priority` alone would help a query filtering only by priority, but this index specifically serves a more realistic combined lookup — "every `IN_PROGRESS` task at `HIGH` priority" — far better than two separate single-column indexes would.

### 4. Reversibility Is a Real Contract, Not Just a Convention

```php
public function down(): void
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropIndex(['status', 'priority']);
        $table->dropColumn('priority');
    });
}
```

The second migration's `down()` reverses **only its own specific change** — dropping the `priority` column and its composite index — leaving the `tasks` table itself (created by the *first* migration) completely untouched. `php artisan migrate:rollback` runs `down()` methods in reverse order, one migration at a time; each migration is responsible for undoing exactly what its own `up()` did, nothing more.

---

## 🖥️ Trying This Yourself

**Easiest path — SQLite, zero external setup:**

```bash
composer install
cp .env.example .env
php artisan key:generate

# .env already defaults to DB_CONNECTION=sqlite, pointing at the empty
# database/database.sqlite file this project ships

php artisan migrate
```

You should see both migrations run, in order, creating `tasks` and then adding `priority` to it. Inspect the result directly:

```bash
php artisan tinker
>>> \Illuminate\Support\Facades\Schema::getColumnListing('tasks');
# ["id", "title", "description", "status", "priority", "attachment_path", "created_at", "updated_at"]
```

**The actual Day 1 exercise:**
```bash
php artisan migrate:rollback --step=1
```
Then re-check the column listing — `priority` should be gone, `tasks` itself should still exist with everything else intact. Run `php artisan migrate` again to restore it.

**Alternative — real Postgres:** uncomment the `pgsql` block in `.env` (and comment out the `sqlite` line), point it at a real running Postgres instance, then run the same `php artisan migrate` command.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Folding a later schema change into the original `create` migration and editing history | Once a migration has run anywhere real, add a NEW migration for further changes — never edit an already-run one |
| Writing `down()` as an afterthought, or leaving it empty | A migration that can't be reversed isn't really safe to run against real data — write `down()` with the same care as `up()` |
| Using `Schema::create()` for a change to an existing table | `Schema::create()` builds from nothing; `Schema::table()` is what signals "this table already exists, I'm changing it" |
| Assuming `enum()` behaves byte-for-byte identically across every database engine | It doesn't — same enforced constraint, different underlying SQL depending on the driver |
| Adding indexes speculatively for every column "just in case" | Index the columns real queries actually filter/sort on — an unused index still costs write performance for no benefit |

---

## ✅ Day 1 Checklist

- [x] `config/database.php` — this project's first real database configuration, sqlite by default for zero-setup local testing
- [x] `create_tasks_table` migration — `id`, bounded `string`, `text`, `enum`, nullable `string`, `timestamps`, one index
- [x] `add_priority_to_tasks_table` migration — a genuinely separate file, `Schema::table()`, `->after()` column positioning, a composite index
- [x] Both migrations' `down()` methods verified to reverse exactly what their `up()` did, nothing more
- [x] Explicit scope statement: `Task.php` and the running app are completely unaffected today — that's tomorrow's job

---

**Date**: September 16, 2026
**Status**: ✅ Week 3, Day 1 Complete!
**Next**: Day 2 — Eloquent Basics: models, mass assignment, accessors, mutators, and casts — where `Task` finally becomes a real Eloquent model, connected to the schema built today, and its in-memory persistence limitation goes away for good.

> *"Today's migrations don't make the app work differently. They make tomorrow's Eloquent model possible."*

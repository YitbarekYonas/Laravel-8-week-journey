<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// ── A Form Request — validation lives HERE, not in the controller ───────
// `php artisan make:request StoreTaskRequest` generates a class shaped
// like this. Type-hinting it in a controller method (instead of the base
// Request) means Laravel validates the incoming request BEFORE the
// controller method body runs at all — if validation fails, Laravel
// automatically returns a 422 with a structured error body, and the
// controller method never executes. TaskController::store() below never
// has to write a single "if invalid, return error" branch itself.
class StoreTaskRequest extends FormRequest
{
    // authorize() decides WHETHER this request is allowed to proceed at
    // all — separate from whether its DATA is valid. Returning true here
    // means "anyone can attempt this." Real authorization (only the
    // task's owner, only an admin, etc.) is Week 5–6 territory, not built
    // yet. Returning false here would make EVERY request to this action
    // fail with 403, regardless of how well-formed the submitted data is.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:TODO,IN_PROGRESS,DONE'],
            // 'nullable' — this field is entirely optional; if present,
            // the remaining rules still apply. mimes/max validate the
            // uploaded FILE itself (type and size in kilobytes), not just
            // that a file was attached.
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    // Custom, human-readable messages — optional. Overrides Laravel's
    // generic default wording for specific rule failures only; every
    // other rule still falls back to Laravel's default message text.
    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: TODO, IN_PROGRESS, DONE.',
            'attachment.mimes' => 'Attachment must be a JPG, PNG, or PDF file.',
        ];
    }
}

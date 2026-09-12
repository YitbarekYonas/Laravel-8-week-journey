<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Deliberately near-identical rules to StoreTaskRequest, on purpose: this
// route is wired to PUT (a full replacement, per REST semantics — see
// Route::apiResource() in routes/api.php), so it requires the same
// complete, valid representation of a task that creating one does. A
// PATCH-style partial-update request (where every field is optional via
// 'sometimes') would be a genuinely different contract, not just a
// smaller version of this one — worth knowing as a distinction, not
// built here since this project's update route is PUT, not PATCH.
class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:TODO,IN_PROGRESS,DONE'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: TODO, IN_PROGRESS, DONE.',
            'attachment.mimes' => 'Attachment must be a JPG, PNG, or PDF file.',
        ];
    }
}

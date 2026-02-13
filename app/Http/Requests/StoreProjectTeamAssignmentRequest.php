<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectTeamAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && ($this->user()?->can('update', $project) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var array<int, string> $roles */
        $roles = config('rbac.abilities.project.create', []);

        return [
            'team_id' => [
                'required',
                'string',
                Rule::exists('team_user', 'team_id')->where(function (Builder $query) use ($roles): void {
                    $query->where('user_id', $this->user()?->getKey())
                        ->whereIn('role', $roles);
                }),
            ],
        ];
    }
}

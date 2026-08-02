<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TeamMemberRequest;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;

class TeamMemberController extends SimpleContentController
{
    protected string $model = TeamMember::class;

    protected string $route = 'admin.team-members';

    protected string $title = 'Team Member';

    protected array $fields = ['name', 'designation', 'biography', 'email', 'phone', 'linkedin_url', 'instagram_url', 'display_order', 'is_active'];

    protected ?string $imageField = 'profile_image';

    public function store(TeamMemberRequest $r): RedirectResponse
    {
        return $this->save($r);
    }

    public function update(TeamMemberRequest $r, int $id): RedirectResponse
    {
        return $this->save($r, $id);
    }
}

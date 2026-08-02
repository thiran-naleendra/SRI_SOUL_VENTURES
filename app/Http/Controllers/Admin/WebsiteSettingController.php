<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebsiteSettingRequest;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class WebsiteSettingController extends Controller
{
    public function edit(): View
    {
        Gate::authorize('viewAny', WebsiteSetting::class);

        return view('admin.settings.edit', ['setting' => WebsiteSetting::first()]);
    }

    public function update(WebsiteSettingRequest $request): RedirectResponse
    {
        $setting = WebsiteSetting::firstOrNew();
        $new = [];
        $old = [];
        try {
            DB::transaction(function () use ($request, $setting, &$new, &$old) {
                $data = $request->safe()->except(['logo', 'footer_logo', 'favicon']);
                foreach (['logo', 'footer_logo', 'favicon'] as $f) {
                    if ($request->hasFile($f)) {
                        $p = $request->file($f)->store('settings', 'public');
                        $new[] = $p;
                        if ($setting->{$f}) {
                            $old[] = $setting->{$f};
                        }$data[$f] = $p;
                    }
                }$setting->fill($data)->save();
            });
        } catch (Throwable$e) {
            Storage::disk('public')->delete($new);
            throw $e;
        }Storage::disk('public')->delete($old);

        return back()->with('success', 'Website settings updated.');
    }
}

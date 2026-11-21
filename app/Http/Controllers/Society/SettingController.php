<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\SocietySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Settings → General: per-society preferences stored as key/value rows.
 */
class SettingController extends Controller
{
    public function general(): View
    {
        $society = $this->currentSociety()->load('settings');

        return view('society.settings.general', [
            'groups' => collect(SocietySetting::DEFINITIONS)
                ->map(fn (array $definition, string $key) => $definition + ['key' => $key, 'value' => $society->setting($key)])
                ->groupBy('group'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();

        $rules = collect(SocietySetting::DEFINITIONS)->map(fn (array $d) => match ($d['type']) {
            'bool' => ['nullable', 'boolean'],
            'month' => ['required', 'integer', 'between:1,12'],
            'int' => ['required', 'integer', 'min:'.($d['min'] ?? 0), 'max:'.($d['max'] ?? 1000000)],
            default => ['nullable', 'string', 'max:255'],
        })->all();

        $data = $request->validate($rules, [], collect(SocietySetting::DEFINITIONS)->map(fn (array $d) => strtolower($d['label']))->all());

        foreach (SocietySetting::DEFINITIONS as $key => $definition) {
            $value = $definition['type'] === 'bool'
                ? ($request->boolean($key) ? '1' : '0')
                : (string) $data[$key];

            SocietySetting::updateOrCreate(['society_id' => $society->id, 'key' => $key], ['value' => $value]);
        }

        return redirect()->route('society.settings.general')->with('success', 'Settings saved.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $networkId = $request->integer('network_id') ?: null;

        $units = Unit::query()
            ->with('network')
            ->when($networkId, function ($query) use ($networkId) {
                $query->where('network_id', $networkId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('document', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $networks = Network::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('units.index', compact(
            'units',
            'networks',
            'search',
            'networkId'
        ));
    }

    public function create(): View
    {
        $networks = Network::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('units.create', compact('networks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'network_id' => [
                'required',
                Rule::exists('networks', 'id')
                    ->where(fn ($query) => $query->where('active', true)),
            ],

            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'document' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],

            'postal_code' => ['nullable', 'string', 'max:10'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
        ]);

        if (! empty($validated['code'])) {
            $request->validate([
                'code' => [
                    Rule::unique('units', 'code')
                        ->where(
                            fn ($query) =>
                                $query->where('network_id', $validated['network_id'])
                        ),
                ],
            ]);
        }

        $validated['state'] = isset($validated['state'])
            ? strtoupper($validated['state'])
            : null;

        $validated['active'] = true;

        Unit::create($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'Unidade cadastrada com sucesso.');
    }

    public function edit(Unit $unit): View
    {
        $networks = Network::query()
            ->where('active', true)
            ->orWhere('id', $unit->network_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('units.edit', compact(
            'unit',
            'networks'
        ));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'network_id' => ['required', 'exists:networks,id'],
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('units', 'code')
                    ->where(
                        fn ($query) =>
                            $query->where(
                                'network_id',
                                $request->input('network_id')
                            )
                    )
                    ->ignore($unit->id),
            ],

            'document' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],

            'postal_code' => ['nullable', 'string', 'max:10'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
        ]);

        $validated['state'] = isset($validated['state'])
            ? strtoupper($validated['state'])
            : null;

        $unit->update($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'Unidade atualizada com sucesso.');
    }

    public function toggleStatus(Unit $unit): RedirectResponse
    {
        $unit->update([
            'active' => ! $unit->active,
        ]);

        return redirect()
            ->route('units.index')
            ->with(
                'success',
                $unit->active
                    ? 'Unidade ativada com sucesso.'
                    : 'Unidade desativada com sucesso.'
            );
    }
}

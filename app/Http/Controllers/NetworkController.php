<?php

namespace App\Http\Controllers;

use App\Models\Network;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NetworkController extends Controller
{
    /**
     * Lista as redes cadastradas.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));

        $networks = Network::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('legal_name', 'like', "%{$search}%")
                        ->orWhere('document', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('networks.index', compact('networks', 'search'));
    }

    /**
     * Formulário de cadastro.
     */
    public function create(): View
    {
        return view('networks.create');
    }

    /**
     * Salva uma nova rede.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:20', 'unique:networks,document'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $validated['active'] = true;

        Network::create($validated);

        return redirect()
            ->route('networks.index')
            ->with('success', 'Rede cadastrada com sucesso.');
    }

    /**
     * Formulário de edição.
     */
    public function edit(Network $network): View
    {
        return view('networks.edit', compact('network'));
    }

    /**
     * Atualiza uma rede.
     */
    public function update(Request $request, Network $network): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],

            'document' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('networks', 'document')->ignore($network->id),
            ],

            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $network->update($validated);

        return redirect()
            ->route('networks.index')
            ->with('success', 'Rede atualizada com sucesso.');
    }

    /**
     * Ativa/desativa uma rede.
     *
     * Não excluímos fisicamente registros.
     */
    public function toggleStatus(Network $network): RedirectResponse
    {
        $network->update([
            'active' => ! $network->active,
        ]);

        $message = $network->active
            ? 'Rede ativada com sucesso.'
            : 'Rede desativada com sucesso.';

        return redirect()
            ->route('networks.index')
            ->with('success', $message);
    }
}
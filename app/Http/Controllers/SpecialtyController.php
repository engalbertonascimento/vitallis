<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SpecialtyController extends Controller
{
    /**
     * Lista as especialidades.
     */
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->get('search')
        );

        $specialties = Specialty::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view(
            'specialties.index',
            compact(
                'specialties',
                'search'
            )
        );
    }

    /**
     * Formulário para nova especialidade.
     */
    public function create(): View
    {
        return view('specialties.create');
    }

    /**
     * Cadastra a especialidade.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:specialties,name',
            ],

            'code' => [
                'nullable',
                'string',
                'max:50',
                'unique:specialties,code',
            ],

            'description' => [
                'nullable',
                'string',
                'max:65535',
            ],
        ]);

        $validated['name'] = trim(
            $validated['name']
        );

        $validated['code'] = ! empty(
            $validated['code']
        )
            ? strtoupper(
                trim($validated['code'])
            )
            : null;

        $validated['active'] = true;

        Specialty::create($validated);

        return redirect()
            ->route('specialties.index')
            ->with(
                'success',
                'Especialidade cadastrada com sucesso.'
            );
    }

    /**
     * Formulário de edição.
     */
    public function edit(
        Specialty $specialty
    ): View {
        return view(
            'specialties.edit',
            compact('specialty')
        );
    }

    /**
     * Atualiza a especialidade.
     */
    public function update(
        Request $request,
        Specialty $specialty
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'specialties',
                    'name'
                )->ignore($specialty->id),
            ],

            'code' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique(
                    'specialties',
                    'code'
                )->ignore($specialty->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:65535',
            ],
        ]);

        $validated['name'] = trim(
            $validated['name']
        );

        $validated['code'] = ! empty(
            $validated['code']
        )
            ? strtoupper(
                trim($validated['code'])
            )
            : null;

        $specialty->update($validated);

        return redirect()
            ->route('specialties.index')
            ->with(
                'success',
                'Especialidade atualizada com sucesso.'
            );
    }

    /**
     * Ativa ou desativa a especialidade.
     *
     * Não fazemos exclusão física para preservar
     * referências históricas em agendas.
     */
    public function toggleStatus(
        Specialty $specialty
    ): RedirectResponse {
        $specialty->update([
            'active' => ! $specialty->active,
        ]);

        return redirect()
            ->route('specialties.index')
            ->with(
                'success',
                $specialty->active
                    ? 'Especialidade ativada com sucesso.'
                    : 'Especialidade desativada com sucesso.'
            );
    }
}

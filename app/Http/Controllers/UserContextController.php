<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserContextController extends Controller
{
    /**
     * Exibe os contextos disponíveis para o usuário.
     */
    public function index(Request $request): View
    {
        $units = $this->accessibleUnits($request);

        $currentContext = DB::table('user_contexts')
            ->where('user_id', $request->user()->id)
            ->first();

        return view('contexts.index', compact(
            'units',
            'currentContext'
        ));
    }

    /**
     * Define a unidade ativa do usuário.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => [
                'required',
                'integer',
                'exists:units,id',
            ],
        ]);

        $user = $request->user();

        /*
         * Administrador Vitallis é global e não depende
         * obrigatoriamente de contexto operacional.
         */
        if ($user->isVitallisAdmin()) {
            return back()->withErrors([
                'unit_id' => 'O Administrador Vitallis possui acesso global e não necessita selecionar uma unidade operacional.',
            ]);
        }

        $unit = Unit::query()
            ->with('network')
            ->whereKey($validated['unit_id'])
            ->where('active', true)
            ->firstOrFail();

        /*
         * Confirma que a unidade realmente pertence
         * ao conjunto autorizado para este usuário.
         */
        $hasAccess = $this->accessibleUnits($request)
            ->contains(
                fn (Unit $accessibleUnit) =>
                    $accessibleUnit->id === $unit->id
            );

        if (! $hasAccess) {
            abort(
                403,
                'Você não possui acesso a esta unidade.'
            );
        }

        DB::table('user_contexts')->updateOrInsert(
            [
                'user_id' => $user->id,
            ],
            [
                'network_id' => $unit->network_id,
                'unit_id' => $unit->id,
                'selected_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        /*
         * Também mantemos o contexto na sessão.
         * Isso evita consultas desnecessárias em operações
         * posteriores da mesma sessão.
         */
        $request->session()->put(
            'active_network_id',
            $unit->network_id
        );

        $request->session()->put(
            'active_unit_id',
            $unit->id
        );

        return back()->with(
            'success',
            'Unidade de trabalho alterada para '.$unit->name.'.'
        );
    }

    /**
     * Retorna as unidades que o usuário pode utilizar.
     *
     * role_user:
     *
     * unit_id preenchido:
     *     acesso somente àquela unidade.
     *
     * unit_id NULL:
     *     papel válido para todas as unidades da rede.
     */
    private function accessibleUnits(Request $request): Collection
    {
        $user = $request->user();

        /*
         * Administrador global pode acessar todas as unidades
         * ativas quando necessário administrativamente.
         */
        if ($user->isVitallisAdmin()) {
            return Unit::query()
                ->with('network')
                ->where('active', true)
                ->whereHas(
                    'network',
                    fn ($query) =>
                        $query->where('active', true)
                )
                ->orderBy('name')
                ->get();
        }

        $links = DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('active', true)
            ->whereNotNull('network_id')
            ->get([
                'network_id',
                'unit_id',
            ]);

        /*
         * Vínculos onde unit_id é NULL concedem acesso
         * às unidades da rede inteira.
         */
        $networkWideIds = $links
            ->whereNull('unit_id')
            ->pluck('network_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        /*
         * Vínculos explicitamente associados a unidades.
         */
        $specificUnitIds = $links
            ->whereNotNull('unit_id')
            ->pluck('unit_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        return Unit::query()
            ->with('network')
            ->where('active', true)
            ->whereHas(
                'network',
                fn ($query) =>
                    $query->where('active', true)
            )
            ->where(function ($query) use (
                $networkWideIds,
                $specificUnitIds
            ) {
                if ($networkWideIds->isNotEmpty()) {
                    $query->whereIn(
                        'network_id',
                        $networkWideIds
                    );
                }

                if ($specificUnitIds->isNotEmpty()) {
                    $networkWideIds->isNotEmpty()
                        ? $query->orWhereIn(
                            'id',
                            $specificUnitIds
                        )
                        : $query->whereIn(
                            'id',
                            $specificUnitIds
                        );
                }

                /*
                 * Usuário sem nenhum vínculo ativo não deve
                 * receber unidades por acidente.
                 */
                if (
                    $networkWideIds->isEmpty()
                    && $specificUnitIds->isEmpty()
                ) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('name')
            ->get();
    }
}

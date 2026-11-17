<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Lista as grades da unidade ativa.
     */
    public function index(Request $request): View
    {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho para acessar as grades.'
            );
        }

        $unit = Unit::query()
            ->with('network')
            ->whereKey($context->unit_id)
            ->where('network_id', $context->network_id)
            ->firstOrFail();

        $schedules = Schedule::query()
            ->with('professional')
            ->where('network_id', $context->network_id)
            ->where('unit_id', $context->unit_id)
            ->orderBy('professional_user_id')
            ->orderBy('weekday')
            ->orderBy('starts_at')
            ->get();

        return view(
            'schedules.index',
            compact(
                'unit',
                'schedules'
            )
        );
    }

    /**
     * Formulário para criação de grade.
     */
    public function create(Request $request): View
    {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho antes de criar uma grade.'
            );
        }

        $unit = Unit::query()
            ->with('network')
            ->whereKey($context->unit_id)
            ->where('network_id', $context->network_id)
            ->firstOrFail();

        /*
         * Profissionais com vínculo ativo na unidade
         * ou válido para toda a rede atual.
         */
        $professionalIds = DB::table('role_user')
            ->where('network_id', $context->network_id)
            ->where('active', true)
            ->where(function ($query) use ($context) {
                $query
                    ->where('unit_id', $context->unit_id)
                    ->orWhereNull('unit_id');
            })
            ->pluck('user_id')
            ->unique();

        $professionals = User::query()
            ->where('active', true)
            ->whereIn('id', $professionalIds)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        return view(
            'schedules.create',
            compact(
                'unit',
                'professionals'
            )
        );
    }

    /**
     * Salva a nova grade.
     */
    public function store(Request $request): RedirectResponse
    {
        $context = $this->activeContext($request);

        if (! $context) {
            abort(
                403,
                'Selecione uma unidade de trabalho antes de criar uma grade.'
            );
        }

        $validated = $request->validate([
            'professional_user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'specialty' => [
                'nullable',
                'string',
                'max:255',
            ],

            'starts_on' => [
                'required',
                'date',
            ],

            'ends_on' => [
                'nullable',
                'date',
                'after_or_equal:starts_on',
            ],

            'weekday' => [
                'required',
                'integer',
                'between:0,6',
            ],

            'starts_at' => [
                'required',
                'date_format:H:i',
            ],

            'ends_at' => [
                'required',
                'date_format:H:i',
            ],

            'slot_duration_minutes' => [
                'required',
                'integer',
                'min:5',
                'max:480',
            ],

            'slot_capacity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'room' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        if (
            $validated['ends_at']
            <= $validated['starts_at']
        ) {
            throw ValidationException::withMessages([
                'ends_at' =>
                    'O horário final deve ser posterior ao horário inicial.',
            ]);
        }

        /*
         * O profissional precisa estar vinculado à unidade
         * ou possuir vínculo válido para toda a rede.
         */
        $professionalHasAccess = DB::table('role_user')
            ->where(
                'user_id',
                $validated['professional_user_id']
            )
            ->where(
                'network_id',
                $context->network_id
            )
            ->where('active', true)
            ->where(function ($query) use ($context) {
                $query
                    ->where(
                        'unit_id',
                        $context->unit_id
                    )
                    ->orWhereNull('unit_id');
            })
            ->exists();

        if (! $professionalHasAccess) {
            throw ValidationException::withMessages([
                'professional_user_id' =>
                    'O profissional selecionado não possui vínculo ativo com esta unidade.',
            ]);
        }

        Schedule::create([
            'network_id' =>
                $context->network_id,

            'unit_id' =>
                $context->unit_id,

            'professional_user_id' =>
                $validated['professional_user_id'],

            'name' =>
                $validated['name'] ?? null,

            'specialty' =>
                $validated['specialty'] ?? null,

            'starts_on' =>
                $validated['starts_on'],

            'ends_on' =>
                $validated['ends_on'] ?? null,

            'weekday' =>
                $validated['weekday'],

            'starts_at' =>
                $validated['starts_at'],

            'ends_at' =>
                $validated['ends_at'],

            'slot_duration_minutes' =>
                $validated['slot_duration_minutes'],

            'slot_capacity' =>
                $validated['slot_capacity'],

            'room' =>
                $validated['room'] ?? null,

            'active' => true,
        ]);

        return redirect()
            ->route('schedules.index')
            ->with(
                'success',
                'Grade de agenda criada com sucesso.'
            );
    }

    /**
     * Resolve o contexto ativo.
     */
    private function activeContext(Request $request): ?object
    {
        $networkId = $request->session()->get(
            'active_network_id'
        );

        $unitId = $request->session()->get(
            'active_unit_id'
        );

        if ($networkId && $unitId) {
            return (object) [
                'network_id' => (int) $networkId,
                'unit_id' => (int) $unitId,
            ];
        }

        $context = DB::table('user_contexts')
            ->where(
                'user_id',
                $request->user()->id
            )
            ->first();

        if (
            ! $context
            || ! $context->network_id
            || ! $context->unit_id
        ) {
            return null;
        }

        return (object) [
            'network_id' =>
                (int) $context->network_id,

            'unit_id' =>
                (int) $context->unit_id,
        ];
    }
}
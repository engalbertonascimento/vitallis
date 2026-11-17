<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar dashboard',
                'slug' => 'dashboard.view',
            ],


            /*
            |--------------------------------------------------------------------------
            | Pacientes
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar pacientes',
                'slug' => 'patients.view',
            ],
            [
                'name' => 'Cadastrar pacientes',
                'slug' => 'patients.create',
            ],
            [
                'name' => 'Editar pacientes',
                'slug' => 'patients.update',
            ],


            /*
            |--------------------------------------------------------------------------
            | Agendamentos
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar agendamentos',
                'slug' => 'appointments.view',
            ],
            [
                'name' => 'Criar agendamentos',
                'slug' => 'appointments.create',
            ],
            [
                'name' => 'Editar agendamentos',
                'slug' => 'appointments.update',
            ],
            [
                'name' => 'Cancelar agendamentos',
                'slug' => 'appointments.cancel',
            ],
            [
                'name' => 'Realizar check-in',
                'slug' => 'appointments.checkin',
            ],
            [
                'name' => 'Confirmar agendamento',
                'slug' => 'appointments.confirm',
            ],
            [
                'name' => 'Registrar ausência',
                'slug' => 'appointments.no_show',
            ],


            /*
            |--------------------------------------------------------------------------
            | Grades de Agenda
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar grades de agenda',
                'slug' => 'schedules.view',
            ],
            [
                'name' => 'Criar grades de agenda',
                'slug' => 'schedules.create',
            ],
            [
                'name' => 'Editar grades de agenda',
                'slug' => 'schedules.update',
            ],
            [
                'name' => 'Bloquear horários de agenda',
                'slug' => 'schedules.block',
            ],
            [
                'name' => 'Vincular profissional à agenda',
                'slug' => 'schedules.professional.assign',
            ],
            [
                'name' => 'Vincular sala à agenda',
                'slug' => 'schedules.room.assign',
            ],


            /*
            |--------------------------------------------------------------------------
            | Especialidades
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar especialidades',
                'slug' => 'specialties.view',
            ],
            [
                'name' => 'Gerenciar especialidades',
                'slug' => 'specialties.manage',
            ],


            /*
            |--------------------------------------------------------------------------
            | Locais de Atendimento
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar locais de atendimento',
                'slug' => 'service_locations.view',
            ],
            [
                'name' => 'Gerenciar locais de atendimento',
                'slug' => 'service_locations.manage',
            ],


            /*
            |--------------------------------------------------------------------------
            | Prontuário
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar prontuário',
                'slug' => 'medical_records.view',
            ],
            [
                'name' => 'Criar registros no prontuário',
                'slug' => 'medical_records.create',
            ],
            [
                'name' => 'Editar registros no prontuário',
                'slug' => 'medical_records.update',
            ],


            /*
            |--------------------------------------------------------------------------
            | Prescrições
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar prescrições',
                'slug' => 'prescriptions.view',
            ],
            [
                'name' => 'Criar prescrições',
                'slug' => 'prescriptions.create',
            ],


            /*
            |--------------------------------------------------------------------------
            | Exames
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar exames',
                'slug' => 'exams.view',
            ],
            [
                'name' => 'Criar solicitações de exames',
                'slug' => 'exams.create',
            ],


            /*
            |--------------------------------------------------------------------------
            | Usuários
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar usuários',
                'slug' => 'users.view',
            ],
            [
                'name' => 'Cadastrar usuários',
                'slug' => 'users.create',
            ],
            [
                'name' => 'Editar usuários',
                'slug' => 'users.update',
            ],


            /*
            |--------------------------------------------------------------------------
            | Funções
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar funções',
                'slug' => 'roles.view',
            ],
            [
                'name' => 'Gerenciar funções',
                'slug' => 'roles.manage',
            ],


            /*
            |--------------------------------------------------------------------------
            | Permissões
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar permissões',
                'slug' => 'permissions.view',
            ],
            [
                'name' => 'Gerenciar permissões',
                'slug' => 'permissions.manage',
            ],


            /*
            |--------------------------------------------------------------------------
            | Permissões por Unidade
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar permissões da unidade',
                'slug' => 'unit_permissions.view',
            ],
            [
                'name' => 'Gerenciar permissões da unidade',
                'slug' => 'unit_permissions.manage',
            ],


            /*
            |--------------------------------------------------------------------------
            | Permissões especiais do usuário
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar permissões especiais de usuários',
                'slug' => 'user_permissions.view',
            ],
            [
                'name' => 'Gerenciar permissões especiais de usuários',
                'slug' => 'user_permissions.manage',
            ],


            /*
            |--------------------------------------------------------------------------
            | Relatórios
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar relatórios',
                'slug' => 'reports.view',
            ],


            /*
            |--------------------------------------------------------------------------
            | Redes
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar redes',
                'slug' => 'network.view',
            ],
            [
                'name' => 'Gerenciar redes',
                'slug' => 'network.manage',
            ],


            /*
            |--------------------------------------------------------------------------
            | Unidades
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar unidades',
                'slug' => 'unit.view',
            ],
            [
                'name' => 'Gerenciar unidades',
                'slug' => 'unit.manage',
            ],


            /*
            |--------------------------------------------------------------------------
            | Auditoria
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Visualizar auditoria',
                'slug' => 'audit.view',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'slug' => $permission['slug'],
                ],
                $permission
            );
        }
    }
}

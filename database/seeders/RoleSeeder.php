<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrador Vitallis',
                'slug' => 'vitallis-admin',
                'description' => 'Administrador global da plataforma Vitallis.',
                'system' => true,
            ],
            [
                'name' => 'Administrador da Rede',
                'slug' => 'network-admin',
                'description' => 'Administrador de uma rede e suas unidades.',
                'system' => false,
            ],
            [
                'name' => 'Gestor da Unidade',
                'slug' => 'unit-manager',
                'description' => 'Responsável pela gestão de uma unidade.',
                'system' => false,
            ],
            [
                'name' => 'Médico',
                'slug' => 'doctor',
                'description' => 'Profissional médico.',
                'system' => false,
            ],
            [
                'name' => 'Enfermagem',
                'slug' => 'nursing',
                'description' => 'Profissional de enfermagem.',
                'system' => false,
            ],
            [
                'name' => 'Recepção',
                'slug' => 'reception',
                'description' => 'Profissional de recepção e atendimento.',
                'system' => false,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                array_merge($roleData, ['active' => true])
            );
        }

        // Administrador Vitallis possui todas as permissões.
        $vitallisAdmin = Role::where('slug', 'vitallis-admin')->firstOrFail();

        $vitallisAdmin->permissions()->sync(
            Permission::pluck('id')->all()
        );

        // Administrador da Rede
        $this->syncPermissions('network-admin', [
            'dashboard.view',
            'patients.view',
            'patients.create',
            'patients.update',
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.cancel',
            'users.view',
            'users.create',
            'users.update',
            'reports.view',
            'network.view',
            'unit.view',
            'unit.manage',
            'audit.view',
        ]);

        // Gestor da Unidade
        $this->syncPermissions('unit-manager', [
            'dashboard.view',
            'patients.view',
            'patients.create',
            'patients.update',
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.cancel',
            'users.view',
            'reports.view',
            'unit.view',
        ]);

        // Médico
        $this->syncPermissions('doctor', [
            'dashboard.view',
            'patients.view',
            'appointments.view',
            'medical_records.view',
            'medical_records.create',
            'medical_records.update',
            'prescriptions.view',
            'prescriptions.create',
            'exams.view',
            'exams.create',
        ]);

        // Enfermagem
        $this->syncPermissions('nursing', [
            'dashboard.view',
            'patients.view',
            'appointments.view',
            'medical_records.view',
            'medical_records.create',
            'exams.view',
        ]);

        // Recepção
        $this->syncPermissions('reception', [
            'dashboard.view',
            'patients.view',
            'patients.create',
            'patients.update',
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.cancel',
        ]);
    }

    private function syncPermissions(string $roleSlug, array $permissions): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();

        $ids = Permission::whereIn('slug', $permissions)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($ids);
    }
}
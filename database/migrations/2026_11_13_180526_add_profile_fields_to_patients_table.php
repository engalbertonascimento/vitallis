<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('social_name')->nullable()->after('name');

            $table->string('cpf', 14)->nullable()->unique()->after('social_name');
            $table->string('cns', 20)->nullable()->unique()->after('cpf');

            $table->date('birth_date')->nullable()->after('cns');
            $table->string('sex', 30)->nullable()->after('birth_date');

            $table->string('mother_name')->nullable()->after('sex');
            $table->string('father_name')->nullable()->after('mother_name');

            $table->string('email')->nullable()->after('father_name');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('mobile_phone', 30)->nullable()->after('phone');

            $table->string('postal_code', 10)->nullable()->after('mobile_phone');
            $table->string('street')->nullable()->after('postal_code');
            $table->string('number', 20)->nullable()->after('street');
            $table->string('complement')->nullable()->after('number');
            $table->string('district')->nullable()->after('complement');
            $table->string('city')->nullable()->after('district');
            $table->char('state', 2)->nullable()->after('city');

            $table->boolean('active')->default(true)->after('state');

            $table->index(['name', 'birth_date']);
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['name', 'birth_date']);
            $table->dropIndex(['active']);

            $table->dropUnique(['cpf']);
            $table->dropUnique(['cns']);

            $table->dropColumn([
                'name',
                'social_name',
                'cpf',
                'cns',
                'birth_date',
                'sex',
                'mother_name',
                'father_name',
                'email',
                'phone',
                'mobile_phone',
                'postal_code',
                'street',
                'number',
                'complement',
                'district',
                'city',
                'state',
                'active',
            ]);
        });
    }
};

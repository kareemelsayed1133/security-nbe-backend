<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'name' => 'admin',
                'display_name_ar' => 'مدير النظام',
                'description' => 'يمتلك جميع الصلاحيات في النظام',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'supervisor',
                'display_name_ar' => 'مشرف أمن',
                'description' => 'يدير الحراس والعمليات اليومية في فرعه',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'guard',
                'display_name_ar' => 'حارس أمن',
                'description' => 'يقوم بالمهام الأمنية اليومية في الفرع',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}


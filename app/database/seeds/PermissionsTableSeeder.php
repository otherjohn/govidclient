<?php

class PermissionsTableSeeder extends Seeder {

    public function run()
    {
        DB::table('permissions')->delete();

        $permissions = array(
            array( // 4
                'name'         => 'manage_users',
                'display_name' => 'Manage Users'
            ),
            array( // 5
                'name'         => 'manage_roles',
                'display_name' => 'Manage Roles'
            ),
            array( // 1
                'name'         => 'manage_patients',
                'display_name' => 'Manage Patients'
            ),
            array( // 4
                'name'         => 'access_profile',
                'display_name' => 'Access own profile'
            ),

        );

        DB::table('permissions')->insert( $permissions );

        DB::table('permission_role')->delete();

        $role_id_admin = Role::where('name', '=', 'admin')->first()->id;
        $role_id_doctor = Role::where('name', '=', 'doctor')->first()->id;
        $role_id_patient = Role::where('name', '=', 'patient')->first()->id;
        $permission_base = (int)DB::table('permissions')->first()->id - 1;

        $permissions = array(
            array(
                'role_id'       => $role_id_admin,
                'permission_id' => $permission_base + 1
            ),
            array(
                'role_id'       => $role_id_admin,
                'permission_id' => $permission_base + 2
            ),
            array(
                'role_id'       => $role_id_admin,
                'permission_id' => $permission_base + 3
            ),
            array(
                'role_id'       => $role_id_admin,
                'permission_id' => $permission_base + 4
            ),
            array(
                'role_id'       => $role_id_doctor,
                'permission_id' => $permission_base + 3
            ),
            array(
                'role_id'       => $role_id_doctor,
                'permission_id' => $permission_base + 4
            ),
            array(
                'role_id'       => $role_id_patient,
                'permission_id' => $permission_base + 4
            )
        );

        DB::table('permission_role')->insert( $permissions );
    }

}
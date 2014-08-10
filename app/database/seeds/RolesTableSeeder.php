<?php

class RolesTableSeeder extends Seeder {

    public function run()
    {
        DB::table('roles')->delete();

        $adminRole = new Role;
        $adminRole->name = 'admin';
        $adminRole->save();

        $doctorRole = new Role;
        $doctorRole->name = 'doctor';
        $doctorRole->save();

        $patientRole = new Role;
        $patientRole->name = 'patient';
        $patientRole->save();

        //$user = User::where('id','=','1')->first();
        //$user->attachRole( $adminRole );

        //$user = User::where('id','=','2')->first();
        //$user->attachRole( $patientRole );
    }

}
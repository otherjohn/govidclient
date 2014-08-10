<?php

class UsersTableSeeder extends Seeder {

public function keygen($length=40)
{
    $key = '';
    list($usec, $sec) = explode(' ', microtime());
    mt_srand((float) $sec + ((float) $usec * 100000));
    
    $inputs = array_merge(range('z','a'),range(0,9),range('A','Z'));

    for($i=0; $i<$length; $i++)
    {
        $key .= $inputs{mt_rand(0,61)};
    }
    return $key;
}


    public function run()
    {
        DB::table('users')->delete();


        $users = array(
            array(
                'access_token' => $this->keygen(),
                'created_at' => new DateTime,
                'updated_at' => new DateTime,
            ),
            array(
                'access_token' => $this->keygen(),
                'created_at' => new DateTime,
                'updated_at' => new DateTime,
            )
        );

        DB::table('users')->insert( $users );
    }

}

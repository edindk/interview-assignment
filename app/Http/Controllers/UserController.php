<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function migrateAndSeedDB(): string
    {
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');
        return json_encode('Successfully migrated and seeded DB');
    }

    public function deleteUser($id): string
    {
        DB::table('users')->delete($id);
        return json_encode('Successfully deleted the user with the following id: ' . $id);
    }

    public function export(): Response
    {
        $users = DB::table('users')->get()->toArray();
        $columns = Schema::getColumnListing('users');

        $path = $this->generateCsv($users, $columns);
        DB::table('users')->delete();
        return response()->download($path);
    }

    private function generateCsv($users, $columns): string
    {
        Storage::disk('local')->delete('users.csv');
        Storage::disk('local')->put('users.csv', null);
        $path = Storage::disk('local')->path('users.csv');

        $file = fopen($path, 'w');
        fputcsv($file, $columns);

        foreach ($users as $user) {
            $row = [
                $user->id,
                $user->firstName,
                $user->lastName,
                $user->email,
            ];
            fputcsv($file, $row);
        }
        fclose($file);

        return $path;
    }

}

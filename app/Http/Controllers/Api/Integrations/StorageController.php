<?php

namespace App\Http\Controllers\Api\Integrations;



class StorageController
{


    public function getImage($path){
        $path = storage_path('app/' . $path);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function getCurrentEmail()
    {
        return response()->json(env('MAIL_FROM_ADDRESS'));

    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Router;
use Illuminate\Support\Facades\Log;

class ExampleController extends Controller
{
    public function verification(Request $request, Router $router)
    {
        $data = $request->data;
        
        Log::info($data);

        $response = $router->routing($data);

        Log::info($response);

        return $response;
    }
}

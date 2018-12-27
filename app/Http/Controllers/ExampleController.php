<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Parser;
use Illuminate\Support\Facades\Log;

class ExampleController extends Controller
{
    public function verification(Request $request, Parser $parser)
    {
        
        $data = $request->data;

        Log::info($data);

        $parser->verification($data);

        return $parser->iso();

    }
}

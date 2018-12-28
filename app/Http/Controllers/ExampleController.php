<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Parser;
use App\Isos\Adiq;
use Illuminate\Support\Facades\Log;

class ExampleController extends Controller
{
    public function verification(Request $request, Parser $parser)
    {
        
        $data = $request->data;

        Log::info($data);

        $parser->set($data);

        return $parser->iso((new Adiq)->getIso());

    }
}

<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlaceholderController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->route('page') ?? 'This page';

        return view('society.placeholder', compact('page'));
    }
}

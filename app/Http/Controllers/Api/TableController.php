<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        return response()->json(Table::all());
    }

    public function show(Table $table)
    {
        return response()->json($table->load('orders'));
    }
}

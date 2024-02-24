<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Inventory;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Report', [
            'data' => Delivery::with('inventories')->orderBy('id', 'desc')->get()
        ]);
    }
}

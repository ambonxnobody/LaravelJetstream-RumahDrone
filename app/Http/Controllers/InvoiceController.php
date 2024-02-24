<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        return Inertia::render('Invoice', [
            'data' => Delivery::with('inventories')->firstWhere('id', $id),
        ]);
    }
}

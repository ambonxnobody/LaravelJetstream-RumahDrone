<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Inventory', [
            'data' => Inventory::with('deliveries')->orderBy('id', 'desc')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:inventories'],
            'stock' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'weight' => ['required', 'numeric'],
            'image' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'minimum_stock' => ['required', 'numeric'],
        ])->validateWithBag('createInventoryInformation');

        Inventory::create($request->all());

//        if (isset($request['image'])) {
//            $inventory->addOrUpdateImage($request['image'], 0);
//        }

        $request->session()->flash('flash.banner', 'Inventory created successfully!');

        return redirect()->route('inventory.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        Validator::make($inventory->toArray(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('inventories')->ignore($inventory->id)],
            'stock' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'weight' => ['required', 'numeric'],
            'image' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'minimum_stock' => ['required', 'numeric'],
        ])->validateWithBag('updateInventoryInformation');

        $inventory->forceFill($request->all())->save();

//        if (isset($request['image'])) {
//            $inventory->addOrUpdateImage($request['image'], $inventory->id);
//        }

        $request->session()->flash('flash.banner', 'Inventory updated successfully!');

        return redirect()->route('inventory.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory): RedirectResponse
    {
        $inventory->delete();

        request()->session()->flash('flash.banner', 'Inventory deleted successfully!');

        return redirect()->route('inventory.index');
    }

    public function removeImage(Inventory $inventory): RedirectResponse
    {
        $inventory->removeImage();

        return redirect()->route('inventory.index');
    }
}

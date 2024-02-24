<?php

namespace App\Http\Controllers;

use App\Events\LowStockNotification;
use App\Models\Delivery;
use App\Models\Inventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Delivery', [
            'data' => Delivery::with('inventories')->orderBy('id', 'desc')->get(),
            'inventory' => Inventory::orderBy('id', 'desc')->where('stock', '>', 0)->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        foreach ($request->inventory as $key => $value) {
            $inventory = Inventory::first('id', $value['inventory_id'])->get()[0];

            if ($value['quantity'] > $inventory->stock && $request->status === 'delivered') {
                $request->session()->flash('flash.banner', 'The stock of ' . $inventory->name . ' is not enough!');
                $request->session()->flash('flash.bannerStyle', 'danger');
                return redirect()->route('delivery.index');
            }
        }

        Validator::make($request->all(), [
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(['pending', 'delivered'])],
            'type' => ['required', Rule::in(['inbound', 'outbound'])],
            'notes' => ['nullable', 'string'],
            'inventory' => 'required|array|min:1',
        ])->validateWithBag('createDeliveryInformation');

        $request->merge(['order_number' => 'INV/' . $request->date . '/RD/' . time()]);

        $delivery = Delivery::create($request->all());

        $delivery->inventories()->attach($request->inventory);

        foreach ($request->inventory as $key => $value) {
            $inventory = Inventory::first('id', $value['inventory_id'])->get()[0];

            if ($request->status === 'delivered') {
                if ($request->type === 'inbound') {
                    $inventory->forceFill([
                        'stock_in' => $inventory->stock_in + $value['quantity'],
                        'stock' => $inventory->stock + $value['quantity'],
                    ])->save();
                } else {
                    $inventory->forceFill([
                        'stock_out' => $inventory->stock_out + $value['quantity'],
                        'stock' => $inventory->stock - $value['quantity'],
                    ])->save();
                }
            }

            if ($inventory->stock <= $inventory->minimum_stock) {
                event(new LowStockNotification('The stock of ' . $inventory->name . ' is low!'));
            }
        }

        $request->session()->flash('flash.banner', 'Delivery created successfully!');

        return redirect()->route('delivery.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Delivery $delivery): RedirectResponse
    {
        foreach ($request->inventory as $key => $value) {
            $inventory = Inventory::first('id', $value['inventory_id'])->get()[0];

            if ($value['quantity'] > $inventory->stock && $request->status === 'delivered') {
                $request->session()->flash('flash.banner', 'The stock of ' . $inventory->name . ' is not enough!');
                $request->session()->flash('flash.bannerStyle', 'danger');
                return redirect()->route('delivery.index');
            }
        }

        if ($request->status === 'delivered') {
            $request->session()->flash('flash.banner', 'You cannot update delivery with delivered status!');
            $request->session()->flash('flash.bannerStyle', 'danger');
            return redirect()->route('delivery.index');
        }

        Validator::make($delivery->toArray(), [
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(['pending', 'delivered'])],
            'type' => ['required', Rule::in(['inbound', 'outbound'])],
            'notes' => ['nullable', 'string'],
            'inventory' => 'required|array|min:1',
        ])->validateWithBag('updateDeliveryInformation');

        $delivery->forceFill($request->all())->save();

        $delivery->inventories()->sync($request->inventory);

        foreach ($request->inventory as $key => $value) {
            $inventory = Inventory::first('id', $value['inventory_id'])->get()[0];

            if ($request->status === 'delivered') {
                if ($request->type === 'inbound') {
                    $inventory->forceFill([
                        'stock_in' => $inventory->stock_in + $value['quantity'],
                        'stock' => $inventory->stock + $value['quantity'],
                    ])->save();
                } else {
                    $inventory->forceFill([
                        'stock_out' => $inventory->stock_out + $value['quantity'],
                        'stock' => $inventory->stock - $value['quantity'],
                    ])->save();
                }
            }

            if ($inventory->stock <= $inventory->minimum_stock) {
                event(new LowStockNotification('The stock of ' . $inventory->name . ' is low!'));
            }
        }

        $request->session()->flash('flash.banner', 'Delivery updated successfully!');

        return redirect()->route('delivery.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Delivery $delivery): RedirectResponse
    {
        if ($delivery->status === 'delivered') {
            request()->session()->flash('flash.banner', 'You cannot update delivery with delivered status!');
            request()->session()->flash('flash.bannerStyle', 'danger');
            return redirect()->route('delivery.index');
        }

        $delivery->delete();

        request()->session()->flash('flash.banner', 'Delivery deleted successfully!');

        return redirect()->route('delivery.index');
    }
}

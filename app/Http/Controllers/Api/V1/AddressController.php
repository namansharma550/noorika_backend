<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->addresses;
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $address = $request->user()->addresses()->create($data);

        $this->handleDefault($request, $address->id, $data['is_default'] ?? false);

        return response()->json($address, 201);
    }

    public function update(Request $request, int $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $data = $this->validated($request);
        $address->update($data);

        $this->handleDefault($request, $address->id, $data['is_default'] ?? false);

        return response()->json($address);
    }

    public function destroy(Request $request, int $id)
    {
        $request->user()->addresses()->where('id', $id)->delete();

        return response()->json(['message' => 'Address removed']);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    protected function handleDefault(Request $request, int $addressId, bool $isDefault): void
    {
        if ($isDefault) {
            $request->user()->addresses()->where('id', '!=', $addressId)->update(['is_default' => false]);
        }
    }
}

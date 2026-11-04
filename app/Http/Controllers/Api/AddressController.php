<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddressRequest;
use App\Http\Resources\Api\AddressResource;
use App\Models\Address;
use App\Services\Address\AddressService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    private $addresses;

    public function __construct(AddressService $addresses)
    {
        $this->addresses = $addresses;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Address::class);

        return AddressResource::collection($this->addresses->forUser($request->user('api')));
    }

    public function store(AddressRequest $request)
    {
        $this->authorize('create', Address::class);
        $address = $this->addresses->create($request->user('api'), $request->validated());

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function show(Address $address)
    {
        $this->authorize('view', $address);

        return new AddressResource($address);
    }

    public function update(AddressRequest $request, Address $address)
    {
        $this->authorize('update', $address);

        return new AddressResource($this->addresses->update($address, $request->validated()));
    }
}

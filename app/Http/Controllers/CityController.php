<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(City::query()->orderBy('id')->get());
    }

    public function store(StoreCityRequest $request): JsonResponse
    {
        $city = City::query()->create($request->validated());

        return response()->json($city, Response::HTTP_CREATED);
    }

    public function show(City $city): JsonResponse
    {
        return response()->json($city);
    }

    public function update(UpdateCityRequest $request, City $city): JsonResponse
    {
        $city->update($request->validated());

        return response()->json($city->refresh());
    }

    public function destroy(City $city): Response
    {
        $city->delete();

        return response()->noContent();
    }
}

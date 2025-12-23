<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    private $jsonFile;

    public function __construct()
    {
        $this->jsonFile = storage_path('app/cities.json');
        if (!file_exists($this->jsonFile)) {
            file_put_contents($this->jsonFile, json_encode([]));
        }
    }

    private function getCities()
    {
        return json_decode(file_get_contents($this->jsonFile), true) ?? [];
    }

    private function saveCities($cities)
    {
        file_put_contents($this->jsonFile, json_encode($cities, JSON_PRETTY_PRINT));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(array_values($this->getCities()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'population' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cities = $this->getCities();
        // Simple ID generation
        $id = count($cities) > 0 ? max(array_keys($cities)) + 1 : 1;

        $city = [
            'id' => $id,
            'name' => $request->name,
            'country' => $request->country,
            'population' => $request->population ?? 0,
        ];

        $cities[$id] = $city;
        $this->saveCities($cities);

        return response()->json($city, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cities = $this->getCities();
        if (!isset($cities[$id])) {
            return response()->json(['message' => 'City not found'], 404);
        }

        return response()->json($cities[$id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cities = $this->getCities();
        if (!isset($cities[$id])) {
            return response()->json(['message' => 'City not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'country' => 'sometimes|required|string|max:255',
            'population' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $city = $cities[$id];

        if ($request->has('name')) {
            $city['name'] = $request->name;
        }
        if ($request->has('country')) {
            $city['country'] = $request->country;
        }
        if ($request->has('population')) {
            $city['population'] = $request->population;
        }

        $cities[$id] = $city;
        $this->saveCities($cities);

        return response()->json($city);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cities = $this->getCities();
        if (!isset($cities[$id])) {
            return response()->json(['message' => 'City not found'], 404);
        }

        unset($cities[$id]);
        $this->saveCities($cities);

        return response()->json(null, 204);
    }
}

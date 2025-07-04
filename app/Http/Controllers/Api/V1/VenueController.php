<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;
use \App\Http\Requests\VenueRequest;
use Illuminate\Support\Facades\Cache;

class VenueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $venues = Cache::remember('venues_list', 3600, function () {
            return Venue::active()->get(['id', 'name', 'location', 'capacity', 'price']);
        });

        return response()->json([
            'success' => true,
            'data'    => $venues
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $venue = Cache::remember("venue_{$id}", 3600, function () use ($id) {
            return Venue::active()->find($id);
        });

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'name'     => $venue->name,
                'location' => $venue->location,
                'capacity' => $venue->capacity,
                'price'    => $venue->price
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VenueRequest $request)
    {


        $venue = Venue::create($request->all());

        // Clear cache
        Cache::forget('venues_list');

        return response()->json([
            'success' => true,
            'data'    => $venue,
            'message' => 'Venue created successfully'
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VenueRequest $request, $id)
    {
        $venue = Venue::find($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found'
            ], 404);
        }



        $venue->update($request->all());

        // Clear cache
        Cache::forget('venues_list');
        Cache::forget("venue_{$id}");

        return response()->json([
            'success' => true,
            'data'    => $venue,
            'message' => 'Venue updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $venue = Venue::find($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Venue not found'
            ], 404);
        }

        $venue->delete();

        // Clear cache
        Cache::forget('venues_list');
        Cache::forget("venue_{$id}");

        return response()->json([
            'success' => true,
            'message' => 'Venue deleted successfully'
        ]);
    }
}

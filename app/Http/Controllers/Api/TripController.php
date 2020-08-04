<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Trip;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    // List all of the trips
    public function index()
    {
        $trips = Trip::OrderBy('created_at', 'desc')->simplePaginate(10);
        foreach($trips as $trip) {
            $trip['username'] = $trip->user->name;
            $trip['avatar'] = asset($trip->user->avatar);
            $trip['user_rating'] = round(DB::table('rates')->where('rated_user_id', $trip->user->id)->avg('rating'), 1);
        }

        return $trips;
    }

    // Show a single trip
    public function show(Trip $trip)
    {
        $trip['username'] = User::find($trip->user_id)->name;
        $trip['offers'] = $trip->offers;
        foreach ($trip['offers'] as $offer) {
            $offer['username'] = $offer->owner->name;
        }
        return response()->json(['data' => $trip]);
    }

    // Store a new trip
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'from' => ['required', 'string', 'min:3', 'max:255'],
                'to' => ['required', 'string', 'min:3', 'max:255'],
                'car_model' => ['required', 'string', 'max:255'],
                'price_per_passenger' => ['required', 'integer'],
                'number_of_empty_seats' => ['required', 'integer', 'min:1'],
                'departure_date' => ['required', 'date', 'after:today'],
                'description' => ['nullable', 'min:10', 'max:255']
            ]
        );

        if($validator->fails()) {
            return response()->json(['status' => 'Validation failure', 'errors' => $validator->errors()]);
        }

        $input = $request->all();
        $trip = Trip::create($input);

        return response()->json(['success' => true, 'data' => $trip], 201);
    }

    // Update a trip
    public function update(Request $request, Trip $trip)
    {
        if($trip->user_id != Auth::guard('api')->id()) {
            return response()->json(['status' => 'Forbidden'], 403);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'from' => ['required', 'string', 'min:3', 'max:255'],
                'to' => ['required', 'string', 'min:3', 'max:255'],
                'car_model' => ['required', 'string', 'max:255'],
                'price_per_passenger' => ['required', 'integer'],
                'number_of_empty_seats' => ['required', 'integer', 'min:1'],
                'departure_date' => ['required', 'date', 'after:today'],
                'description' => ['nullable', 'min:10', 'max:255']
            ]
        );

        if ($validator->fails()) {
            return response()->json(['status' => 'Validation failure', 'errors' => $validator->errors()]);
        }

        $input = $request->all();
        $trip->update($input);
        return response()->json(['success' => true, 'data' => $trip]);
    }

    // Delete a trip
    public function destroy(Trip $trip)
    {
        if ($trip->user_id != Auth::guard('api')->id()) {
            return response()->json(['status' => 'Forbidden'], 403);
        }
        $trip->delete();
        return response()->json(['success' => true, 'message' => 'Trip deleted successfuly.', 'data' => $trip], 200);
    }

    // Search for a trip
    public function search(Request $request) {
        $keyword = $request['keyword'];

        $matchingTrips = Trip::OrderBy('created_at', 'desc')->where('description', 'LIKE', '%' . $keyword . '%')
            ->orWhere('from', 'LIKE', '%' . $keyword . '%')
            ->orWhere('to', 'LIKE', '%' . $keyword . '%')->get();

        if(count($matchingTrips) > 0) {
            foreach($matchingTrips as $trip) {
                $trip['username'] = $trip->user->name;
                $trip['avatar'] = asset($trip->user->avatar);
                $trip['user_rating'] = round(DB::table('rates')->where('rated_user_id', $trip->user->id)->avg('rating'), 1);
            }
            return response()->json(['success' => true, 'data' => $matchingTrips]);
        } else {
            return response()->json(['message' => 'No trips found.']);
        }
    }

}

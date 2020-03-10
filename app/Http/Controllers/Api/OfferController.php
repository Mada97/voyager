<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Offer;

class OfferController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'trip_id' => ['required', 'numeric'],
                'offer_price' => ['required', 'numeric'],
                'number_of_seats_needed' => ['required', 'integer', 'min:1']
            ]
        );

        if($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
        }

        // Checking if the user already made an offer on this trip.
        $previousOffer = DB::table('offers')->where([['user_id', '=', Auth::User()->id], ['trip_id', '=', $request['trip_id']]])->count();
        if($previousOffer) {
            return response()->json(['success' => false, 'message' => 'You already made an offer on this trip.']);
        }

        $input = $request->all();
        $input['user_id'] = Auth::User()->id;
        $offer = Offer::create($input);

        return response()->json(['success' => true, 'data' => $offer], 201);
    }

    public function update(Offer $offer, Request $request)
    {
        if ($offer->user_id != Auth::guard('api')->id()) {
            return response()->json(['status' => 'Forbidden'], 403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'trip_id' => ['required', 'numeric'],
                'offer_price' => ['required', 'numeric'],
                'number_of_seats_needed' => ['required', 'integer', 'min:1']
            ]
        );

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
        }

        $input = $request->all();
        $input['user_id'] = Auth::User()->id;
        $offer->update($input);

        return response()->json(['success' => true, 'data' => $offer]);
    }

    public function destroy(Offer $offer)
    {
        if ($offer->user_id != Auth::guard('api')->id()) {
            return response()->json(['status' => 'Forbidden'], 403);
        }

        $offer->delete();
        return response()->json(['success' => 'true', 'message' => 'Offer removed successfully.', 'data' => $offer], 200);
    }
}

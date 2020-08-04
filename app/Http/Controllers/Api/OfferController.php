<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewOffer;
use App\Notifications\OfferAccepted;
use App\Notifications\OfferDeclined;
use App\User;
use App\Offer;
use App\Trip;

class OfferController extends Controller
{
    // Show offers for the logged in user
    public function show() {
        $user = Auth::user();
        $offers = Offer::where('user_id', Auth::User()->id)->orderBy('created_at', 'desc')->get();
        foreach($offers as $offer) {
            $trip = $offer->trip;
            $trip['username'] = $offer->trip->user->name;
            $offer['trip'] = $trip;
        }
        return response()->json(['success' => true, 'offers' => $offers]);
    }

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

        // A user can't make an offer on his/her own trip.
        $trip = Trip::find($request['trip_id']);
        if(Auth::user()->id == $trip->user->id) {
            return response()->json(['success' => false, 'message' => 'you can\'t make an offer on your own trip.']);
        }

        // Checking if the user already made an offer on this trip.
        $previousOffer = DB::table('offers')->where([['user_id', '=', Auth::User()->id], ['trip_id', '=', $request['trip_id']]])->count();
        if($previousOffer) {
            return response()->json(['success' => false, 'message' => 'You already made an offer on this trip.']);
        }

        // Checking if there are sufficient number of empty seats on the trip
        if($trip->number_of_empty_seats < $request['number_of_seats_needed']) {
            return response()->json(['success' => false, 'message' => 'Not enough empty seats on this trip.']);
        }

        $input = $request->all();
        $input['user_id'] = Auth::User()->id;
        $offer = Offer::create($input);

        $trip['number_of_empty_seats'] -= $request['number_of_seats_needed'];
        $trip->update();

        // sending notifications to the user that a new offer was made on his trip.
        $notifiableUser = $offer->trip->user;
        $notifiableUser->notify(new NewOffer($offer->owner, $trip));

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
        $offer->trip['number_of_empty_seats'] += $offer['number_of_seats_needed'];
        $offer->trip->update();

        return response()->json(['success' => 'true', 'message' => 'Offer removed successfully.', 'data' => $offer], 200);
    }

    // accept or decline an offer
    public function respondToOffer(Offer $offer, Request $request)
    {
        if($offer->trip->user->id != Auth::user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to respond to this offer.'
            ], 401);
        }

        if($offer->offer_status == 1 OR $offer->offer_status == 2) {
            return response()->json([
                'success' => false,
                'message' => 'You already responded to this offer.'
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'offer_status' => ['required', 'integer', 'min:1', 'max:2']
            ]
        );

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()]);
        }

        $offer->offer_status = intval($request->offer_status);
        $offer->update();

        $user = $offer->owner;
        if($offer->offer_status == 1) {
            $user->notify(new OfferAccepted($offer->trip->user, $offer->trip));
            return response()->json(['success' => 'true', 'message' => 'You accepted this offer.']);
        }

        if($offer->offer_status == 2) {
            $user->notify(new OfferDeclined($offer->trip->user, $offer->trip));
            return response()->json(['success' => 'true', 'message' => 'You declined this offer.']);
        }
    }
}

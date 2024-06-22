<?php

namespace App\Http\Controllers;

use App\Models\Stars;
use Illuminate\Http\Request;

class StarsController extends Controller
{
    public function rateBook(Request $request)
    {
        $request->validate([
            'book_id' => 'required|integer|exists:books,id',
            'avaliation' => 'required|integer|min:1|max:5',
        ]);

        $rate = Stars::create([
            'user_id' => auth()->id(),
            'book_id' => $request->book_id,
            'avaliation' => $request->avaliation,
        ]);

        return response()->json($rate, 201);
    }

    public function updateRate(Request $request, $id)
    {
        $request->validate([
            'avaliation' => 'required|integer|min:1|max:5',
        ]);

        $rate = Stars::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$rate) {
            return response()->json(['message' => 'Rate not found'], 404);
        }

        $rate->avaliation = $request->avaliation;
        $rate->save();

        return response()->json($rate, 200);
    }
}

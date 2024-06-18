<?php

namespace App\Http\Controllers;

use App\Models\WishList;
use Illuminate\Http\Request;

class WishListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;

        $query = WishList::query();

        $query->where('user_id', $userId)
            ->with('book')
            ->paginate(10);

        return response()->json($query);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => 'required|exists:books,id'
        ]);

        $user = auth()->user();
        $userId = $user->id;

        $wish = WishList::create([
            'user_id' => $userId,
            'book_id' => $data['book_id']
        ]);

        return response()->json($wish);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $wish = WishList::findOrFail($id);

        return response()->json($wish);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WishList $wishList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WishList $wishList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $wish = WishList::findOrFail($id);

        $wish->delete();

        return response()->json(200);
    }
}

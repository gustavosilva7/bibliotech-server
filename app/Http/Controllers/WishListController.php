<?php

namespace App\Http\Controllers;

use App\Models\Books;
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

        $query = Books::query()
            ->join('wish_lists', 'books.id', '=', 'wish_lists.book_id')
            ->where('wish_lists.user_id', $userId)
            ->select('books.*')
            ->get();

        return response()->json($query);
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
        $wish = WishList::find($id);

        return response()->json($wish);
    }

    public function hasInWishList(int $id)
    {
        $user = auth()->user();
        $userId = $user->id;

        $wish = WishList::where('user_id', $userId)
            ->where('book_id', $id)
            ->first();

        if ($wish) {
            return response()->json(true);
        }

        return response()->json(false);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $wish = WishList::where('book_id', $id)
            ->where('user_id', auth()->user()->id)
            ->first();

        $wish->delete();

        return response()->json(200);
    }
}

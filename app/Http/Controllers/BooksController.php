<?php

namespace App\Http\Controllers;

use App\Models\Books;
use Illuminate\Http\Request;

class BooksController extends Controller
{
    public function index()
    {
        $books = Books::all();

        return response()->json(['message' => $books], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'publisher' => 'required|string',
            'year' => 'required|integer',
            'tag' => 'required|string',
            'quantity' => 'required|integer',
            'edition' => 'required|integer',
        ]);

        $book = Books::create([
            'title' => $request->title,
            'author' => $request->author,
            'publisher' => $request->publisher,
            'year' => $request->year,
            'tag' => $request->tag,
            'quantity' => $request->quantity,
            'edition' => $request->edition,
        ]);

        return response()->json(['message' => $book], 200);
    }

    public function show($id)
    {
        $book = Books::find($id);

        return response()->json(['message' => $book], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'string',
            'author' => 'string',
            'publisher' => 'string',
            'year' => 'integer',
            'tag' => 'string',
            'quantity' => 'integer',
            'edition' => 'integer',
        ]);

        $book = Books::find($id);

        $book->title = $request->title;
        $book->author = $request->author;
        $book->publisher = $request->publisher;
        $book->year = $request->year;
        $book->tag = $request->tag;
        $book->quantity = $request->quantity;
        $book->edition = $request->edition;

        $book->save();

        return response()->json(['message' => $book], 200);
    }

    public function destroy($id)
    {
        $book = Books::find($id);

        $book->delete();

        return response()->json(['message' => 'Book deleted'], 200);
    }
}

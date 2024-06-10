<?php

namespace App\Http\Controllers;

use App\Enums\StatusLendingEnum;
use App\Models\Books;
use App\Models\Lendings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BooksController extends Controller
{
    public function index()
    {
        $books = Books::all();

        return response()->json($books);
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

    public function getDataBooks()
    {
        $currentYear = Carbon::now()->year;

        $booksByMonth = Books::selectRaw('extract(month from created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $lendingsByMonth = Lendings::selectRaw('extract(month from created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $lateLendingsByMonth = Lendings::selectRaw('extract(month from updated_at) as month, COUNT(*) as count')
            ->whereYear('updated_at', $currentYear)
            ->whereColumn('updated_at', '>', 'return_date')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $data = [
            'books' => $this->normalizeMonthlyData($booksByMonth),
            'lendings' => $this->normalizeMonthlyData($lendingsByMonth),
            'late_lendings' => $this->normalizeMonthlyData($lateLendingsByMonth),
        ];

        return response()->json($data, 200);
    }

    private function normalizeMonthlyData($data)
    {
        $normalizedData = array_fill(1, 12, 0);
        foreach ($data as $month => $count) {
            $normalizedData[$month] = $count;
        }
        return array_values($normalizedData);
    }

    public function getBooksToday()
    {
        $books = Books::all();
        $lendings = Lendings::whereNot("status", StatusLendingEnum::Finished)->get();
        $lateLendings = Lendings::where("status", StatusLendingEnum::Delayed)->get();

        $data = [
            'books' => $books,
            'lendings' => $lendings,
            'late_lendings' => $lateLendings,
        ];

        return response()->json($data, 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\StatusLendingEnum;
use App\Models\Books;
use App\Models\Lendings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class BooksController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $perPage = $request->per_page ?? 20;

        $query = Books::query();

        if ($search) {
            $query = Books::where('title', 'ilike', "%$search%")
                ->orWhere('author', 'ilike', "%$search%")
                ->orWhere('publisher', 'ilike', "%$search%")
                ->orWhere('tag', 'ilike', "%$search%");
        }

        $books = $query->orderBy('id', 'asc')->paginate($perPage);

        return response()->json($books);
    }

    public function booksPrint()
    {
        $books = Books::all();

        return response()->json($books, 200);
    }

    public function fetchBooksActives()
    {
        $books = Books::where('active', true)->get();

        return response()->json($books, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'publisher' => 'required|string',
            'year' => 'required|integer',
            'tag' => 'required|integer',
            'quantity' => 'required|integer',
            'edition' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $uploadFolder = 'books';
            $image = $request->file('image');
            $image_uploaded_path = $image->store($uploadFolder, 'public');
            $uploadedImageResponse = array(
                "image_name" => basename($image_uploaded_path),
                "image_url" => Storage::disk('public')->url($image_uploaded_path),
                "mime" => $image->getClientMimeType()
            );

            $path = "books/" . $uploadedImageResponse['image_name'];
        }

        $data = [
            'title' => $request->title,
            'author' => $request->author,
            'publisher' => $request->publisher,
            'year' => $request->year,
            'tag' => $request->tag,
            'edition' => $request->edition,
            'image' => $path,
        ];

        collect()->times($request->quantity, function () use ($data) {
            $books = Books::create($data);
        });

        return response()->json(['message' => "Success"], 200);
    }

    public function show($id)
    {
        $book = Books::findOrFail($id)->load('stars');

        return response()->json($book, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'string',
            'author' => 'string',
            'publisher' => 'string',
            'year' => 'integer',
            'tag' => 'integer',
            'edition' => 'integer',
        ]);

        $book = Books::find($id);

        $book->title = $request->title;
        $book->author = $request->author;
        $book->publisher = $request->publisher;
        $book->year = $request->year;
        $book->tag = $request->tag;
        $book->edition = $request->edition;

        $book->save();

        return response()->json(['book' => $book], 200);
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

    public function addImage(Request $request)
    {
        $request->validate([
            'tag' => 'required|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $books = Books::where('tag', $request->tag);

        if ($request->hasFile('image')) {
            $uploadFolder = 'books';
            $image = $request->file('image');
            $image_uploaded_path = $image->store($uploadFolder, 'public');
            $uploadedImageResponse = array(
                "image_name" => basename($image_uploaded_path),
                "image_url" => Storage::disk('public')->url($image_uploaded_path),
                "mime" => $image->getClientMimeType()
            );

            $path = "books/" . $uploadedImageResponse['image_name'];
        }

        $books->image = $path;
        $books->save();

        return response()->json(['message' => 'Image uploaded successfully'], 200);
    }

    public function booksMoreLending()
    {
        $books = Books::query()
            ->join('lendings', 'books.id', '=', 'lendings.book_id')
            ->groupBy('books.id')
            ->orderByRaw('COUNT(lendings.id) DESC')
            ->select('books.*')
            ->paginate(10);

        return response()->json($books, 200);
    }

    public function booksMoreRating()
    {
        $books = Books::query()
            ->join('stars', 'books.id', '=', 'stars.book_id')
            ->groupBy('books.id')
            ->orderByRaw('AVG(stars.avaliation) DESC')
            ->select('books.*')
            ->paginate(10);

        return response()->json($books, 200);
    }

    private function normalizeMonthlyData($data)
    {
        $normalizedData = array_fill(1, 12, 0);
        foreach ($data as $month => $count) {
            $normalizedData[$month] = $count;
        }
        return array_values($normalizedData);
    }
}

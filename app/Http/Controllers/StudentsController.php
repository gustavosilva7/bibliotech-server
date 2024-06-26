<?php

namespace App\Http\Controllers;

use App\Enums\StatusLendingEnum;
use App\Models\Lendings;
use App\Models\StudentsProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentsController extends Controller
{
    public function index()
    {
        $students = StudentsProfile::with('user')->get();

        return response()->json($students);
    }
    public function fetchStudentsActives()
    {
        $students = StudentsProfile::with([
            'user' => function ($query) {
                $query->where('active', true);
            }
        ])->whereHas('user', function ($query) {
            $query->where('active', true);
        })->get();

        return response()->json($students, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'serie' => 'required|integer',
            'class' => 'required|integer',
        ]);

        $credentials = $request->only('email', 'password');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 2
        ]);

        $student = StudentsProfile::create([
            'user_id' => $user->id,
            'serie' => $request->serie,
            'class' => $request->class
        ]);

        if (auth()->attempt($credentials)) {
            return response()->json(['student' => $student], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function show($id)
    {
        $student = StudentsProfile::with('user')->find($id);

        return response()->json(['student' => $student], 200);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'serie' => 'nullable|integer',
            'class' => 'nullable|integer',
        ]);

        $user = User::find($id);
        $student = StudentsProfile::where('user_id', $id)->first();

        if ($request->name) {
            $user->name = $request->name;
        }
        if ($request->email) {
            $user->email = $request->email;
        }
        if ($request->serie) {
            $student->serie = $request->serie;
        }
        if ($request->class) {
            $student->class = $request->class;
        }

        $user->save();
        $student->save();

        $userUpdate = User::with(['studentProfile', 'role'])->find($id);

        return response()->json($userUpdate, 200);
    }

    public function readers(Request $request)
    {
        $request->validate([
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = StudentsProfile::with('user')
            ->withCount([
                'rankings' => function ($query) use ($startDate, $endDate) {
                    if ($startDate) {
                        $query->where('created_at', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query->where('created_at', '<=', $endDate);
                    }
                }
            ])
            ->whereHas('rankings', function ($query) use ($startDate, $endDate) {
                if ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $query->where('created_at', '<=', $endDate);
                }
            })
            ->orderBy('rankings_count', 'desc');

        $students = $query->paginate(10);

        return response()->json($students, 200);
    }

    public function booksRead()
    {
        $user = auth()->user();

        $books = DB::table('books')
            ->join('lendings', 'books.id', '=', 'lendings.book_id')
            ->where('lendings.user_id', $user->id)
            ->groupBy('books.id')
            ->orderBy(DB::raw('COUNT(lendings.id)'), 'desc')
            ->where('lendings.status', StatusLendingEnum::Finished)
            ->select('books.*')
            ->paginate(10);

        return response()->json($books, 200);
    }

    public function bookInLending()
    {
        $user = auth()->user();

        $book = DB::table('books')
            ->join('lendings', 'books.id', '=', 'lendings.book_id')
            ->where('lendings.user_id', $user->id)
            ->where('lendings.status', StatusLendingEnum::Pendent)
            ->select('books.*')
            ->first();

        return response()->json($book, 200);
    }

    public function checkUserReadBook(int $bookId)
    {
        $user = auth()->user();

        $check = Lendings::where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->where('status', StatusLendingEnum::Finished)
            ->get();

        if ($check->isEmpty()) {
            return response()->json(false);
        }

        return response()->json($check, 200);
    }
}

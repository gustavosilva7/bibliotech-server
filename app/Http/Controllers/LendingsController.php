<?php

namespace App\Http\Controllers;

use App\Enums\StatusLendingEnum;
use App\Models\Books;
use App\Models\Lendings;
use App\Models\Raking;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LendingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->search ?? null;
        $status = $request->status ?? null;

        $query = Lendings::with('book', 'user', 'user.studentProfile');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('book', function ($query) use ($search) {
                    $query->where('title', 'ilike', "%$search%");
                })
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'ilike', "%$search%");
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', [StatusLendingEnum::Pendent, StatusLendingEnum::Delayed]);
        }

        $lendings = $query->paginate($perPage);

        return response()->json($lendings, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $book = Books::findOrFail($request->book_id);
        $student = User::findOrFail($request->user_id);

        if ($student->role_id != 2) {
            return response()->json(['error' => "Usuário não é um estudante"], 400);
        }

        if (!$book->active) {
            return response()->json(['error' => "Livro indisponível"], 400);
        }

        if (!$student->active) {
            return response()->json(['error' => "Aluno indisponível"], 400);
        }

        $loan = Lendings::create([
            'book_id' => $book->id,
            'user_id' => $student->id,
            'return_date' => Carbon::now()->addDays(15),
            'status' => StatusLendingEnum::Pendent->value
        ]);

        if ($loan) {
            Raking::create([
                'user_id' => $student->id
            ]);
            Rating::create([
                'book_id' => $book->id
            ]);

            $book->update(['active' => false]);
            $student->update(['active' => false]);
        }

        return response()->json(['message' => 'Emprestimo realizado com sucesso'], 201);
    }

    public function checked($id)
    {
        $loan = Lendings::findOrFail($id);

        if (!$loan) {
            return response()->json(['message' => "Empréstimo não encontrado"], 400);
        }

        if (!$loan->status == StatusLendingEnum::Finished->value) {
            return response()->json(['message' => "Empréstimo já finalizado"], 400);
        }

        $loan->update(['status' => StatusLendingEnum::Finished->value]);

        $book = Books::findOrFail($loan->book_id);
        $student = User::findOrFail($loan->user_id);

        $book->update(['active' => true]);
        $student->update(['active' => true]);

        return response()->json(['message' => 'Empréstimo finalizado com sucesso'], 200);
    }
}

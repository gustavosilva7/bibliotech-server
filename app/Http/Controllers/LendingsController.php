<?php

namespace App\Http\Controllers;

use App\Enums\StatusLendingEnum;
use App\Models\Books;
use App\Models\Lendings;
use App\Models\Raking;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;

class LendingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lendings = Lendings::with('book', 'user')->get();

        return response()->json(['message' => $lendings], 200);
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
            'return_date' => now()->addDays(15),
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

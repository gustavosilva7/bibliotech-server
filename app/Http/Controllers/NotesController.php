<?php

namespace App\Http\Controllers;

use App\Models\Notes;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = Notes::query()
            ->where('created_by', auth()->id())
            ->paginate(10);

        return response()->json($notes);
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
        $request->validate([
            'content' => 'required|string',
        ]);

        $note = Notes::create([
            'content' => $request->content,
            'created_by' => auth()->id(),
        ]);

        return response()->json($note, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $latestNote = Notes::query()
            ->where('created_by', auth()->id())
            ->latest()
            ->first();

        return response()->json($latestNote);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Notes $notes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $note = Notes::find($id);
        $note->update($request->all());

        return response()->json($note, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notes $notes)
    {
        //
    }
}

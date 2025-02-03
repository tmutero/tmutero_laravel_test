<?php

namespace App\Http\Controllers;
use App\Models\Book;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class BookController extends Controller
{

    public function index()
    {
        return response()->json(Book::all(), 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:books',
            'description' => 'nullable',
        ]);

        $book = Book::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => auth()->id(),
        ]);

        return response()->json($book, 201);
    }


    public function show($id)
    {
        $book = Cache::remember("book_$id", 60, function () use ($id) {
            return Book::find($id);
        });

        return $book ? response()->json($book, 200) : response()->json(['message' => 'Book not found'], 404);

    }


    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        // Check if user is authorized to update
        if (auth()->id() !== $book->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|unique:books,title,' . $id,
            'description' => 'nullable',
        ]);

        $book->update($request->all());

        Cache::forget("book_$id");

        return response()->json($book, 200);
    }


    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        // Check if user is authorized to delete
        if (auth()->id() !== $book->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $book->delete();

        Cache::forget("book_$id");

        return response()->json(['message' => 'Book deleted'], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $contacts = Contact::query()
            ->with(['category', 'tags'])
            ->keyword($request->keyword)
            ->gender($request->gender)
            ->categoryFilter($request->category_id)
            ->date($request->date)
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->appends($request->query());

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact(
            'contacts',
            'categories',
            'tags'
        ));
    }

    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}

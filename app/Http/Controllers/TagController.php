<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    public function store(StoreTagRequest $request)
    {
        Tag::create([
            'name' => $request->name,
        ]);

        return redirect('/admin');
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}

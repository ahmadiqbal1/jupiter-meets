<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use Illuminate\Support\Facades\Cache;

class ContentController extends Controller
{
    /**
     * Manage site content.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = Content::get();

        return view('admin.content.index', [
            'page' => 'Content',
            'data' => $data,
        ]);
    }

    //return the content page
    public function edit($id)
    {
        $model = Content::find($id);

        return view('admin.content.edit', [
            'page' => 'Content',
            'model' => $model,
        ]);
    }

    //update content
    public function update(Request $request)
    {
        $model = Content::find($request->id);

        $request->validate([
            'value' => 'required',
        ]);

        $model->value = $request->value;

        if ($model->save()) {
            Cache::forget('content');
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }
}

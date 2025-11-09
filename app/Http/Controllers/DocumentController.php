<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Vish4395\LaravelFileViewer\LaravelFileViewer;
use App\Models\Document;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('DocumentList', ['request'=> Document::where('manager', '=', auth()->user()->employee->id)->where('read', 'U')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('DocumentForm');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $path = $request->file->storePubliclyAs('documents', $request->file->getClientOriginalName(), 'public');
            Document::create([
                'employee_id' => auth()->user()->employee->id,
                'manager' => auth()->user()->employee->manager,
                'subject' => $request->input('subject'),
                'file_name' => $request->file->getClientOriginalName(),
                'file_path' => $path,
                'file' => Storage::url($path),
                'read' => 'U'
            ]);
        }
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $document = Document::find($id);
        return LaravelFileViewer::show($document->file_name, $document->file_path, asset($document->file));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        Document::find($id)->update(['read' => 'R']);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
    }
}

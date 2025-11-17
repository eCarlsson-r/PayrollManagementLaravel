<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Vish4395\LaravelFileViewer\LaravelFileViewer;
use App\Models\Document;
use App\Models\Timecard;
use App\Models\Receipt;
use App\Notifications\DocumentUpload;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('DocumentList', ['documents'=> Document::where('manager', '=', auth()->user()->employee->id)->where('verified', 'U')->get()]);
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
            $document = Document::create([
                'employee_id' => auth()->user()->employee->id,
                'manager' => auth()->user()->employee->manager,
                'subject' => $request->input('subject'),
                'file_name' => $request->file->getClientOriginalName(),
                'file_path' => $path,
                'file' => Storage::url($path),
                'verified' => 'U'
            ]);

            auth()->user()->employee->manager->account->notify(new DocumentUpload($document));
        }
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        $document = Document::find($id);
        if ($document->subject == "Time Card") {
            Timecard::create([
                'document_id' => $id,
                'date' => $request->input('date'),
                'time_start' => $request->input('time_start'),
                'time_end' => $request->input('time_end')
            ]);
        } elseif ($document->subject == "Sales Receipt") {
            Receipt::create([
                'document_id' => $id,
                'date' => $request->input('date'),
                'amount' => $request->input('amount')
            ]);
        }
        $document->update(['verified' => 'V']);
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

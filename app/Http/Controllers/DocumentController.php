<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index() {
        $documents = DB::table('documents')
                    ->join('users', 'documents.user_id', '=', 'users.id')
                    ->select('documents.*', 'users.name as user_name')
                    ->get();
        return view('admin.documents.index', compact('documents'));
    }

    public function create() {
        $users = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('is_guest', false)
            ->get();
        return view('admin.documents.create', compact('users'));
    }

    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required',
            'type' => 'required',
            'file' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('documents', 'public');
            
            DB::table('documents')->insert([
                'user_id' => $request->user_id,
                'type' => $request->type,
                'file_path' => $path,
                'created_at' => now(),
            ]);
        }

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diunggah!');
    }

    public function destroy($id) {
        $doc = DB::table('documents')->where('id', $id)->first();
        Storage::disk('public')->delete($doc->file_path);
        DB::table('documents')->where('id', $id)->delete();
        return back()->with('success', 'Dokumen dihapus!');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use Inertia\Inertia;

class BukuController extends Controller
{
    public function index(Request $request){
        $query = Buku::query();

        if($request->has('search') && $request->search != ''){
            $search=$request->search;
            $query->where('judul', 'like', "%{$search}%")
                ->orWhere('penulis', 'like', "%{$search}%");
        }

        return Inertia::render('Buku/Index', [
            'bukus' => $query->latest()->get(),
            'filters' => $request->only('search'),
            'isAdmin' => auth()->check() ? auth()->user()->role() === 'admin' : false,
        ]);
    }

    public function store(Request $request){
        if(!Gate::allows('isadmin')){
            abort(403, 'Aksi tidak diizinkan. Hanya Admin yang dapat menambah data.');
        };

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        Buku::create($validated);
        return redirect()->route('buku.index')->with('succes', 'buku ditambahkan');
    }

    public function update(Request $request, Buku $buku)
    {
        if (!Gate::allows('is-admin')) {
            abort(403, 'Aksi tidak diizinkan. Hanya Admin yang dapat mengubah data.');
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Buku $buku)
    {

        if (!Gate::allows('is-admin')) {
            abort(403, 'Aksi tidak diizinkan. Hanya Admin yang dapat menghapus data.');
        }

        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus.');
    }
}

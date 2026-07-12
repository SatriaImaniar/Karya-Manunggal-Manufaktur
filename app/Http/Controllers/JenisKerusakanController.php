<?php

namespace App\Http\Controllers;

use App\Models\JenisKerusakan;
use App\Models\Machine;
use Illuminate\Http\Request;

/**
 * JenisKerusakanController
 *
 * Mengelola CRUD master data jenis kerusakan mesin.
 * Fitur:
 *   - Index  : tampilkan semua jenis kerusakan dalam tabel
 *   - Store  : simpan jenis kerusakan baru via modal
 *   - Update : edit jenis kerusakan via modal
 *   - Destroy: hapus jenis kerusakan
 */
class JenisKerusakanController extends Controller
{
    /**
     * Tampilkan daftar semua jenis kerusakan.
     */
    public function index()
    {
        $jenisKerusakanList = JenisKerusakan::with('machine')
            ->orderBy('nama_kerusakan')
            ->paginate(15);

        $machines = Machine::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.jenis_kerusakan.index', compact(
            'jenisKerusakanList',
            'machines'
        ));
    }

    /**
     * Simpan jenis kerusakan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kerusakan'        => 'required|string|max:255',
            'machine_id'            => 'nullable|exists:machines,id',
            'deskripsi'             => 'nullable|string|max:1000',
            'estimasi_downtime_jam' => 'nullable|numeric|min:0|max:9999.99',
        ]);

        JenisKerusakan::create($validated);

        return redirect()
            ->route('admin.jenis-kerusakan.index')
            ->with('success', 'Jenis kerusakan "' . $validated['nama_kerusakan'] . '" berhasil ditambahkan.');
    }

    /**
     * Update jenis kerusakan yang ada.
     */
    public function update(Request $request, JenisKerusakan $jenisKerusakan)
    {
        $validated = $request->validate([
            'nama_kerusakan'        => 'required|string|max:255',
            'machine_id'            => 'nullable|exists:machines,id',
            'deskripsi'             => 'nullable|string|max:1000',
            'estimasi_downtime_jam' => 'nullable|numeric|min:0|max:9999.99',
        ]);

        $jenisKerusakan->update($validated);

        return redirect()
            ->route('admin.jenis-kerusakan.index')
            ->with('success', 'Jenis kerusakan "' . $validated['nama_kerusakan'] . '" berhasil diperbarui.');
    }

    /**
     * Hapus jenis kerusakan.
     *
     * Jika masih digunakan di data historis maintenance,
     * field jenis_kerusakan_id di sana akan menjadi null (set null).
     */
    public function destroy(JenisKerusakan $jenisKerusakan)
    {
        $nama = $jenisKerusakan->nama_kerusakan;
        $jenisKerusakan->delete();

        return redirect()
            ->route('admin.jenis-kerusakan.index')
            ->with('success', 'Jenis kerusakan "' . $nama . '" berhasil dihapus.');
    }
}

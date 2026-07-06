<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

/**
 * MachineController
 *
 * CRUD controller untuk mengelola data master mesin manufaktur.
 * Hanya dapat diakses oleh Admin/SPV.
 */
class MachineController extends Controller
{
    /**
     * Tampilkan daftar semua mesin.
     */
    public function index()
    {
        $machines = Machine::withCount(['maintenanceHistories', 'maintenanceSchedules'])
            ->orderBy('code')
            ->paginate(10);

        return view('admin.machines.index', compact('machines'));
    }

    /**
     * Tampilkan form tambah mesin baru.
     */
    public function create()
    {
        return view('admin.machines.create');
    }

    /**
     * Simpan mesin baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:machines,code',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'operating_hours_per_day' => 'required|numeric|min:0.5|max:24',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        Machine::create($validated);

        return redirect()
            ->route('admin.machines.index')
            ->with('success', 'Mesin berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit mesin.
     */
    public function edit(Machine $machine)
    {
        return view('admin.machines.edit', compact('machine'));
    }

    /**
     * Update data mesin.
     */
    public function update(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:machines,code,' . $machine->id,
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'operating_hours_per_day' => 'required|numeric|min:0.5|max:24',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        $machine->update($validated);

        return redirect()
            ->route('admin.machines.index')
            ->with('success', 'Data mesin berhasil diperbarui.');
    }

    /**
     * Hapus mesin dari database.
     */
    public function destroy(Machine $machine)
    {
        $machine->delete();

        return redirect()
            ->route('admin.machines.index')
            ->with('success', 'Mesin berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function index()
  {
    $users = User::where('role', 'teknisi')
      ->orderBy('name')
      ->paginate(15);

    return view('admin.users.index', compact('users'));
  }

  public function create()
  {
    return view('admin.users.create');
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'email', 'max:255', 'unique:users,email'],
      'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    User::create([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'password' => $validated['password'],
      'role' => 'teknisi',
    ]);

    return redirect()
      ->route('admin.users.index')
      ->with('success', 'Teknisi berhasil ditambahkan.');
  }

  public function edit(User $user)
  {
    if ($user->role !== 'teknisi') {
      abort(404);
    }

    return view('admin.users.edit', compact('user'));
  }

  public function update(Request $request, User $user)
  {
    if ($user->role !== 'teknisi') {
      abort(404);
    }

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
      'password' => ['nullable', 'string', 'min:8', 'confirmed'],
    ]);

    $updateData = [
      'name' => $validated['name'],
      'email' => $validated['email'],
    ];

    if (!empty($validated['password'])) {
      $updateData['password'] = $validated['password'];
    }

    $user->update($updateData);

    return redirect()
      ->route('admin.users.index')
      ->with('success', 'Data teknisi berhasil diperbarui.');
  }

  public function destroy(User $user)
  {
    if ($user->role !== 'teknisi') {
      abort(404);
    }

    $user->delete();

    return redirect()
      ->route('admin.users.index')
      ->with('success', 'Teknisi berhasil dihapus.');
  }
}

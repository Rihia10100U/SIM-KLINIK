<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Manajemen User')]
class ManajemenUser extends Component
{
    use WithPagination;

    public string $title = 'Manajemen User';

    public string $cari = '';

    public bool $showModal = false;

    public ?int $editId = null;

    public string $nama = '';

    public string $email = '';

    public string $role = '';

    public string $password = '';

    public string $passwordKonfirmasi = '';

    protected function rules(): array
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editId)],
            'role' => 'required|in:'.implode(',', array_column(Role::cases(), 'value')),
        ];

        if (! $this->editId) {
            // Tambah user baru: password wajib
            $rules['password'] = 'required|string|min:8';
            $rules['passwordKonfirmasi'] = 'required|same:password';
        } elseif ($this->password !== '') {
            // Edit user: password opsional, divalidasi hanya kalau diisi
            $rules['password'] = 'string|min:8';
            $rules['passwordKonfirmasi'] = 'required|same:password';
        }

        return $rules;
    }

    protected array $messages = [
        'passwordKonfirmasi.same' => 'Konfirmasi password tidak cocok.',
    ];

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function bukaForm(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function tutupForm(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->editId = $user->id;
        $this->nama = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->password = '';
        $this->passwordKonfirmasi = '';

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data = $this->validate();

        if ($this->editId) {
            $user = User::findOrFail($this->editId);

            $user->name = $data['nama'];
            $user->email = $data['email'];
            $user->role = $data['role'];

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            session()->flash('sukses', 'Data user berhasil diperbarui.');
        } else {
            User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
            ]);

            session()->flash('sukses', 'User baru berhasil ditambahkan.');
        }

        $this->tutupForm();
    }

    public function hapus(int $id): void
    {
        if ($id === Auth::id()) {
            session()->flash('gagal', 'Kamu tidak bisa menghapus akun kamu sendiri.');

            return;
        }

        User::findOrFail($id)->delete();

        session()->flash('sukses', 'User berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'nama', 'email', 'password', 'passwordKonfirmasi']);
        $this->role = Role::Resepsionis->value;
        $this->resetErrorBag();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->cari, fn ($q) => $q
                ->where('name', 'like', "%{$this->cari}%")
                ->orWhere('email', 'like', "%{$this->cari}%"))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.manajemen-user', [
            'users' => $users,
            'roles' => Role::options(),
        ]);
    }
}

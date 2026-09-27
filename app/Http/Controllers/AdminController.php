<?php

namespace App\Http\Controllers;

use App\Core\Services\SettingService;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    use FiltersRequests;

    // ---- Users ----
    public function userIndex(Request $request)
    {
        $users = $this->tableQuery($request, User::with(['roles'])->where('organization_id', $request->user()->organization_id), ['name', 'email']);
        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    public function userStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8', 'role' => 'required|exists:roles,name',
            'central_kitchen_id' => 'nullable|exists:central_kitchens,id',
        ]);
        $user = User::create([
            'organization_id' => $request->user()->organization_id,
            'central_kitchen_id' => $data['central_kitchen_id'] ?? null,
            'name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'],
            'is_active' => true,
        ]);
        $user->assignRole($data['role']);

        return back()->with('success', 'Pengguna dibuat.');
    }

    public function userToggle(User $user)
    {
        abort_if($user->id === request()->user()->id, 422, 'Tidak dapat menonaktifkan akun sendiri.');
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status pengguna diperbarui.');
    }

    public function userRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|exists:roles,name']);
        $user->syncRoles([$request->role]);

        return back()->with('success', 'Peran diperbarui.');
    }

    // ---- Roles ----
    public function roleIndex()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('.', $p->name)[0] ?? 'other');

        return view('admin.roles', compact('roles', 'permissions'));
    }

    public function roleSync(Request $request, Role $role)
    {
        $request->validate(['permissions' => 'nullable|array', 'permissions.*' => 'exists:permissions,name']);
        abort_if(in_array($role->name, ['super-admin', 'admin']) && $request->user()->email !== 'admin@mbg.id' && ! $request->user()->hasRole('super-admin'), 403);
        $role->syncPermissions($request->get('permissions', []));

        return back()->with('success', 'Izin peran diperbarui.');
    }

    // ---- Audit log ----
    public function auditIndex(Request $request)
    {
        $query = AuditLog::with(['user'])->latest();
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('q')) {
            $query->where('model_type', 'like', '%'.$request->q.'%');
        }
        $logs = $query->paginate(25)->withQueryString();

        return view('admin.audit', compact('logs'));
    }

    // ---- Settings ----
    public function settingIndex(Request $request, SettingService $settings)
    {
        $all = $settings->getAll();

        return view('admin.settings', compact('all'));
    }

    public function settingStore(Request $request, SettingService $settings)
    {
        $request->validate(['key' => 'required|string|max:100', 'value' => 'required|string']);
        $settings->set($request->key, $request->value);

        return back()->with('success', 'Pengaturan tersimpan.');
    }

    // ---- Notifications ----
    public function notificationIndex(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function notificationRead(Request $request, string $id)
    {
        $request->user()->notifications()->whereKey($id)->first()?->markAsRead();

        return back();
    }

    public function notificationReadAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai dibaca.');
    }
}

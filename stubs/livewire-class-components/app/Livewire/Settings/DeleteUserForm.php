<?php

namespace App\Livewire\Settings;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();
        $user->socialAccounts()->delete();

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}

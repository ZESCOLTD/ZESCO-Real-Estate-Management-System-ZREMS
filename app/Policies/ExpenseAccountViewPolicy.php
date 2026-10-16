<?php

namespace App\Policies;

use App\Models\ExpenseAccountView;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpenseAccountViewPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, ExpenseAccountView $expenseAccountView): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ExpenseAccountView $expenseAccountView): bool
    {
    }

    public function delete(User $user, ExpenseAccountView $expenseAccountView): bool
    {
    }

    public function restore(User $user, ExpenseAccountView $expenseAccountView): bool
    {
    }

    public function forceDelete(User $user, ExpenseAccountView $expenseAccountView): bool
    {
    }
}

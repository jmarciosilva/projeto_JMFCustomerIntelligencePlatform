<?php

namespace App\Http\Controllers\Admin;

use App\Application\Auth\Actions\LogoutAdminAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LogoutAdminAction $action): RedirectResponse
    {
        $action->handle();

        return redirect()->route('admin.login');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        if (!Auth::check()) {
            abort(403);
        }

        $user = Auth::user();

        $tab = strtolower((string) request()->query('tab', 'all'));
        if (!in_array($tab, ['all', 'unread', 'action'], true)) {
            $tab = 'all';
        }

        $notifications = collect();
        $unreadCount = 0;
        $actionCount = 0;

        if (Schema::hasTable('notifications')) {
            $baseQuery = DatabaseNotification::query()
                ->where('notifiable_type', get_class($user))
                ->where('notifiable_id', $user->id);

            $unreadCount = (clone $baseQuery)->whereNull('read_at')->count();

            // Needs action count: unread or containing actionable meta/url
            $actionCount = (clone $baseQuery)->where(function ($q) {
                $q->whereNull('read_at')
                  ->where(function ($inner) {
                      $inner->whereNotNull('data->url')
                            ->orWhereNotNull('data->meta');
                  });
            })->count();

            $query = (clone $baseQuery)->orderByDesc('created_at');

            if ($tab === 'unread') {
                $query->whereNull('read_at');
            } elseif ($tab === 'action') {
                $query->where(function ($q) {
                    $q->whereNotNull('data->url')
                      ->orWhereNotNull('data->meta');
                });
            }

            $notifications = $query->paginate(20)->withQueryString();
        }

        $layout = 'layouts.student_dashboard';
        if ($user->role === 'admin') {
            $layout = 'layouts.admin';
        } elseif ($user->role === 'landlord') {
            $layout = 'layouts.landlord';
        }

        return view('notifications.index', compact('notifications', 'unreadCount', 'actionCount', 'tab', 'layout'));
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        if (!Auth::check()) {
            abort(403);
        }
        if (!Schema::hasTable('notifications')) {
            return back();
        }

        $user = Auth::user();

        $notification = DatabaseNotification::query()
            ->where('id', $id)
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        $stay = $request->boolean('stay');
        $panel = (string) $request->input('panel');
        if ($stay && ($user->role ?? null) === 'student' && $panel === 'notifications') {
            return redirect()->route('notifications.index');
        }

        $redirectUrl = data_get($notification->data, 'url');
        if (is_string($redirectUrl) && $redirectUrl !== '') {
            return redirect($redirectUrl);
        }

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        if (!Auth::check()) {
            abort(403);
        }
        if (!Schema::hasTable('notifications')) {
            return back();
        }

        $user = Auth::user();
        DatabaseNotification::query()
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $stay = request()->boolean('stay');
        $panel = (string) request()->input('panel');
        if ($stay && ($user->role ?? null) === 'student' && $panel === 'notifications') {
            return redirect()->route('notifications.index')
                ->with('success', 'All notifications marked as read.');
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}

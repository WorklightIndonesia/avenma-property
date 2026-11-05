<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    private const EVENTS = ['created', 'updated', 'deleted', 'login', 'logout', 'login_failed'];

    public function index(Request $request): View
    {
        $event = (string) $request->query('event');

        $logs = AuditLog::query()
            ->with('user')
            ->when(in_array($event, self::EVENTS, true), fn ($query) => $query->where('event', $event))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'event' => $event,
            'events' => self::EVENTS,
        ]);
    }
}

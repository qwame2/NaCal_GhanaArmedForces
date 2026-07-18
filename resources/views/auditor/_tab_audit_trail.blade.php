@forelse($systemLogs as $log)
    <tr class="log-row">
        <td style="font-weight: 700; color: var(--text-muted); font-size: 0.78rem; white-space: nowrap;">
            {{ $log->created_at->format('d/m/Y H:i:s') }}
        </td>
        <td style="font-weight: 800;">
            @php
                $roleDisplay = 'N/A';
                if ($log->user) {
                    if ($log->user->role === 'Main Admin') {
                        $roleDisplay = 'Head of Admin(Authorizer)';
                    } elseif ($log->user->role === 'Head of Stores') {
                        $roleDisplay = 'Head of Stores';
                    } elseif ($log->user->is_admin) {
                        $roleDisplay = 'Head of Stores';
                    } elseif ($log->user->role === 'Department Head') {
                        if ($log->user->department === 'Human Resource Management Department') {
                            $roleDisplay = 'Dept Head(HR)';
                        } elseif ($log->user->department === 'Welfare Department') {
                            $roleDisplay = 'Head of Welfare';
                        } else {
                            $roleDisplay = 'Dept Head(' . $log->user->department . ')';
                        }
                    } elseif ($log->user->role === 'Officer') {
                        $roleDisplay = 'Store Officer';
                    } else {
                        $roleDisplay = $log->user->role;
                    }
                    if ($log->user->rank) {
                        $roleDisplay .= ' (' . $log->user->rank . ')';
                    }
                } else {
                    $roleDisplay = 'System Automated';
                }
            @endphp
            {{ $log->user ? $log->user->name : 'System Automated' }}
            <div style="font-size: 0.75rem; color: var(--audit-primary); font-weight: 800; margin-top: 2px;">{{ $roleDisplay }}</div>
            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; margin-top: 1px;">{{ $log->user ? '@' . $log->user->username : '' }}</div>
        </td>
        <td>
            <span class="badge-event">{{ $log->event_type }}</span>
        </td>
        <td style="font-weight: 700; font-family: monospace; color: var(--audit-primary);">
            {{ $log->action }}
        </td>
        <td style="max-width: 320px; line-height: 1.4; color: var(--text-main); font-weight: 500;">
            {{ $log->friendly_description }}
        </td>
        <td>
            @php
                $sevClass = 'info';
                if (in_array(strtolower($log->severity), ['danger', 'critical'])) {
                    $sevClass = 'danger';
                } elseif (strtolower($log->severity) === 'warning') {
                    $sevClass = 'warning';
                } elseif (strtolower($log->severity) === 'success') {
                    $sevClass = 'success';
                }
            @endphp
            <span class="log-badge {{ $sevClass }}">{{ $log->severity }}</span>
        </td>
        <td style="font-family: monospace; color: var(--text-muted); font-size: 0.78rem;">
            {{ $log->ip_address ?: '-' }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
            <p style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">No system log events archived.</p>
            <p style="font-size: 0.8rem;">Try clearing your filters or adjusting your date range.</p>
        </td>
    </tr>
@endforelse

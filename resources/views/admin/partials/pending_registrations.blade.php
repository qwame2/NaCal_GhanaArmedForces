@if($pendingUsers->count() === 0)
    <div class="reg-empty-state">
        <div class="reg-empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h3>No Pending Requests</h3>
        <p>All registration requests have been processed. New submissions will appear here.</p>
    </div>
@else
    <div class="reg-list">
        @foreach($pendingUsers as $req)
        <div class="reg-card" id="reg-card-{{ $req->id }}">
            {{-- Avatar + Identity --}}
            <div class="reg-identity">
                <div class="reg-avatar">
                    {{ strtoupper(substr($req->name, 0, 1)) }}
                </div>
                <div>
                    <div class="reg-name">{{ $req->name }}</div>
                    <div class="reg-username">@ {{ $req->username }}</div>
                    <div class="reg-time">Submitted {{ $req->created_at->diffForHumans() }}</div>
                </div>
            </div>

            {{-- Detail Pills --}}
            <div class="reg-details">
                <div class="reg-pill role">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    {{ $req->role === 'Main Admin' ? 'Head of Admin' : $req->role }}
                </div>
                @if($req->department)
                <div class="reg-pill dept">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                    {{ $req->department }}
                </div>
                @endif
                @if($req->rank)
                <div class="reg-pill rank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                    {{ $req->rank }}
                </div>
                @endif
                @if($req->sponsor)
                <div class="reg-pill sponsor" style="background: #f5f3ff; color: #6d28d9; border: 1px solid rgba(109, 40, 217, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Sponsor: {{ $req->sponsor->name }}
                </div>
                @endif
                @if($req->service_number)
                <div class="reg-pill service-number" style="background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
                    Service: {{ $req->service_number }}
                </div>
                @endif
                @if($req->phone)
                <div class="reg-pill phone" style="background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    {{ $req->phone }}
                </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="reg-actions">
                <form action="{{ route('admin.users.approve_registration', $req->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="reg-btn approve" title="Approve registration">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Approve
                    </button>
                </form>

                <form action="{{ route('admin.users.reject_registration', $req->id) }}" method="POST" style="display:inline;" class="decline-form">
                    @csrf
                    <button type="button" class="reg-btn decline" title="Decline registration" onclick="confirmDecline(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Decline
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@endif

@extends('layouts.admin')

@section('content')
<div class="view-header">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 2rem;">
        <div>
            <div class="title-group">
                <div class="title-capsule">
                    <div class="capsule-prefix">
                        <i data-lucide="lock"></i>
                    </div>
                    <h2>Access Control Matrix</h2>
                    <span class="capsule-tag">Security Protocol</span>
                </div>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">Managing granular operational permissions for strategic personnel.</p>
            </div>
        </div>
        
        <div style="flex: 1; max-width: 450px;">
            <div style="position: relative;">
                <i data-lucide="search" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); width: 20px; color: var(--primary); opacity: 0.6;"></i>
                <input type="text" id="personnelSearch" placeholder="Search personnel by name or username..." 
                    style="width: 100%; padding: 1.15rem 1.5rem 1.15rem 3.5rem; border-radius: 20px; border: 2px solid var(--border-color); background: white; color: var(--text-main); font-weight: 700; outline: none; transition: all 0.3s; font-size: 1rem;"
                    oninput="filterPersonnel()">
            </div>
        </div>
    </div>
</div>

<div class="registry-vault" style="background: transparent; box-shadow: none;">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 2rem;">
        @foreach($users as $user)
        <div class="perm-card" data-user-id="{{ $user->id }}" style="background: white; border-radius: 30px; padding: 2rem; border: 1px solid rgba(0,0,0,0.02); box-shadow: 0 10px 40px rgba(0,0,0,0.02); transition: all 0.3s;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 50px; height: 50px; border-radius: 14px; overflow: hidden; border: 2px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div>
                        <h4 style="font-weight: 900; color: #0f172a; font-size: 1.05rem; margin: 0;">{{ $user->name }}</h4>
                        <span style="font-size: 0.75rem; color: var(--primary); font-weight: 800; font-family: 'JetBrains Mono', monospace;">@ {{ $user->username }}</span>
                    </div>
                </div>
                <div style="background: {{ $user->is_active ? '#ecfdf5' : '#fef2f2' }}; color: {{ $user->is_active ? '#10b981' : '#ef4444' }}; padding: 6px 12px; border-radius: 10px; font-size: 0.65rem; font-weight: 900; letter-spacing: 0.05em; border: 1px solid {{ $user->is_active ? '#d1fae5' : '#fecdd3' }};">
                    {{ $user->is_active ? 'ACTIVE' : 'SUSPENDED' }}
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- Toggle 1: Inventory Item Entry -->
                <div style="background: #f8fafc; padding: 1.25rem; border-radius: 20px; border: 1px solid #edf2f7; transition: all 0.3s;" class="perm-row">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Inventory Item Entry</span>
                        <label class="switch">
                            <input type="checkbox" onchange="togglePermission(this, 'can_add_inventory')" {{ $user->can_add_inventory ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <p style="font-size: 0.8rem; color: #94a3b8; font-weight: 600; line-height: 1.4;">Allow personnel to enter newly received items into the strategic system.</p>
                </div>

                <!-- Toggle 2: Logistics Operations -->
                <div style="background: #f8fafc; padding: 1.25rem; border-radius: 20px; border: 1px solid #edf2f7; transition: all 0.3s;" class="perm-row">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Logistics Operations</span>
                        <label class="switch">
                            <input type="checkbox" onchange="togglePermission(this, 'can_operate_logistics')" {{ $user->can_operate_logistics ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <p style="font-size: 0.8rem; color: #94a3b8; font-weight: 600; line-height: 1.4;">Permissions for issuing items and recording returns in the category.</p>
                </div>

                <!-- Toggle 3: Report Generation -->
                <div style="background: #f8fafc; padding: 1.25rem; border-radius: 20px; border: 1px solid #edf2f7; transition: all 0.3s;" class="perm-row">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Report Generation</span>
                        <label class="switch">
                            <input type="checkbox" onchange="togglePermission(this, 'can_generate_reports')" {{ $user->can_generate_reports ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <p style="font-size: 0.8rem; color: #94a3b8; font-weight: 600; line-height: 1.4;">Access to data analytics and export modules for oversight reporting.</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
    .perm-card:hover { transform: translateY(-5px); box-shadow: 0 20px 60px rgba(0,0,0,0.06); }
    
    /* The switch - the box around the slider */
    .switch {
      position: relative;
      display: inline-block;
      width: 36px;
      height: 20px;
    }

    /* Hide default HTML checkbox */
    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    /* The slider */
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #cbd5e1;
      -webkit-transition: .4s;
      transition: .4s;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 14px;
      width: 14px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      -webkit-transition: .4s;
      transition: .4s;
    }

    input:checked + .slider {
      background-color: var(--primary);
    }

    input:focus + .slider {
      box-shadow: 0 0 1px var(--primary);
    }

    input:checked + .slider:before {
      -webkit-transform: translateX(16px);
      -ms-transform: translateX(16px);
      transform: translateX(16px);
    }

    /* Rounded sliders */
    .slider.round {
      border-radius: 34px;
    }

    .slider.round:before {
      border-radius: 50%;
    }

    .saving { opacity: 0.5; pointer-events: none; }
</style>

<script>
    function filterPersonnel() {
        const term = document.getElementById('personnelSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.perm-card');
        
        cards.forEach(card => {
            const name = card.querySelector('h4').textContent.toLowerCase();
            const username = card.querySelector('span').textContent.toLowerCase();
            
            if (name.includes(term) || username.includes(term)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function togglePermission(checkbox, permission) {
        const card = checkbox.closest('.perm-card');
        const userId = card.getAttribute('data-user-id');
        const value = checkbox.checked ? 1 : 0;
        const row = checkbox.closest('.perm-row');

        // Visual feedback
        row.style.opacity = '0.5';
        
        fetch('{{ route("admin.permissions.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                permission: permission,
                value: value
            })
        })
        .then(response => response.json())
        .then(data => {
            row.style.opacity = '1';
            if (data.success) {
                // Could show a toast here
                console.log('Permission updated successfully');
            } else {
                checkbox.checked = !checkbox.checked; // Revert
                alert('Failed to update permission: ' + data.message);
            }
        })
        .catch(error => {
            row.style.opacity = '1';
            checkbox.checked = !checkbox.checked; // Revert
            console.error('Error:', error);
            alert('A system error occurred.');
        });
    }
</script>
@endsection

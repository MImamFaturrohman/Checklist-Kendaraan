@php
    $isAdmin      = $isAdmin      ?? false;
    $isSuperAdmin = $isSuperAdmin ?? false;
    $isManager    = $isManager    ?? false;
@endphp
@if ($isAdmin || $isSuperAdmin)
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
@elseif ($isManager)
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
@else
    <i class="bi bi-person-check-fill" aria-hidden="true"></i>
@endif

@if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <rect width="24" height="24" rx="6" fill="#10b981"/>
            <path d="M17 9L11 15L8 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        {{ session('success') }}

        <button type="button" data-bs-dismiss="alert" class="close">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                <path opacity="0.6" d="M11 1.00004L1 11M0.999958 1L10.9999 11" stroke="white" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
@endif

@if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <rect width="24" height="24" rx="6" fill="#ef4444"/>
            <path d="M15 9L9 15M9 9L15 15" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        {{ session('error') }}

        <button type="button" data-bs-dismiss="alert" class="close">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                <path opacity="0.6" d="M11 1.00004L1 11M0.999958 1L10.9999 11" stroke="white" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
@endif

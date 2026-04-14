@php
    $status = $getRecord()?->status ?? '—';
@endphp

<div class="fi-fo-placeholder">

    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
            Status
        </span>
    </label>

    <div class="pt-2"> 
        <span class="bg-danger-100 text-danger-700 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-danger-900/50 dark:text-danger-400">
            {{ $status }}
        </span>
    </div>

</div>
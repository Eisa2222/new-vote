@props([
    'exportUrl',
    'templateUrl',
    'importUrl',
    'label',          // e.g. "players" / "clubs"
])

<div class="rounded-2xl bg-gradient-to-{{ app()->getLocale() === 'ar' ? 'l' : 'r' }} from-brand-50 to-accent-500/10 border border-brand-200 p-4 flex flex-col md:flex-row md:items-center gap-3 md:gap-4">
    <div class="flex items-center gap-3 flex-1 min-w-0">
        <div class="w-11 h-11 rounded-xl bg-brand-600 text-white flex items-center justify-center text-xl">📥</div>
        <div class="min-w-0">
            <div class="font-bold text-ink-900 text-sm">{{ __('Import / Export :label', ['label' => $label]) }}</div>
            <div class="text-xs text-ink-500">{{ __('CSV (UTF-8). Use the template to see the expected columns.') }}</div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $exportUrl }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-brand-300 bg-white text-brand-700 hover:bg-brand-50 px-3 py-2 text-sm font-semibold">
            ⬇ {{ __('Export CSV') }}
        </a>
        <a href="{{ $templateUrl }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-ink-200 bg-white text-ink-700 hover:bg-ink-50 px-3 py-2 text-sm font-semibold">
            📄 {{ __('Template') }}
        </a>
        <form method="post" action="{{ $importUrl }}" enctype="multipart/form-data" class="inline-flex items-center gap-2">
            @csrf
            <label class="inline-flex items-center gap-1.5 rounded-xl border border-brand-500 bg-brand-600 hover:bg-brand-700 text-white px-3 py-2 text-sm font-semibold cursor-pointer">
                📂 {{ __('Choose file') }}
                <input type="file" name="file" accept=".csv,text/csv" required
                       class="hidden"
                       onchange="this.closest('form').querySelector('[data-import-submit]').disabled = !this.files.length;
                                this.closest('form').querySelector('[data-file-name]').textContent = this.files[0]?.name || ''">
            </label>
            <span data-file-name class="text-xs text-ink-600 max-w-[160px] truncate"></span>
            <button type="submit" data-import-submit disabled
                    class="inline-flex items-center gap-1.5 rounded-xl bg-accent-500 hover:bg-accent-600 text-white px-3 py-2 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                ⬆ {{ __('Import') }}
            </button>
        </form>
    </div>
</div>

@if(session('import_errors'))
    <div class="mt-3 rounded-2xl bg-amber-50 border border-amber-300 p-4 text-sm">
        <div class="font-bold text-amber-900 mb-2">{{ __('Some rows were skipped:') }}</div>
        <ul class="list-disc list-inside space-y-0.5 text-amber-800">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Column toggle partial --}}
{{-- Usage: @include('partials.column-toggle', ['tableId' => 'myTable', 'columns' => ['Nama', 'Email', 'Aksi']]) --}}
{{-- Optional: 'storageKey' => 'unique-page-key' for localStorage persistence --}}
@php
    $tableId = $tableId ?? 'dataTable';
    $storageKey = $storageKey ?? 'col-toggle-' . request()->path();
    $columns = $columns ?? [];
    $columnOffset = $columnOffset ?? 0;
@endphp

@if(count($columns) > 0)
<div class="dropdown d-inline-block">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        <i class="bi bi-layout-three-columns"></i> Kolom
    </button>
    <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 220px; max-height: 350px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-2 px-2">
            <small class="fw-bold text-muted">Tampilkan Kolom</small>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="resetColumns_{{ Str::camel($tableId) }}()">
                <small>Reset</small>
            </button>
        </div>
        <div class="dropdown-divider mt-0"></div>
        @foreach($columns as $idx => $col)
        <label class="dropdown-item d-flex align-items-center gap-2 py-1 user-select-none" style="cursor: pointer; font-size: 0.875rem;">
            <input type="checkbox" class="form-check-input mt-0 col-toggle-checkbox" 
                   data-table="{{ $tableId }}" 
                   data-col-index="{{ $idx + $columnOffset }}"
                   checked>
            {{ $col }}
        </label>
        @endforeach
    </div>
</div>

<script>
(function() {
    var tableId = @json($tableId);
    var storageKey = @json($storageKey);
    var totalCols = {{ count($columns) }};

    function getTable() {
        return document.getElementById(tableId);
    }

    function getHiddenCols() {
        try {
            var stored = localStorage.getItem(storageKey);
            return stored ? JSON.parse(stored) : [];
        } catch(e) {
            return [];
        }
    }

    function saveHiddenCols(hidden) {
        try {
            localStorage.setItem(storageKey, JSON.stringify(hidden));
        } catch(e) {}
    }

    function toggleColumn(colIndex, show) {
        var table = getTable();
        if (!table) return;
        
        // Handle thead rows (may have colspan/rowspan)
        var theadRows = table.querySelectorAll('thead tr');
        if (theadRows.length === 1) {
            // Simple single-row header
            var ths = theadRows[0].querySelectorAll('th');
            if (ths[colIndex]) {
                ths[colIndex].style.display = show ? '' : 'none';
            }
        } else if (theadRows.length > 1) {
            // Multi-row header - only toggle first row
            var ths = theadRows[0].querySelectorAll('th');
            if (ths[colIndex]) {
                ths[colIndex].style.display = show ? '' : 'none';
                // If has colspan, also hide sub-columns in row 2
                var colspan = parseInt(ths[colIndex].getAttribute('colspan')) || 1;
                if (colspan > 1 && theadRows[1]) {
                    var offset = 0;
                    for (var i = 0; i < colIndex; i++) {
                        var c = parseInt(ths[i].getAttribute('colspan')) || 1;
                        var rs = parseInt(ths[i].getAttribute('rowspan')) || 1;
                        if (rs < 2) offset += c;
                    }
                    var subThs = theadRows[1].querySelectorAll('th');
                    for (var j = offset; j < offset + colspan && j < subThs.length; j++) {
                        subThs[j].style.display = show ? '' : 'none';
                    }
                }
            }
        }

        // Handle tbody rows
        var tbodyRows = table.querySelectorAll('tbody tr');
        tbodyRows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            // Skip rows with colspan (empty messages)
            if (cells.length <= 1) return;
            if (cells[colIndex]) {
                cells[colIndex].style.display = show ? '' : 'none';
            }
        });

        // Handle tfoot rows
        var tfootRows = table.querySelectorAll('tfoot tr');
        tfootRows.forEach(function(row) {
            var cells = row.querySelectorAll('td, th');
            if (cells.length <= 1) return;
            if (cells[colIndex]) {
                cells[colIndex].style.display = show ? '' : 'none';
            }
        });
    }

    function applyStoredPrefs() {
        var hidden = getHiddenCols();
        var checkboxes = document.querySelectorAll('.col-toggle-checkbox[data-table="' + tableId + '"]');
        
        hidden.forEach(function(colIndex) {
            toggleColumn(colIndex, false);
            checkboxes.forEach(function(cb) {
                if (parseInt(cb.dataset.colIndex) === colIndex) {
                    cb.checked = false;
                }
            });
        });
    }

    // Bind checkbox events
    document.addEventListener('DOMContentLoaded', function() {
        var checkboxes = document.querySelectorAll('.col-toggle-checkbox[data-table="' + tableId + '"]');
        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                var colIndex = parseInt(this.dataset.colIndex);
                var show = this.checked;
                toggleColumn(colIndex, show);

                var hidden = getHiddenCols();
                if (show) {
                    hidden = hidden.filter(function(i) { return i !== colIndex; });
                } else {
                    if (hidden.indexOf(colIndex) === -1) hidden.push(colIndex);
                }
                saveHiddenCols(hidden);
            });
        });

        applyStoredPrefs();
    });

    // Reset function - attach to window
    window['resetColumns_' + @json(Str::camel($tableId))] = function() {
        var checkboxes = document.querySelectorAll('.col-toggle-checkbox[data-table="' + tableId + '"]');
        checkboxes.forEach(function(cb) {
            cb.checked = true;
            var colIndex = parseInt(cb.dataset.colIndex);
            toggleColumn(colIndex, true);
        });
        saveHiddenCols([]);
    };
})();
</script>
@endif

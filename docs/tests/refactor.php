<?php
$filePath = 'd:\LPKD-APJI\SIPERA\resources\views\admin\submissions\monitoring.blade.php';
$content = file_get_contents($filePath);

$replacements = [
    [
        'target' => '                                    <select class="inline-assign-select {{ $s->marketing_id ? \'has-value\' : \'\' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="marketing"
                                            data-model="marketing"
                                            onchange="quickAssignMarketing(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($marketings as $mkt)
                                            <option value="{{ $mkt->id }}" {{ $s->marketing_id == $mkt->id ? \'selected\' : \'\' }}>{{ $mkt->name }}</option>
                                        @endforeach
                                    </select>',
        'replacement' => '                                    <select class="inline-assign-select lazy-select {{ $s->marketing_id ? \'has-value\' : \'\' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="marketing"
                                            data-model="marketing"
                                            data-selected="{{ $s->marketing_id }}"
                                            onchange="quickAssignMarketing(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->marketing_id)
                                            <option value="{{ $s->marketing_id }}" selected>{{ $marketings->firstWhere(\'id\', $s->marketing_id)?->name }}</option>
                                        @endif
                                    </select>',
    ]
];

$fields = [
    'petugas_editor1_id' => 'editor1',
    'petugas_author1_id' => 'author1',
    'petugas_editor2_id' => 'editor2',
    'petugas_reviewer1_id' => 'reviewer1',
    'petugas_reviewer2_id' => 'reviewer2',
    'petugas_editor3_id' => 'editor3',
    'petugas_author2_id' => 'author2',
    'petugas_production_id' => 'production',
    'petugas_validator_id' => 'validator',
];

foreach ($fields as $field => $type) {
    if ($type === 'validator') {
        $target = '                                    <select class="inline-assign-select {{ $s->' . $field . ' ? \'has-value\' : \'\' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="' . $type . '"
                                            data-model="pic"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->' . $field . ' == $pic->id ? \'selected\' : \'\' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>';
    } else {
        $target = '                                    <select class="inline-assign-select {{ $s->' . $field . ' ? \'has-value\' : \'\' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="' . $type . '"
                                            data-model="pic"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @foreach($pics as $pic)
                                            <option value="{{ $pic->id }}" {{ $s->' . $field . ' == $pic->id ? \'selected\' : \'\' }}>{{ $pic->name }}</option>
                                        @endforeach
                                    </select>';
    }
                                    
    $replacement = '                                    <select class="inline-assign-select lazy-select {{ $s->' . $field . ' ? \'has-value\' : \'\' }}" 
                                            data-submission="{{ $s->id }}" 
                                            data-type="' . $type . '"
                                            data-model="pic"
                                            data-selected="{{ $s->' . $field . ' }}"
                                            onchange="quickAssign(this)">
                                        <option value="">-- Pilih --</option>
                                        @if($s->' . $field . ')
                                            <option value="{{ $s->' . $field . ' }}" selected>{{ $pics->firstWhere(\'id\', $s->' . $field . ')?->name }}</option>
                                        @endif
                                    </select>';
    $replacements[] = ['target' => $target, 'replacement' => $replacement];
}

$count = 0;
foreach ($replacements as $r) {
    // Windows line ending support
    $target = str_replace("\n", "\r\n", $r['target']);
    $replacement = str_replace("\n", "\r\n", $r['replacement']);
    
    if (strpos($content, $target) !== false) {
        $content = str_replace($target, $replacement, $content);
        $count++;
    } else {
        // try to relax whitespace
        $targetRegex = '/' . preg_quote(trim($r['target']), '/') . '/s';
        $targetRegex = preg_replace('/\s+/', '\s+', $targetRegex);
        if (preg_match($targetRegex, $content)) {
            $content = preg_replace($targetRegex, $replacement, $content);
            $count++;
        } else {
            echo "Failed to match type: " . substr($r['target'], 0, 50) . "\n";
        }
    }
}

// Insert JS
$jsInjection = "
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listPics = @json(\$pics);
    const listMarketings = @json(\$marketings);

    // Event delegation for mouseenter to lazy-load options
    document.addEventListener('mouseover', function(e) {
        if(e.target && e.target.classList.contains('lazy-select')) {
            let select = e.target;
            if(!select.dataset.loaded) {
                let model = select.dataset.model;
                let selectedVal = select.dataset.selected;
                let optionsData = model === 'marketing' ? listMarketings : listPics;
                
                // Add all options if not already present
                optionsData.forEach(item => {
                    if (item.id != selectedVal) { // skip already rendered selected
                        let opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.name;
                        select.appendChild(opt);
                    }
                });
                select.dataset.loaded = 'true';
            }
        }
    });
});
</script>
";

$jsInjection = str_replace("\n", "\r\n", $jsInjection);

if (strpos($content, 'const listPics =') === false) {
    // Find last @endsection and insert before it
    $pos = strrpos($content, '@endsection');
    if ($pos !== false) {
        $content = substr_replace($content, $jsInjection . "\r\n@endsection", $pos, strlen('@endsection'));
    }
}

file_put_contents($filePath, $content);
echo "Replaced $count items. Done!\n";

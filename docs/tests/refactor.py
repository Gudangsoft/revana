import re

file_path = r'd:\LPKD-APJI\SIPERA\resources\views\admin\submissions\monitoring.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Pattern for marketing
content = re.sub(
    r'<select class=\"(.*?)\s*({{\s*\$s->marketing_id[^{}]*}})\"\s*(.*?)data-model=\"marketing\"(.*?)(onchange=\"[^\"]+\")>\s*<option value=\"\">-- Pilih --</option>\s*@foreach\(\$marketings as \$mkt\)\s*<option value=\"{{\s*\$mkt->id\s*}}\" {{\s*\$s->marketing_id == \$mkt->id \? \'selected\' : \'\'\s*}}>{{ \$mkt->name }}</option>\s*@endforeach\s*</select>',
    r'''<select class="\g<1> \g<2> lazy-select"\n                                            \g<3>data-model="marketing"\g<4>data-selected="{{ $s->marketing_id }}"\n                                            \g<5>>
                                        <option value="">-- Pilih --</option>
                                        @if($s->marketing_id)
                                            <option value="{{ $s->marketing_id }}" selected>{{ $marketings->firstWhere('id', $s->marketing_id)?->name }}</option>
                                        @endif
                                    </select>''',
    content, flags=re.DOTALL
)

def repl(m):
    cls1 = m.group(1).strip()
    cls2 = m.group(2).strip()
    s_field = m.group(3).strip() # e.g. $s->petugas_editor1_id
    mid_attrs = m.group(4)
    model_attr = m.group(5) # data-model="pic"
    mid_attrs2 = m.group(6)
    onchange = m.group(7)
    
    return f'''<select class="{cls1} {cls2} lazy-select"{mid_attrs}{model_attr}{mid_attrs2}data-selected="{{{{ {s_field} }}}}" {onchange}>
                                        <option value="">-- Pilih --</option>
                                        @if({s_field})
                                            <option value="{{{{ {s_field} }}}}" selected>{{{{ $pics->firstWhere('id', {s_field})?->name }}}}</option>
                                        @endif
                                    </select>'''

content = re.sub(
    r'<select class=\"(.*?)\s*({{\s*(\$s->[a-zA-Z0-9_]+)\s*\?.*?}})\"\s*(.*?)data-model=\"pic\"(.*?)(onchange=\"[^\"]+\")>\s*<option value=\"\">-- Pilih --</option>\s*@foreach\(\$pics as \$pic\)\s*<option value=\"{{\s*\$pic->id\s*}}\" {{\s*.*?\s*}}>{{ \$pic->name }}</option>\s*@endforeach\s*</select>',
    repl,
    content, flags=re.DOTALL
)

# Insert the lazy load scripts at the very end of content before @endsection, inside the Javascript section.
javascript_injection = r"""
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listPics = @json($pics);
    const listMarketings = @json($marketings);

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
                select.dataset.loaded = "true";
            }
        }
    });
});
</script>
"""

if 'const listPics =' not in content:
    content = content.replace("@endsection\n", javascript_injection + "\n@endsection\n", 1)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print('Done!')

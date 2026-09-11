@extends('layouts.editor')

@section('title', 'Туршиж үзэх — ' . $lesson->title)

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
<style>
    /* Prism's theme hardcodes its own font-family/line-height/tab-size on
       code[class*="language-"], which otherwise drifts from the textarea's
       font — the two layers stop lining up and the caret appears to float
       away from the character it's actually next to. Force both layers to
       share identical metrics so the transparent textarea's caret always
       sits on top of the highlighted character underneath it. */
    [data-editor-highlight],
    [data-editor-highlight] code[class*="language-"] {
        font-family: var(--font-mono, ui-monospace, monospace) !important;
        font-size: 0.875rem !important;
        line-height: 1.5rem !important;
        tab-size: 4 !important;
    }
</style>
@endpush

@section('content')
<div class="flex h-screen flex-col" data-editor data-mode="{{ $isCpp ? 'cpp' : 'web' }}" data-language="{{ $isCpp ? 'cpp' : 'markup' }}">
    {{-- Window chrome --}}
    <div class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-2.5 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center gap-3">
            <div class="hidden items-center gap-1.5 sm:flex">
                <span class="h-3 w-3 rounded-full bg-red-500/80"></span>
                <span class="h-3 w-3 rounded-full bg-amber-400/80"></span>
                <span class="h-3 w-3 rounded-full bg-emerald-500/80"></span>
            </div>
            <div class="hidden h-5 w-px bg-slate-200 dark:bg-slate-700 sm:block"></div>
            <a href="{{ route('lessons.show', $lesson) }}" title="Хичээл рүү буцах"
               class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.5 1.5 0 012.122 0L22.28 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </a>
            <button type="button" data-theme-toggle aria-label="Өнгө солих"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white">
                <svg class="h-4.5 w-4.5 dark:hidden" viewBox="0 0 24 24" fill="currentColor"><path d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                <svg class="hidden h-4.5 w-4.5 dark:block" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z" /></svg>
            </button>
            <span class="hidden truncate text-sm font-medium text-slate-500 dark:text-slate-400 sm:inline">{{ $lesson->title }}</span>
        </div>

        <button type="button" data-editor-run
                class="inline-flex items-center gap-1.5 rounded-lg bg-linear-to-r from-emerald-500 to-teal-500 px-5 py-2 text-sm font-semibold text-white shadow-[0_0_15px_rgba(16,185,129,0.35)] transition-all duration-300 hover:shadow-[0_0_25px_rgba(16,185,129,0.55)]">
            Run
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
        </button>
    </div>

    {{-- Fake file tab --}}
    <div class="flex items-center gap-1 border-b border-slate-200 bg-slate-50 px-3 pt-2 dark:border-slate-800 dark:bg-slate-950">
        <div class="flex items-center gap-2 rounded-t-lg border border-b-0 border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-600 dark:border-slate-800 dark:bg-[#1e1e2e] dark:text-slate-300">
            <span class="h-2 w-2 rounded-full {{ $isCpp ? 'bg-blue-400' : 'bg-orange-400' }}"></span>
            {{ $isCpp ? 'main.cpp' : 'index.html' }}
        </div>
    </div>

    <div class="grid flex-1 grid-cols-1 overflow-hidden md:grid-cols-2">
        {{-- Code editor with gutter + live Prism highlight overlay --}}
        <div class="flex overflow-hidden bg-[#1e1e2e]">
            <div data-editor-gutter class="select-none overflow-hidden whitespace-pre bg-black/20 px-3 py-4 text-right font-mono text-sm leading-6 tabular-nums text-slate-600"></div>
            <div class="relative min-w-0 flex-1">
                <pre data-editor-highlight class="pointer-events-none absolute inset-0 m-0 overflow-auto whitespace-pre p-4 font-mono text-sm leading-6"><code class="language-{{ $isCpp ? 'cpp' : 'markup' }}"></code></pre>
                <textarea data-editor-code spellcheck="false" autocapitalize="off" autocomplete="off" autocorrect="off"
                    lang="en" translate="no" data-gramm="false" data-gramm_editor="false" data-enable-grammarly="false" data-ms-editor="false"
                    class="absolute inset-0 h-full w-full resize-none overflow-auto whitespace-pre bg-transparent p-4 font-mono text-sm leading-6 text-transparent caret-white selection:bg-indigo-500/30 focus:outline-none">{{ $lesson->starter_code }}</textarea>
            </div>
        </div>

        {{-- Preview / console --}}
        <div class="flex h-full flex-col overflow-hidden border-t border-slate-200 bg-white dark:border-slate-800 md:border-t-0 md:border-l">
            @if ($isCpp)
                <div class="flex items-center justify-between border-b border-slate-800 bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <span>Console</span>
                    <span class="text-slate-500">Симуляц горим</span>
                </div>
                <pre data-editor-console class="flex-1 overflow-auto whitespace-pre-wrap bg-slate-950 p-4 font-mono text-sm text-emerald-400">Run товч дарж програмаа ажиллуулна уу...</pre>
            @else
                <iframe data-editor-frame class="h-full w-full" title="Preview" sandbox="allow-scripts allow-same-origin"></iframe>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-clike.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-c.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-cpp.min.js"></script>
<script>
    (function () {
        const root = document.querySelector('[data-editor]');
        if (!root) return;

        const mode = root.dataset.mode;
        const language = root.dataset.language;
        const codeEl = root.querySelector('[data-editor-code]');
        const highlightPre = root.querySelector('[data-editor-highlight]');
        const highlightCode = highlightPre.querySelector('code');
        const gutterEl = root.querySelector('[data-editor-gutter]');
        const runBtn = root.querySelector('[data-editor-run]');

        function updateGutter() {
            const lineCount = codeEl.value.split('\n').length;
            let lines = '';
            for (let i = 1; i <= lineCount; i++) lines += i + '\n';
            gutterEl.textContent = lines;
        }

        function highlight() {
            const grammar = (window.Prism && Prism.languages[language]) || null;
            highlightCode.innerHTML = grammar
                ? Prism.highlight(codeEl.value, grammar, language)
                : codeEl.value.replace(/&/g, '&amp;').replace(/</g, '&lt;');
            updateGutter();
        }

        function syncScroll() {
            highlightPre.scrollTop = codeEl.scrollTop;
            highlightPre.scrollLeft = codeEl.scrollLeft;
            gutterEl.scrollTop = codeEl.scrollTop;
        }

        codeEl.addEventListener('input', highlight);
        codeEl.addEventListener('scroll', syncScroll);
        codeEl.addEventListener('keydown', function (event) {
            if (event.key !== 'Tab') return;
            event.preventDefault();
            const start = codeEl.selectionStart;
            const end = codeEl.selectionEnd;
            codeEl.value = codeEl.value.slice(0, start) + '    ' + codeEl.value.slice(end);
            codeEl.selectionStart = codeEl.selectionEnd = start + 4;
            highlight();
        });

        highlight();

        function runWeb() {
            const frame = root.querySelector('[data-editor-frame]');
            if (frame) frame.srcdoc = codeEl.value;
        }

        function runCpp() {
            const consoleEl = root.querySelector('[data-editor-console]');
            if (!consoleEl) return;

            const code = codeEl.value;

            // Split on a top-level separator (respecting quotes/parens) — used
            // for both "a + b" expressions and "fn(a, b)" argument lists.
            function splitTopLevel(expr, sepChar) {
                const parts = [];
                let depth = 0;
                let inStr = false;
                let current = '';
                for (let i = 0; i < expr.length; i++) {
                    const ch = expr[i];
                    if (ch === '"') inStr = !inStr;
                    if (!inStr) {
                        if (ch === '(') depth++;
                        if (ch === ')') depth--;
                        if (ch === sepChar && depth === 0) {
                            parts.push(current);
                            current = '';
                            continue;
                        }
                    }
                    current += ch;
                }
                parts.push(current);
                return parts;
            }

            function unescapeStr(s) {
                return s.replace(/\\n/g, '\n').replace(/\\t/g, '\t');
            }

            // User-defined single-statement functions: TYPE name(params) { return expr; }
            const userFunctions = {};
            const funcRegex = /\b(?:int|double|float|string|bool|char|void)\s+([A-Za-z_]\w*)\s*\(([^)]*)\)\s*\{\s*return\s+([^;]+);\s*\}/g;
            let fm;
            while ((fm = funcRegex.exec(code))) {
                const params = fm[2].split(',').map((p) => p.trim().split(/\s+/).pop()).filter(Boolean);
                userFunctions[fm[1]] = { params, body: fm[3] };
            }

            // Pointers: TYPE* name = &target;
            const pointerTarget = {};
            const ptrDeclRegex = /\b(?:int|double|float|char|bool|string)\s*\*\s*([A-Za-z_]\w*)\s*=\s*&\s*([A-Za-z_]\w*)\s*;/g;
            let pm;
            while ((pm = ptrDeclRegex.exec(code))) {
                pointerTarget[pm[1]] = pm[2];
            }

            // Arrays: TYPE name[N] = {a, b, c};
            const arrays = {};
            const arrayDeclRegex = /\b(?:int|double|float|char|bool|string)\s+([A-Za-z_]\w*)\s*\[\s*\d*\s*\]\s*=\s*\{([^}]*)\}\s*;/g;
            let arm;
            while ((arm = arrayDeclRegex.exec(code))) {
                arrays[arm[1]] = splitTopLevel(arm[2], ',').map((v) => v.trim());
            }

            function findMatchingBrace(str, openIndex) {
                let depth = 0;
                for (let i = openIndex; i < str.length; i++) {
                    if (str[i] === '{') depth++;
                    else if (str[i] === '}') {
                        depth--;
                        if (depth === 0) return i;
                    }
                }
                return -1;
            }

            const variables = {};

            function evaluateExpression(expr, vars) {
                expr = expr.trim();
                if (expr === '') return '';
                if (expr === 'true') return '1';
                if (expr === 'false') return '0';

                const strMatch = expr.match(/^"([\s\S]*)"$/);
                if (strMatch) return unescapeStr(strMatch[1]);

                const charMatch = expr.match(/^'(.)'$/);
                if (charMatch) return charMatch[1];

                if (/^-?\d+\.?\d*f?$/.test(expr)) return expr.replace(/f$/, '');

                const derefMatch = expr.match(/^\*\s*([A-Za-z_]\w*)$/);
                if (derefMatch && pointerTarget[derefMatch[1]] && pointerTarget[derefMatch[1]] in vars) {
                    return vars[pointerTarget[derefMatch[1]]];
                }

                const indexMatch = expr.match(/^([A-Za-z_]\w*)\[(\d+)\]$/);
                if (indexMatch && arrays[indexMatch[1]]) {
                    const el = arrays[indexMatch[1]][parseInt(indexMatch[2], 10)];
                    return el === undefined ? null : evaluateExpression(el, vars);
                }

                const fnMatch = expr.match(/^([A-Za-z_]\w*)\((.*)\)$/);
                if (fnMatch) {
                    const args = splitTopLevel(fnMatch[2], ',').filter((a) => a.trim() !== '').map((a) => evaluateExpression(a, vars));
                    const nums = args.map((a) => parseFloat(a));
                    switch (fnMatch[1]) {
                        case 'max': return nums.some(isNaN) ? null : String(Math.max(...nums));
                        case 'min': return nums.some(isNaN) ? null : String(Math.min(...nums));
                        case 'sqrt': return isNaN(nums[0]) ? null : String(Math.sqrt(nums[0]));
                        case 'pow': return nums.some(isNaN) ? null : String(Math.pow(nums[0], nums[1]));
                        case 'abs': return isNaN(nums[0]) ? null : String(Math.abs(nums[0]));
                    }
                    if (userFunctions[fnMatch[1]]) {
                        const fn = userFunctions[fnMatch[1]];
                        const localVars = Object.assign({}, vars);
                        fn.params.forEach((p, i) => { localVars[p] = args[i] ?? ''; });
                        return evaluateExpression(fn.body, localVars);
                    }
                    return null;
                }

                const plusParts = splitTopLevel(expr, '+');
                if (plusParts.length > 1) {
                    const values = plusParts.map((p) => evaluateExpression(p, vars));
                    if (values.some((v) => v === null)) return null;
                    if (values.every((v) => /^-?\d+\.?\d*$/.test(v))) {
                        return String(values.reduce((a, b) => a + parseFloat(b), 0));
                    }
                    return values.join('');
                }

                if (expr in vars) return vars[expr];
                return null;
            }

            // Variable declarations, including comma-separated ("int a = 1, b = 2;")
            // and references ("string &meal = food;").
            const aliasOf = {};
            const declStmtRegex = /\b(?:int|double|float|char|bool|string|auto)\s*&?\s+([^;{}]+);/g;
            let dsm;
            while ((dsm = declStmtRegex.exec(code))) {
                for (const part of splitTopLevel(dsm[1], ',')) {
                    const eq = part.indexOf('=');
                    if (eq === -1) continue;
                    let name = part.slice(0, eq).trim();
                    const valueExpr = part.slice(eq + 1).trim();
                    const isRef = name.startsWith('&');
                    if (isRef) name = name.slice(1).trim();
                    if (!/^[A-Za-z_]\w*$/.test(name)) continue;
                    const val = evaluateExpression(valueExpr, variables);
                    variables[name] = val === null ? valueExpr : val;
                    if (isRef && /^[A-Za-z_]\w*$/.test(valueExpr) && valueExpr in variables) {
                        aliasOf[name] = valueExpr;
                    }
                }
            }

            // Plain reassignments on their own line: "name = expr;"
            const assignRegex = /(?:^|\n)[ \t]*([A-Za-z_]\w*)\s*=\s*([^=;][^;]*);/g;
            let am;
            while ((am = assignRegex.exec(code))) {
                const name = am[1];
                if (!(name in variables)) continue;
                const val = evaluateExpression(am[2], variables);
                if (val === null) continue;
                variables[name] = val;
                if (aliasOf[name]) variables[aliasOf[name]] = val;
                for (const [aliasName, target] of Object.entries(aliasOf)) {
                    if (target === name) variables[aliasName] = val;
                }
            }

            // Only int/double comparisons are supported ("score >= 90").
            function evaluateCondition(cond, vars) {
                const m = cond.trim().match(/^(.+?)(>=|<=|==|!=|>|<)(.+)$/);
                if (!m) return null;
                const left = parseFloat(evaluateExpression(m[1].trim(), vars));
                const right = parseFloat(evaluateExpression(m[3].trim(), vars));
                if (isNaN(left) || isNaN(right)) return null;
                switch (m[2]) {
                    case '>=': return left >= right;
                    case '<=': return left <= right;
                    case '==': return left === right;
                    case '!=': return left !== right;
                    case '>': return left > right;
                    case '<': return left < right;
                }
                return null;
            }

            // Collapse if / else-if / else chains down to whichever branch's
            // condition is true, so the cout scan only sees that one branch.
            function resolveIfChains(src) {
                let result = '';
                let i = 0;
                while (true) {
                    const rest = src.slice(i);
                    const ifMatch = /\bif\s*\(/.exec(rest);
                    if (!ifMatch) { result += rest; break; }

                    const ifStart = i + ifMatch.index;
                    result += src.slice(i, ifStart);

                    let pos = ifStart + ifMatch[0].length;
                    let depth = 1;
                    while (depth > 0) {
                        if (src[pos] === '(') depth++;
                        else if (src[pos] === ')') depth--;
                        pos++;
                    }
                    const condition = src.slice(ifStart + ifMatch[0].length, pos - 1);

                    let bracePos = src.indexOf('{', pos);
                    let braceEnd = findMatchingBrace(src, bracePos);
                    const branches = [{ cond: condition, body: src.slice(bracePos + 1, braceEnd) }];
                    let cursor = braceEnd + 1;

                    while (true) {
                        const afterRest = src.slice(cursor);
                        const elseIfMatch = /^\s*else\s+if\s*\(/.exec(afterRest);
                        const elseMatch = /^\s*else\s*\{/.exec(afterRest);
                        if (elseIfMatch) {
                            let p2 = cursor + elseIfMatch[0].length;
                            let d2 = 1;
                            while (d2 > 0) {
                                if (src[p2] === '(') d2++;
                                else if (src[p2] === ')') d2--;
                                p2++;
                            }
                            const cond2 = src.slice(cursor + elseIfMatch[0].length, p2 - 1);
                            const bp2 = src.indexOf('{', p2);
                            const be2 = findMatchingBrace(src, bp2);
                            branches.push({ cond: cond2, body: src.slice(bp2 + 1, be2) });
                            cursor = be2 + 1;
                        } else if (elseMatch) {
                            const bp3 = cursor + afterRest.indexOf('{');
                            const be3 = findMatchingBrace(src, bp3);
                            branches.push({ cond: null, body: src.slice(bp3 + 1, be3) });
                            cursor = be3 + 1;
                            break;
                        } else {
                            break;
                        }
                    }

                    let chosen = '';
                    for (const branch of branches) {
                        if (branch.cond === null) { chosen = branch.body; break; }
                        if (evaluateCondition(branch.cond, variables)) { chosen = branch.body; break; }
                    }
                    result += chosen;
                    i = cursor;
                }
                return result;
            }

            // Collapse a switch statement down to the matching case's body
            // (stopping at its break), so the cout scan only sees that case.
            function resolveSwitch(src) {
                let result = '';
                let i = 0;
                while (true) {
                    const rest = src.slice(i);
                    const swMatch = /\bswitch\s*\(/.exec(rest);
                    if (!swMatch) { result += rest; break; }

                    const swStart = i + swMatch.index;
                    result += src.slice(i, swStart);

                    let pos = swStart + swMatch[0].length;
                    let depth = 1;
                    while (depth > 0) {
                        if (src[pos] === '(') depth++;
                        else if (src[pos] === ')') depth--;
                        pos++;
                    }
                    const switchExpr = src.slice(swStart + swMatch[0].length, pos - 1).trim();
                    const bracePos = src.indexOf('{', pos);
                    const braceEnd = findMatchingBrace(src, bracePos);
                    const body = src.slice(bracePos + 1, braceEnd);
                    const switchVal = evaluateExpression(switchExpr, variables);

                    const caseRegex = /case\s+([^:]+):|default\s*:/g;
                    const segments = [];
                    let lastLabel = null;
                    let lastIndex = 0;
                    let cm2;
                    while ((cm2 = caseRegex.exec(body))) {
                        if (lastLabel !== null) segments.push({ label: lastLabel, body: body.slice(lastIndex, cm2.index) });
                        lastLabel = cm2[1] !== undefined ? cm2[1].trim() : '__default__';
                        lastIndex = caseRegex.lastIndex;
                    }
                    if (lastLabel !== null) segments.push({ label: lastLabel, body: body.slice(lastIndex) });

                    let chosen = '';
                    let matched = false;
                    for (const seg of segments) {
                        if (!matched) {
                            if (seg.label === '__default__') continue;
                            const labelVal = evaluateExpression(seg.label, variables);
                            if (labelVal !== null && switchVal !== null && labelVal === switchVal) matched = true;
                        }
                        if (matched) {
                            const breakIdx = seg.body.indexOf('break');
                            chosen += breakIdx === -1 ? seg.body : seg.body.slice(0, breakIdx);
                            if (breakIdx !== -1) break;
                        }
                    }
                    if (!matched) {
                        const def = segments.find((seg) => seg.label === '__default__');
                        if (def) {
                            const breakIdx = def.body.indexOf('break');
                            chosen = breakIdx === -1 ? def.body : def.body.slice(0, breakIdx);
                        }
                    }

                    result += chosen;
                    i = braceEnd + 1;
                }
                return result;
            }

            const resolvedCode = resolveSwitch(resolveIfChains(code));

            // cout chains: split on "<<" outside quotes, resolve each token.
            function splitChain(expr) {
                const parts = [];
                let inStr = false;
                let current = '';
                for (let i = 0; i < expr.length; i++) {
                    const ch = expr[i];
                    if (ch === '"') inStr = !inStr;
                    if (!inStr && ch === '<' && expr[i + 1] === '<') {
                        parts.push(current);
                        current = '';
                        i++;
                        continue;
                    }
                    current += ch;
                }
                parts.push(current);
                return parts.map((p) => p.trim()).filter((p) => p !== '');
            }

            let output = '';
            let anyResolved = false;
            const coutRegex = /cout\s*<<([^;]+);/g;
            let cm;
            while ((cm = coutRegex.exec(resolvedCode))) {
                for (const token of splitChain(cm[1])) {
                    if (token === 'endl') {
                        output += '\n';
                        anyResolved = true;
                        continue;
                    }
                    const value = evaluateExpression(token, variables);
                    if (value !== null) {
                        output += value;
                        anyResolved = true;
                    }
                }
            }

            consoleEl.textContent = anyResolved
                ? output + '\n\n[Program finished with exit code 0]'
                : '// Симуляц консол нь зөвхөн cout-оор шууд хэвлэгдэх статик утгуудыг харуулна.\n[Program finished with exit code 0]';
        }

        runBtn.addEventListener('click', () => (mode === 'cpp' ? runCpp() : runWeb()));

        if (mode !== 'cpp') {
            runWeb();
        }
    })();
</script>
@endpush

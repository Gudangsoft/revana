<?php

namespace App\Services;

class CitationGenerator
{
    /**
     * Generate semua format sitasi dari metadata artikel.
     */
    public static function generate(array $m): array
    {
        $penulis = trim($m['penulis']       ?? '');
        $judul   = trim($m['judul_artikel'] ?? '');
        $jurnal  = trim($m['nama_jurnal']   ?? '');
        $tahun   = trim((string)($m['tahun'] ?? ''));
        $vol     = trim($m['volume']        ?? '');
        $no      = trim($m['nomor']         ?? '');
        $hal     = trim($m['halaman']       ?? '');
        $doi     = trim($m['doi']           ?? '');

        if (!$penulis && !$judul) return [];

        $authors  = self::parseAuthors($penulis);
        $doiUrl   = $doi ? ' https://doi.org/' . ltrim(str_replace(['https://doi.org/','http://doi.org/','doi.org/'], '', $doi), '/') : '';
        $doiIEEE  = $doi ? ', doi: ' . ltrim(str_replace(['https://doi.org/','http://doi.org/','doi.org/'], '', $doi), '/') : '';

        return array_filter([
            'APA'       => self::apa($authors, $judul, $jurnal, $tahun, $vol, $no, $hal, $doiUrl),
            'IEEE'      => self::ieee($authors, $judul, $jurnal, $tahun, $vol, $no, $hal, $doiIEEE),
            'Harvard'   => self::harvard($authors, $judul, $jurnal, $tahun, $vol, $no, $hal, $doiUrl),
            'Chicago'   => self::chicago($authors, $judul, $jurnal, $tahun, $vol, $no, $hal, $doiUrl),
            'Vancouver' => self::vancouver($authors, $judul, $jurnal, $tahun, $vol, $no, $hal, $doiUrl),
            'MLA'       => self::mla($authors, $judul, $jurnal, $tahun, $vol, $no, $hal),
            'ABNT'      => self::abnt($authors, $judul, $jurnal, $tahun, $vol, $no, $hal, $doiUrl),
        ]);
    }

    /* ─────────────────── PARSER PENULIS ─────────────────── */

    /**
     * Parse "Lastname, F. M., & Lastname2, F. M." → [['last'=>..,'init'=>..], ...]
     */
    public static function parseAuthors(string $raw): array
    {
        if (!$raw) return [];

        // Normalise separators
        $raw = preg_replace('/\s*[,;]?\s*&\s*/', ', ', $raw); // "& " → ", "
        $raw = preg_replace('/\s+and\s+/i', ', ', $raw);

        // Split on ", " that comes AFTER a period (end of initials like "A. W.")
        // so "Pratama, A. W., Setiawan, B." → ["Pratama, A. W.", "Setiawan, B."]
        $parts = preg_split('/(?<=\.),\s*/', $raw);

        $authors = [];
        foreach ($parts as $part) {
            $part = trim($part, " ,\t");
            if (!$part) continue;

            // Split on FIRST comma: "Lastname, Initials"
            $comma = strpos($part, ',');
            if ($comma !== false) {
                $last = trim(substr($part, 0, $comma));
                $init = trim(substr($part, $comma + 1));
            } else {
                // No comma — could be "FirstName LastName" natural order
                $words = explode(' ', $part);
                $last  = array_pop($words);
                $init  = implode(' ', $words);
            }
            $last = rtrim($last, '.,');
            if ($last) $authors[] = ['last' => $last, 'init' => $init];
        }

        if (!$authors && $raw) {
            $authors[] = ['last' => trim($raw), 'init' => ''];
        }

        return $authors;
    }

    /** Format inisial: pastikan diakhiri satu titik, tidak double */
    private static function init(string $i): string
    {
        return $i ? rtrim($i, '.') . '.' : '';
    }

    /* ─────────────────── FORMAT AUTHOR STRINGS ─────────────────── */

    private static function fmtAPA(array $authors): string
    {
        $list = array_map(fn($a) => $a['last'] . ($a['init'] ? ', ' . self::init($a['init']) : ''), $authors);
        if (count($list) <= 1) return implode('', $list);
        $last = array_pop($list);
        return implode(', ', $list) . ', & ' . $last;
    }

    private static function fmtIEEE(array $authors): string
    {
        // IEEE: "F. M. Lastname" order
        $list = array_map(fn($a) => ($a['init'] ? self::init($a['init']) . ' ' : '') . $a['last'], $authors);
        if (count($list) === 1) return $list[0];
        if (count($list) === 2) return implode(' and ', $list);
        $last = array_pop($list);
        return implode(', ', $list) . ', and ' . $last;
    }

    private static function fmtHarvard(array $authors): string
    {
        $list = array_map(fn($a) => $a['last'] . ($a['init'] ? ', ' . self::init($a['init']) : ''), $authors);
        if (count($list) === 1) return $list[0];
        $last = array_pop($list);
        return implode(', ', $list) . ' and ' . $last;
    }

    private static function fmtChicago(array $authors): string
    {
        if (!$authors) return '';
        $first = $authors[0]['last'] . ($authors[0]['init'] ? ', ' . self::init($authors[0]['init']) : '');
        if (count($authors) === 1) return $first;
        $rest = array_map(fn($a) => ($a['init'] ? self::init($a['init']) . ' ' : '') . $a['last'], array_slice($authors, 1));
        if (count($authors) === 2) return $first . ', and ' . $rest[0];
        return $first . ', ' . implode(', ', array_slice($rest, 0, -1)) . ', and ' . end($rest);
    }

    private static function fmtVancouver(array $authors): string
    {
        $list = array_map(fn($a) => $a['last'] . ' ' . preg_replace('/[^A-Za-z]/i', '', $a['init']), $authors);
        return implode(', ', $list);
    }

    private static function fmtMLAFirst(array $authors): string
    {
        if (!$authors) return '';
        $first = $authors[0]['last'] . ($authors[0]['init'] ? ', ' . self::init($authors[0]['init']) : '');
        if (count($authors) === 1) return $first;
        if (count($authors) === 2) return $first . ', and ' . ($authors[1]['init'] ? self::init($authors[1]['init']) . ' ' : '') . $authors[1]['last'];
        return $first . ', et al.';
    }

    private static function fmtABNT(array $authors): string
    {
        $list = array_map(fn($a) => strtoupper($a['last']) . ($a['init'] ? ', ' . self::init($a['init']) : ''), $authors);
        return implode('; ', $list);
    }

    /* ─────────────────── STYLE GENERATORS ─────────────────── */

    private static function volNo(string $vol, string $no): string
    {
        if ($vol && $no) return "{$vol}({$no})";
        return $vol ?: $no;
    }

    private static function apa(array $au, string $j, string $jn, string $t, string $v, string $n, string $h, string $doi): string
    {
        if (!$au && !$j) return '';
        $a   = self::fmtAPA($au);
        $vn  = self::volNo($v, $n);
        $hal = $h ? ", {$h}" : '';
        return trim("{$a} ({$t}). {$j}. *{$jn}*" . ($vn ? ", *{$vn}*" : '') . "{$hal}.{$doi}");
    }

    private static function ieee(array $au, string $j, string $jn, string $t, string $v, string $n, string $h, string $doi): string
    {
        if (!$au && !$j) return '';
        $a   = self::fmtIEEE($au);
        $vn  = ($v ? "vol. {$v}" : '') . ($n ? ", no. {$n}" : '');
        $pp  = $h ? "pp. {$h}" : '';
        $parts = array_filter([$vn, $pp, $t]);
        return trim("{$a}, \"{$j},\" *{$jn}*" . ($parts ? ', ' . implode(', ', $parts) : '') . "{$doi}.");
    }

    private static function harvard(array $au, string $j, string $jn, string $t, string $v, string $n, string $h, string $doi): string
    {
        if (!$au && !$j) return '';
        $a   = self::fmtHarvard($au);
        $vn  = self::volNo($v, $n);
        $hal = $h ? ", pp.{$h}" : '';
        return trim("{$a} ({$t}) '{$j}', *{$jn}*" . ($vn ? ", {$vn}" : '') . "{$hal}.{$doi}");
    }

    private static function chicago(array $au, string $j, string $jn, string $t, string $v, string $n, string $h, string $doi): string
    {
        if (!$au && !$j) return '';
        $a   = self::fmtChicago($au);
        $hal = $h ? ": {$h}" : '';
        return trim("{$a}. \"{$j}.\" *{$jn}*" . ($v ? " {$v}" : '') . ($n ? ", no. {$n}" : '') . " ({$t}){$hal}.{$doi}");
    }

    private static function vancouver(array $au, string $j, string $jn, string $t, string $v, string $n, string $h, string $doi): string
    {
        if (!$au && !$j) return '';
        $a  = self::fmtVancouver($au);
        $vn = $v . ($n ? "({$n})" : '');
        return trim("{$a}. {$j}. {$jn}. {$t}" . ($vn ? ";{$vn}" : '') . ($h ? ":{$h}" : '') . ".{$doi}");
    }

    private static function mla(array $au, string $j, string $jn, string $t, string $v, string $n, string $h): string
    {
        if (!$au && !$j) return '';
        $a   = self::fmtMLAFirst($au);
        $vn  = ($v ? "vol. {$v}" : '') . ($n ? ", no. {$n}" : '');
        $hal = $h ? ", pp. {$h}" : '';
        return trim("{$a}. \"{$j}.\" *{$jn}*" . ($vn ? ", {$vn}" : '') . ", {$t}{$hal}.");
    }

    private static function abnt(array $au, string $j, string $jn, string $t, string $v, string $n, string $h, string $doi): string
    {
        if (!$au && !$j) return '';
        $a   = self::fmtABNT($au);
        $hal = $h ? "p. {$h}, " : '';
        return trim("{$a}. {$j}. *{$jn}*" . ($v ? ", v. {$v}" : '') . ($n ? ", n. {$n}" : '') . ", {$hal}{$t}.{$doi}");
    }
}

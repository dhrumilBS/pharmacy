# Builds the placeholder "trusted by" logos as standalone SVG files in assets/img/trusted/.
# PLACEHOLDERS: invented names for layout review only. Replace each file with the real customer's logo
# (single-colour SVG, with written permission) and update $logos in partials/trusted-strip.php.
# Text uses system fonts because fonts don't load inside <img> SVGs. Width is estimated per style.
# Usage: .\scripts\make-trusted-logos.ps1
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$out  = Join-Path $root 'assets\img\trusted'
New-Item -ItemType Directory -Force $out | Out-Null
$ink = '#123B5D'   # --navy-800

# Glyphs drawn in a 24-unit box
$g = @{
  leaf    = '<path d="M5 19c0-9 6-14 15-14 0 9-5 15-14 15M5 19l8-8" fill="none" stroke="INK" stroke-width="2" stroke-linecap="round"/>'
  wave    = '<path d="M2 9c3-3 5 3 8 0s5 3 8 0 3-1 4-1M2 15c3-3 5 3 8 0s5 3 8 0 3-1 4-1" fill="none" stroke="INK" stroke-width="2.2" stroke-linecap="round"/>'
  cross   = '<circle cx="12" cy="12" r="10" fill="INK"/><path d="M10 6.5h4v3.5h3.5v4H14v3.5h-4V14H6.5v-4H10z" fill="#fff"/>'
  peak    = '<path d="M2 19 9 7l4 6 3-4 6 10z" fill="none" stroke="INK" stroke-width="1.8" stroke-linejoin="round"/>'
  hex     = '<path d="m12 2 9 5v10l-9 5-9-5V7z" fill="INK"/><path d="M8.5 8.5 12 15l3.5-6.5" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
  pulse   = '<path d="M1 13h5l2.5-6 4 11 3-8 2 3H23" fill="none" stroke="INK" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>'
  bowl    = '<path d="M3 11h18a9 9 0 0 1-18 0zM14 11l6-8" fill="none" stroke="INK" stroke-width="2" stroke-linecap="round"/>'
  arch    = '<path d="M2 20V12a10 10 0 0 1 20 0v8M7 20v-8a5 5 0 0 1 10 0v8" fill="none" stroke="INK" stroke-width="2"/>'
  capsule = '<rect x="2" y="8" width="20" height="8" rx="4" fill="none" stroke="INK" stroke-width="2" transform="rotate(-30 12 12)"/><path d="m9.5 7.7 5 8.6" stroke="INK" stroke-width="2"/>'
  up      = '<rect x="2" y="2" width="20" height="20" rx="6" fill="INK"/><path d="M7 15l4-4 2.5 2.5L17 9m0 0h-3.5M17 9v3.5" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
}
# file, name, glyph, style, [second line]
$logos = @(
  @('sanjivani-medicos',   'Sanjivani Medicos',      'leaf',    'serif'),
  @('kaveri-pharma',       'KAVERI PHARMA',          'wave',    'caps'),
  @('arogya-chemists',     'Arogya Chemists',        'cross',   'round'),
  @('nilgiri-healthcare',  'Nilgiri Healthcare',     'peak',    'light'),
  @('vardhan-pharma',      'Vardhan',                'hex',     'stack', 'PHARMA DISTRIBUTORS'),
  @('lifeline-pharmacy',   'Lifeline Pharmacy',      'pulse',   'round'),
  @('shree-ram-medical',   'Shree Ram Medical Hall', 'bowl',    'serif'),
  @('medisetu',            'MEDISETU',               'arch',    'caps'),
  @('dhanvantari-drug',    'Dhanvantari Drug House', 'capsule', 'light'),
  @('pragati-medical',     'Pragati Medical Stores', 'up',      'round')
)
$sans = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif"
foreach ($l in $logos) {
  $file, $name, $glyph, $style = $l[0..3]
  switch ($style) {
    'serif' { $txt = "<text x='40' y='27' font-family=`"Georgia, 'Times New Roman', serif`" font-style='italic' font-size='22' fill='$ink'>$name</text>"; $w = 40 + $name.Length * 11.6 }
    'caps'  { $txt = "<text x='40' y='25' font-family=`"$sans`" font-weight='800' font-size='15' letter-spacing='3.6' fill='$ink'>$name</text>"; $w = 40 + $name.Length * 14.2 }
    'round' { $txt = "<text x='40' y='26' font-family=`"$sans`" font-weight='700' font-size='19' letter-spacing='-0.4' fill='$ink'>$name</text>"; $w = 40 + $name.Length * 10.4 }
    'light' { $txt = "<text x='40' y='26' font-family=`"$sans`" font-weight='400' font-size='20' fill='$ink'>$name</text>"; $w = 40 + $name.Length * 10.0 }
    'stack' { $sub = $l[4]; $txt = "<text x='40' y='19' font-family=`"$sans`" font-weight='800' font-size='17' fill='$ink'>$name</text><text x='40' y='32' font-family=`"$sans`" font-weight='600' font-size='8.5' letter-spacing='1.9' fill='$ink'>$sub</text>"; $w = 40 + [Math]::Max($name.Length * 10.5, $sub.Length * 7.4) }
  }
  $w = [int][Math]::Ceiling($w)
  $mark = $g[$glyph].Replace('INK', $ink)
  $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 $w 40' width='$w' height='40' role='img'><title>$name (placeholder)</title><g transform='translate(0 6) scale(1.17)'>$mark</g>$txt</svg>"
  Set-Content -Path (Join-Path $out "$file.svg") -Value $svg -Encoding utf8 -NoNewline
  "{0}.svg  {1}px" -f $file, $w
}

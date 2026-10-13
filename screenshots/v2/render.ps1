# v2: renders src/*.html (app screens, 1440x900) to v2/<name>.png,
# then graphics/src/*.html (website graphics) to graphics/<name>.png. All at 2x, headless Edge.
# Usage:  .\screenshots\v2\render.ps1 [-Only <name prefix>]
param([string]$Only = '')

$edge = "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$prof = Join-Path $env:TEMP 'hr-shot-profile-v2'

# Canvas sizes (CSS px). Screens default 1440x900, graphics default 1600x1000.
$sizes = @{ 'hero-showcase' = '1600,1000' }

function Render($dir, $outDir, $default) {
  Get-ChildItem $dir -Filter *.html -ErrorAction SilentlyContinue | Where-Object { $_.BaseName -like "$Only*" } | ForEach-Object {
    $size = if ($sizes.ContainsKey($_.BaseName)) { $sizes[$_.BaseName] } else { $default }
    $out  = Join-Path $outDir ($_.BaseName + '.png')
    $url  = 'file:///' + ($_.FullName -replace '\\', '/')
    Start-Process $edge -Wait -ArgumentList '--headless=new', '--disable-gpu', '--hide-scrollbars',
      '--allow-file-access-from-files', "--user-data-dir=$prof", '--force-device-scale-factor=2',
      '--virtual-time-budget=5000', "--window-size=$size", "--screenshot=$out", $url
    Write-Output "rendered $out ($size @2x)"
  }
}

Render (Join-Path $root 'src') $root '1440,900'
Render (Join-Path $root 'graphics\src') (Join-Path $root 'graphics') '1600,1000'

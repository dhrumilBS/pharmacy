# v3: renders screenshots/v3/src/*.html to screenshots/v3/<name>.png at 2x (1440x960 by default).
# Usage:  .\screenshots\v3\render.ps1 [-Only <name prefix>]
param([string]$Only = '')
$edge = "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$prof = Join-Path $env:TEMP 'hr-shot-profile-v3'
$sizes = @{}   # per-file overrides, e.g. @{ '05-mobile' = '420,900' }
Get-ChildItem (Join-Path $root 'src') -Filter *.html | Where-Object { $_.BaseName -like "$Only*" } | ForEach-Object {
  $size = if ($sizes.ContainsKey($_.BaseName)) { $sizes[$_.BaseName] } else { '1440,960' }
  $out  = Join-Path $root ($_.BaseName + '.png')
  $url  = 'file:///' + ($_.FullName -replace '\\', '/')
  Start-Process $edge -Wait -ArgumentList '--headless=new', '--disable-gpu', '--hide-scrollbars', '--allow-file-access-from-files',
    "--user-data-dir=$prof", '--force-device-scale-factor=2', '--virtual-time-budget=5000', "--window-size=$size", "--screenshot=$out", $url
  Write-Output "rendered $out ($size @2x)"
}

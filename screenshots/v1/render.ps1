# Renders every screenshots/src/*.html to screenshots/<name>.png at 2x using headless Edge.
# Usage (PowerShell):  .\screenshots\render.ps1            # all screens
#                      .\screenshots\render.ps1 01-sale     # only names starting with "01-sale"
param([string]$Only = '')

$edge = "${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$src  = Join-Path $root 'src'
$prof = Join-Path $env:TEMP 'hr-shot-profile'

# Page size per screen (CSS px). Default 1600x900.
$sizes = @{ '01-sale-billing' = '1600,780'; '02-dashboard' = '1600,1120'; '04-purchase-csv-import' = '1600,780'; '05-stock-alert-short-book' = '1600,700'; '06-gst-report' = '1600,700'; '07-token-board' = '1600,580' }

Get-ChildItem $src -Filter *.html | Where-Object { $_.BaseName -like "$Only*" } | ForEach-Object {
  $size = if ($sizes.ContainsKey($_.BaseName)) { $sizes[$_.BaseName] } else { '1600,900' }
  $out  = Join-Path $root ($_.BaseName + '.png')
  $url  = 'file:///' + ($_.FullName -replace '\\', '/')
  Start-Process $edge -Wait -ArgumentList '--headless=new', '--disable-gpu', '--hide-scrollbars',
    "--user-data-dir=$prof", '--force-device-scale-factor=2', '--virtual-time-budget=4000',
    "--window-size=$size", "--screenshot=$out", $url
  Write-Output "rendered $($_.BaseName).png ($size @2x)"
}

<#
.SYNOPSIS
    Records or verifies SHA-256 hashes of every PHP_Site file, so the WordPress
    migration can prove it left the existing site untouched (Requirement 1.1, 1.2).

.DESCRIPTION
    The PHP_Site is the repository root (two levels above this script). These
    folders are skipped because they are not part of the site or change on their own:
      .git/  .kiro/  wordpress/  partials/data/cache/

    Directory junctions and symbolic links are not followed.

    Baseline format (UTF-8, no BOM), one line per file, sorted ordinally by path:
      <sha256 lowercase hex><two spaces><path relative to the repo root, forward slashes>
    Lines starting with '#' are comments.

.PARAMETER Record
    Hash every file and write wordpress/tools/out/php-site-baseline.txt.
    Refuses to overwrite an existing baseline unless -Force is given.

.PARAMETER Verify
    Re-hash every file and list added, changed and removed files.

.PARAMETER Force
    With -Record, overwrite an existing baseline.

.EXAMPLE
    powershell -NoProfile -ExecutionPolicy Bypass -File wordpress\tools\baseline.ps1 -Record

.EXAMPLE
    powershell -NoProfile -ExecutionPolicy Bypass -File wordpress\tools\baseline.ps1 -Verify

.NOTES
    Exit codes: 0 = recorded / no differences, 1 = differences found, 2 = usage or I/O error.
#>
[CmdletBinding()]
param(
    [switch] $Record,
    [switch] $Verify,
    [switch] $Force
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# Paths -----------------------------------------------------------------------

$RepoRoot     = [System.IO.Path]::GetFullPath( ( Join-Path $PSScriptRoot '..\..' ) ).TrimEnd( '\' )
$BaselinePath = Join-Path $PSScriptRoot 'out\php-site-baseline.txt'

# Folders skipped, relative to the repo root (compared case-insensitively, as Windows does).
$Excluded = @( '.git', '.kiro', 'wordpress', 'partials/data/cache' )

# Helpers ---------------------------------------------------------------------

function Get-RelativePath( [string] $FullPath ) {
    return $FullPath.Substring( $RepoRoot.Length + 1 ).Replace( '\', '/' )
}

function Test-Excluded( [string] $RelativeDir ) {
    foreach ( $ex in $Excluded ) {
        if ( [string]::Equals( $RelativeDir, $ex, [System.StringComparison]::OrdinalIgnoreCase ) ) {
            return $true
        }
    }
    return $false
}

# Returns a Dictionary[path -> sha256] for every PHP_Site file.
function Get-SiteHashes {
    $hashes = New-Object 'System.Collections.Generic.Dictionary[string,string]' ( [System.StringComparer]::Ordinal )
    $sha    = [System.Security.Cryptography.SHA256]::Create()
    $stack  = New-Object 'System.Collections.Generic.Stack[System.IO.DirectoryInfo]'
    $stack.Push( ( New-Object System.IO.DirectoryInfo $RepoRoot ) )

    try {
        while ( $stack.Count -gt 0 ) {
            $dir = $stack.Pop()
            foreach ( $entry in $dir.EnumerateFileSystemInfos() ) {
                $rel = Get-RelativePath $entry.FullName

                if ( $entry -is [System.IO.DirectoryInfo] ) {
                    # Never follow junctions or symlinks out of the repository.
                    if ( ( $entry.Attributes -band [System.IO.FileAttributes]::ReparsePoint ) -ne 0 ) { continue }
                    if ( Test-Excluded $rel ) { continue }
                    $stack.Push( $entry )
                    continue
                }

                $stream = [System.IO.File]::Open( $entry.FullName, 'Open', 'Read', 'ReadWrite' )
                try {
                    $bytes = $sha.ComputeHash( $stream )
                } finally {
                    $stream.Dispose()
                }
                $hashes[ $rel ] = ( [System.BitConverter]::ToString( $bytes ) -replace '-', '' ).ToLowerInvariant()
            }
        }
    } finally {
        $sha.Dispose()
    }

    return , $hashes
}

function Get-SortedKeys( $Dictionary ) {
    $keys = New-Object 'System.Collections.Generic.List[string]' ( , [string[]] @( $Dictionary.Keys ) )
    $keys.Sort( [System.StringComparer]::Ordinal )
    return , $keys
}

function Read-Baseline {
    $hashes = New-Object 'System.Collections.Generic.Dictionary[string,string]' ( [System.StringComparer]::Ordinal )
    $lineNo = 0
    foreach ( $line in [System.IO.File]::ReadAllLines( $BaselinePath, ( New-Object System.Text.UTF8Encoding $false ) ) ) {
        $lineNo++
        if ( $line -eq '' -or $line.StartsWith( '#' ) ) { continue }
        if ( $line -notmatch '^([0-9a-f]{64})  (.+)$' ) {
            throw "Malformed baseline line ${lineNo}: $line"
        }
        $hashes[ $Matches[2] ] = $Matches[1]
    }
    return , $hashes
}

# Main ------------------------------------------------------------------------

if ( $Record -eq $Verify ) {
    Write-Host 'Usage: baseline.ps1 -Record [-Force] | -Verify'
    exit 2
}

try {
    if ( $Record ) {
        if ( ( Test-Path -LiteralPath $BaselinePath ) -and -not $Force ) {
            Write-Host "Baseline already exists: $BaselinePath"
            Write-Host 'It is the reference for -Verify. Use -Record -Force only to replace it on purpose.'
            exit 2
        }

        $hashes = Get-SiteHashes
        $keys   = Get-SortedKeys $hashes

        $lines = New-Object 'System.Collections.Generic.List[string]'
        $lines.Add( '# PHP_Site baseline (SHA-256). Written by wordpress/tools/baseline.ps1 -Record' )
        $lines.Add( "# root: $RepoRoot" )
        $lines.Add( '# excluded: ' + ( ( $Excluded | ForEach-Object { "$_/" } ) -join ' ' ) )
        $lines.Add( '# recorded: ' + ( Get-Date ).ToString( 'yyyy-MM-ddTHH:mm:sszzz', [System.Globalization.CultureInfo]::InvariantCulture ) )
        $lines.Add( "# files: $($keys.Count)" )
        foreach ( $k in $keys ) { $lines.Add( "$($hashes[$k])  $k" ) }

        $outDir = Split-Path -Parent $BaselinePath
        if ( -not ( Test-Path -LiteralPath $outDir ) ) {
            New-Item -ItemType Directory -Path $outDir | Out-Null
        }
        [System.IO.File]::WriteAllLines( $BaselinePath, $lines, ( New-Object System.Text.UTF8Encoding $false ) )

        Write-Host "Recorded $($keys.Count) files to $BaselinePath"
        exit 0
    }

    # -Verify
    if ( -not ( Test-Path -LiteralPath $BaselinePath ) ) {
        Write-Host "No baseline at $BaselinePath. Run baseline.ps1 -Record first."
        exit 2
    }

    $before = Read-Baseline
    $now    = Get-SiteHashes

    $added   = New-Object 'System.Collections.Generic.List[string]'
    $changed = New-Object 'System.Collections.Generic.List[string]'
    $removed = New-Object 'System.Collections.Generic.List[string]'

    foreach ( $k in $now.Keys ) {
        if ( -not $before.ContainsKey( $k ) ) { $added.Add( $k ) }
        elseif ( $before[ $k ] -ne $now[ $k ] ) { $changed.Add( $k ) }
    }
    foreach ( $k in $before.Keys ) {
        if ( -not $now.ContainsKey( $k ) ) { $removed.Add( $k ) }
    }
    foreach ( $list in @( $added, $changed, $removed ) ) { $list.Sort( [System.StringComparer]::Ordinal ) }

    foreach ( $group in @( @( 'Added', $added ), @( 'Changed', $changed ), @( 'Removed', $removed ) ) ) {
        if ( $group[1].Count -eq 0 ) { continue }
        Write-Host "$($group[0]) ($($group[1].Count)):"
        foreach ( $p in $group[1] ) { Write-Host "  $p" }
    }

    $diffCount = $added.Count + $changed.Count + $removed.Count
    if ( $diffCount -gt 0 ) {
        Write-Host "PHP_Site differs from the baseline: $($added.Count) added, $($changed.Count) changed, $($removed.Count) removed."
        exit 1
    }

    Write-Host "PHP_Site unchanged: $($now.Count) files match the baseline."
    exit 0
} catch {
    Write-Host "baseline.ps1 failed: $($_.Exception.Message)"
    exit 2
}

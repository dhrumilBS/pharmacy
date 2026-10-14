# Converts rendered product screens into sharp web JPGs for the site (assets/img/), in two sizes so the
# browser picks one and resamples once (see img_tag() in partials/config.php):
#   screenshots/v2/<name>.png          -> assets/img/v2-<name>.jpg (1440 px) + v2-<name>@2x.jpg (2880 px)
# Sources are rendered at 2x, so the @2x files are full resolution with no resampling.
# Quality 92: UI text blurs and rings at lower JPEG quality.
# Usage: .\scripts\export-images.ps1
Add-Type -AssemblyName System.Drawing
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$out  = Join-Path $root 'assets\img'
New-Item -ItemType Directory -Force $out | Out-Null

$codec = [Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq 'image/jpeg' }
$params = New-Object Drawing.Imaging.EncoderParameters 1
$params.Param[0] = New-Object Drawing.Imaging.EncoderParameter ([Drawing.Imaging.Encoder]::Quality), 92L

function Export($src, $dst, $width) {
  $img = [Drawing.Image]::FromFile($src)
  $h = [int][Math]::Round($img.Height * $width / $img.Width)
  $bmp = New-Object Drawing.Bitmap $width, $h
  $g = [Drawing.Graphics]::FromImage($bmp)
  $g.InterpolationMode = 'HighQualityBicubic'; $g.SmoothingMode = 'HighQuality'; $g.PixelOffsetMode = 'HighQuality'
  $g.CompositingQuality = 'HighQuality'
  # Clamp edges so the bicubic filter doesn't pull a soft border in from outside the image
  $attr = New-Object Drawing.Imaging.ImageAttributes; $attr.SetWrapMode('TileFlipXY')
  $g.Clear([Drawing.Color]::White)
  $g.DrawImage($img, (New-Object Drawing.Rectangle 0, 0, $width, $h), 0, 0, $img.Width, $img.Height, 'Pixel', $attr)
  $bmp.Save($dst, $codec, $params)
  $g.Dispose(); $bmp.Dispose(); $img.Dispose(); $attr.Dispose()
  '{0}  {1:N0} KB' -f (Split-Path $dst -Leaf), ((Get-Item $dst).Length / 1KB)
}

function ExportPair($src, $base, $width) {
  Export $src (Join-Path $out ($base + '.jpg')) $width
  Export $src (Join-Path $out ($base + '@2x.jpg')) ($width * 2)
}

Get-ChildItem (Join-Path $root 'screenshots\v2') -Filter *.png | ForEach-Object {
  ExportPair $_.FullName ('v2-' + $_.BaseName) 1440
}

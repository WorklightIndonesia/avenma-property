# Exports the public site as plain HTML for a design review on Vercel.
#
# The demo server must be started with DEMO_NOTICE=1 so the catalogue pages say
# their filters are inactive. Example:
#
#   $env:DEMO_NOTICE="1"; php artisan serve --port=8123
#   powershell -File scripts/export-static.ps1
#   npx vercel deploy --prod dist
#
# Writes to dist/ and then checks that every root-relative link resolves.

param(
    [string] $BaseUrl = 'http://127.0.0.1:8123',
    [string] $OutDir = 'dist'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

if (Test-Path $OutDir) { Remove-Item $OutDir -Recurse -Force }
New-Item -ItemType Directory -Path $OutDir | Out-Null

function Write-Text($path, $text) {
    $dir = Split-Path -Parent $path
    if ($dir -and -not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    [System.IO.File]::WriteAllText($path, $text, (New-Object System.Text.UTF8Encoding($false)))
}

function Get-Page($url) {
    (Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 60).Content
}

# Maps an href to a path this export can serve, or $null to skip it.
function Get-PagePath($href) {
    if ($href -notmatch '^/') { return $null }            # external, mailto, tel, #
    $path = $href
    $query = ''
    $mark = $href.IndexOf('?')
    if ($mark -ge 0) {
        $path = $href.Substring(0, $mark)
        $query = $href.Substring($mark + 1)
    }
    if ($path -match '^/(build|images|vendor)/' -or $path -eq '/favicon.ico') { return $null }
    if ($path -in @('/sitemap.xml', '/robots.txt', '/hubungi-kami')) { return $null }

    $path = $path.TrimEnd('/')
    if ($path -eq '') { $path = '/' }

    if ($query -ne '') {
        # Pagination becomes a real directory; filter queries cannot work here.
        if ($query -match '^page=(\d+)$' -and $path -ne '/') { return "$path/halaman/$($Matches[1])" }
        return $null
    }

    return $path
}

# The inverse: the demo path was rewritten from a ?page= query, so the source
# server needs the query back.
function Get-SourcePath($path) {
    if ($path -match '^(.+)/halaman/(\d+)$') { return "$($Matches[1])?page=$($Matches[2])" }
    return $path
}

$seeds = @('/', '/layanan', '/layanan-lama', '/properti', '/blog', '/kontak')
$queue = New-Object 'System.Collections.Generic.Queue[string]'
foreach ($seed in $seeds) { $queue.Enqueue($seed) }

$seen = @{}
$pages = New-Object System.Collections.Specialized.OrderedDictionary

while ($queue.Count -gt 0) {
    $path = $queue.Dequeue()
    if ($seen.ContainsKey($path)) { continue }
    $seen[$path] = $true

    try {
        $html = Get-Page ($BaseUrl + (Get-SourcePath $path))
    } catch {
        # A dead link should show up in the link check, not stop the export.
        Write-Host "  skipped $path ($($_.Exception.Message))" -ForegroundColor Yellow
        continue
    }
    $pages[$path] = $html

    foreach ($m in [regex]::Matches($html, 'href="([^"]+)"')) {
        # Blade prints absolute URLs, so pull the host off first.
        $href = $m.Groups[1].Value.Replace('&amp;', '&')
        if ($href.StartsWith($BaseUrl)) { $href = $href.Substring($BaseUrl.Length) }
        $next = Get-PagePath $href
        if ($next -and -not $seen.ContainsKey($next)) { $queue.Enqueue($next) }
    }
}

$written = 0

# The host appears plain, escaped inside @js() (\/, \\\/), and URL-encoded
# inside the WhatsApp message templates. All forms go, so nothing points at the
# local server.
$authority = ([uri]$BaseUrl).Authority
$plainHost = 'https?:[\\/]+' + [regex]::Escape($authority)
$encodedHost = 'https?%3A%2F%2F' + [regex]::Escape([uri]::EscapeDataString($authority))

foreach ($path in $pages.Keys) {
    $html = $pages[$path]
    # Root-relative, so the copy works on any host.
    $html = [regex]::Replace($html, $plainHost, '')
    $html = [regex]::Replace($html, $encodedHost, '')
    # url('/') prints the bare host, so the root link would end up empty.
    $html = $html.Replace('href=""', 'href="/"')
    $html = [regex]::Replace($html, 'href="(/[^"?#]*[^/"#])\?page=(\d+)"', 'href="$1/halaman/$2"')

    $rel = $path.TrimStart('/')
    if ($rel -eq '') {
        Write-Text (Join-Path $OutDir 'index.html') $html
    } else {
        Write-Text (Join-Path $OutDir "$rel/index.html") $html
    }
    $written++
}

# Assets the pages load by path.
foreach ($dir in @('build', 'images', 'vendor')) {
    if (Test-Path "public/$dir") { Copy-Item "public/$dir" -Destination $OutDir -Recurse -Force }
}
if (Test-Path 'public/favicon.ico') { Copy-Item 'public/favicon.ico' $OutDir -Force }

# Not part of the demo: keep the copy out of search results.
Write-Text (Join-Path $OutDir 'robots.txt') "User-agent: *`nDisallow: /`n"

# The one redirect the backend used to answer.
$whatsapp = (& curl.exe -s -o NUL -w '%{redirect_url}' "$BaseUrl/hubungi-kami").Trim()
if (-not $whatsapp) { throw 'Could not read the /hubungi-kami redirect target.' }

Write-Text (Join-Path $OutDir 'hubungi-kami/index.html') @"
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta http-equiv="refresh" content="0;url=$whatsapp">
<title>WhatsApp Avenma Properti</title>
</head>
<body>
<p>Membuka WhatsApp&hellip;</p>
<p><a href="$whatsapp">Lanjut ke WhatsApp</a></p>
</body>
</html>
"@

# Vercel serves 404.html for unknown paths.
$notFound = & curl.exe -s "$BaseUrl/tidak-ada-halaman-ini"
Write-Text (Join-Path $OutDir '404.html') ([string]$notFound).Replace($BaseUrl, '')

# Every internal link and asset must land on a file this export wrote.
$missing = New-Object System.Collections.Generic.List[string]
$checked = 0
$outFull = (Resolve-Path $OutDir).Path
$files = Get-ChildItem $OutDir -Recurse -Filter *.html -File

foreach ($file in $files) {
    $html = [System.IO.File]::ReadAllText($file.FullName)
    $from = $file.FullName.Substring($outFull.Length + 1)

    foreach ($m in [regex]::Matches($html, '(?:href|src)="(/[^"]*)"')) {
        $href = $m.Groups[1].Value
        $target = $href.Split('#')[0].Split('?')[0]
        if ($target -eq '') { continue }
        $checked++

        $candidate = Join-Path $OutDir $target.TrimStart('/')
        $exists = (Test-Path $candidate -PathType Leaf) -or
                  (Test-Path (Join-Path $candidate 'index.html')) -or
                  (Test-Path "$candidate.html")

        if (-not $exists) { $missing.Add("$from -> $href") }
    }
}

$sizeMb = [math]::Round((Get-ChildItem $OutDir -Recurse -File | Measure-Object Length -Sum).Sum / 1MB, 1)

Write-Host "Pages written : $written"
Write-Host "Links checked : $checked"
Write-Host "Output size   : $sizeMb MB"
if ($missing.Count -gt 0) {
    Write-Host "Broken links  : $($missing.Count)" -ForegroundColor Red
    $missing | Select-Object -First 25 | ForEach-Object { Write-Host "  $_" }
    exit 1
}
Write-Host 'Broken links  : none' -ForegroundColor Green

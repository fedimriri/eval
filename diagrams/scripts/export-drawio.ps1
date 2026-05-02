param(
    [string]$SourceDir = (Join-Path $PSScriptRoot "..\draw_io"),
    [string]$OutputDir = (Join-Path $PSScriptRoot "..\exports"),
    [string]$Format = "png",
    [int]$Dpi = 400,
    [int]$Border = 10
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Convert-ToDrawioPath {
    param([string]$Path)

    $resolved = (Resolve-Path $Path).Path
    return $resolved -replace "\\", "/"
}

function Resolve-DrawioCommand {
    if ($env:DRAWIO_BIN -and (Test-Path $env:DRAWIO_BIN)) {
        return $env:DRAWIO_BIN
    }

    $candidates = @(
        "drawio",
        "draw.io",
        "$env:ProgramFiles\draw.io\draw.io.exe",
        "$env:ProgramFiles\draw.io\drawio.exe",
        "$env:LOCALAPPDATA\Programs\draw.io\draw.io.exe",
        "$env:LOCALAPPDATA\Programs\draw.io\drawio.exe"
    )

    foreach ($candidate in $candidates) {
        if ([string]::IsNullOrWhiteSpace($candidate)) {
            continue
        }

        if (Test-Path $candidate) {
            return $candidate
        }

        $cmd = Get-Command $candidate -ErrorAction SilentlyContinue
        if ($cmd) {
            return $cmd.Source
        }
    }

    throw "Could not find draw.io CLI. Set DRAWIO_BIN or install draw.io desktop CLI."
}

if (-not (Test-Path $SourceDir)) {
    throw "Source directory not found: $SourceDir"
}

if (-not (Test-Path $OutputDir)) {
    New-Item -ItemType Directory -Path $OutputDir -Force | Out-Null
}

# Export folder must be clean before creating new files.
Get-ChildItem -Path $OutputDir -Force | Remove-Item -Recurse -Force

$patterns = @("*.drawio", "*.drawio.xml")

$inputFiles = foreach ($pattern in $patterns) {
    Get-ChildItem -Path $SourceDir -Filter $pattern -File
}

$inputFiles = $inputFiles | Sort-Object FullName -Unique

if (-not $inputFiles -or $inputFiles.Count -eq 0) {
    Write-Host "No draw.io files found in $SourceDir"
    exit 0
}

$drawio = Resolve-DrawioCommand
$normalizedFormat = $Format.ToLowerInvariant()
$expectedExtensions = if ($normalizedFormat -eq "jpeg") { @("jpeg", "jpg") } elseif ($normalizedFormat -eq "jpg") { @("jpg", "jpeg") } else { @($normalizedFormat) }

Write-Host "Using draw.io executable: $drawio"
Write-Host "Exporting $($inputFiles.Count) file(s) from $SourceDir to $OutputDir"

foreach ($file in $inputFiles) {
    $baseName = ($file.Name -replace '\.drawio(\.xml)?$', '')
    $inputPathForDrawio = Convert-ToDrawioPath -Path $file.FullName
    $outputDirForDrawio = Convert-ToDrawioPath -Path $OutputDir

    $args = @(
        $inputPathForDrawio,
        "--export",
        "--format", $normalizedFormat,
        "--output", $outputDirForDrawio,
        "--border", $Border,
        "--dpi", $Dpi,
        "--all-pages"
    )

    # if ($normalizedFormat -eq "png") {
    #     $args += "--transparent"
    # }

    & $drawio @args

    # Under strict mode, read LASTEXITCODE via the variable provider only if it exists.
    $exitCode = $null
    if (Test-Path "Variable:LASTEXITCODE") {
        $exitCode = (Get-Item "Variable:LASTEXITCODE").Value
    }

    if ($null -ne $exitCode -and $exitCode -ne 0) {
        throw "Export failed for file: $($file.FullName) with exit code $exitCode"
    }

    # draw.io may write files asynchronously; allow a short wait for outputs to appear.
    $exportedFiles = @()
    $timeoutAt = (Get-Date).AddSeconds(30)
    do {
        Start-Sleep -Milliseconds 500
        $exportedFiles = @(
            foreach ($ext in $expectedExtensions) {
                Get-ChildItem -Path $OutputDir -Filter ($baseName + "*." + $ext) -File -ErrorAction SilentlyContinue
            }
        )
    } while ($exportedFiles.Count -eq 0 -and (Get-Date) -lt $timeoutAt)

    if (-not $exportedFiles) {
        throw "Export failed for file: $($file.FullName). draw.io did not produce any .$normalizedFormat output."
    }

    Write-Host "Exported: $($file.Name)"
}

Write-Host "Done. $normalizedFormat exports are in $OutputDir"

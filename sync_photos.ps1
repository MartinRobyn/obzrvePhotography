# ============================================================
# sync_photos.ps1
# Copies photos from your Desktop Photos folder into the
# website's image/ directory, preserving the folder structure.
#
# Usage:  Right-click → "Run with PowerShell"
#         OR open PowerShell and run:  .\sync_photos.ps1
#
# To change source/destination, edit the paths below.
# ============================================================

$Source      = "$env:USERPROFILE\OneDrive\Desktop\Photos"
$Destination = "$PSScriptRoot\image"

# Folder name mapping  (source folder → destination folder)
# Add or change mappings here if your folder names differ.
$FolderMap = @{
    'product'  = 'product'
    'wedding'  = 'wedding'
    'event'    = 'event'
    'portait'  = 'portait'
    'portrait' = 'portait'
    'slider'   = 'slider'
    'carousel' = 'slider'
}

$ImageExts = @('.jpg', '.jpeg', '.png', '.webp', '.gif')

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Photono - Photo Sync" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  From: $Source"
Write-Host "  To:   $Destination"
Write-Host ""

if (-not (Test-Path $Source)) {
    Write-Host "ERROR: Source folder not found: $Source" -ForegroundColor Red
    Write-Host "Update the `$Source path at the top of this script." -ForegroundColor Yellow
    pause
    exit 1
}

# Ensure destination root exists
if (-not (Test-Path $Destination)) {
    New-Item -ItemType Directory -Path $Destination -Force | Out-Null
}

$totalCopied = 0
$totalSkipped = 0

foreach ($srcFolder in Get-ChildItem -Path $Source -Directory) {
    $key = $srcFolder.Name.ToLower()
    $destFolderName = $FolderMap[$key]

    if (-not $destFolderName) {
        Write-Host "  SKIP: '$($srcFolder.Name)' (no mapping)" -ForegroundColor DarkGray
        continue
    }

    $destPath = Join-Path $Destination $destFolderName
    Write-Host ""
    Write-Host "  Syncing: $($srcFolder.Name) -> image/$destFolderName" -ForegroundColor Green

    # Get all image files recursively from the source category folder
    $files = Get-ChildItem -Path $srcFolder.FullName -Recurse -File |
             Where-Object { $ImageExts -contains $_.Extension.ToLower() }

    foreach ($file in $files) {
        # Preserve subfolder structure
        $relativePath = $file.FullName.Substring($srcFolder.FullName.Length)
        $destFile = Join-Path $destPath $relativePath
        $destDir  = Split-Path $destFile -Parent

        # Create subdirectory if needed
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }

        # Copy only if file doesn't exist or source is newer
        if (-not (Test-Path $destFile) -or
            (Get-Item $file.FullName).LastWriteTime -gt (Get-Item $destFile).LastWriteTime) {
            Copy-Item -Path $file.FullName -Destination $destFile -Force
            Write-Host "    + $relativePath" -ForegroundColor White
            $totalCopied++
        } else {
            $totalSkipped++
        }
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Done!  Copied: $totalCopied  |  Up-to-date: $totalSkipped" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Your photos are now in the image/ folder."
Write-Host "Open the website in a PHP server to see them."
Write-Host ""
pause




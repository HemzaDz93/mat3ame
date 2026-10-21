#requires -Version 5.1
# تشغيل منصة الزاوية: قاعدة البيانات + خادم الويب + مجدول التنبيهات

$Xampp  = 'C:\xampp'
$AppDir = Split-Path -Parent $PSScriptRoot
$EnvFile = Join-Path $AppDir '.env'
$SslDir  = "$Xampp\apache\conf\ssl\mat3am"
$certChanged = $false

try { [Console]::OutputEncoding = [System.Text.Encoding]::UTF8 } catch { }

function Step($n, $text, $color) { Write-Host "  [$n] $text" -ForegroundColor $color }

Write-Host ''
Write-Host '  ============================================================' -ForegroundColor Cyan
Write-Host '                     منصة الزاوية  -  تشغيل المنصة' -ForegroundColor Cyan
Write-Host '  ============================================================' -ForegroundColor Cyan
Write-Host ''

# ==================== ضبط عنوان الشبكة: كشف تغيّر IP (DHCP) ====================
$envHost = $null
$envLines = @()
if (Test-Path $EnvFile) {
    $envLines = [System.IO.File]::ReadAllLines($EnvFile, [System.Text.Encoding]::UTF8)
    $urlLine  = $envLines | Where-Object { $_ -match '^APP_URL=' } | Select-Object -First 1
    if ($urlLine) { try { $envHost = ([Uri]$urlLine.Substring(8).Trim()).Host } catch { } }
}

# عنوان الشبكة المحلية: عنوان خاص (Private) وحيد على كرت متصل لديه بوابة
$candidates = @(Get-NetIPConfiguration -ErrorAction SilentlyContinue |
    Where-Object { $_.NetAdapter.Status -eq 'Up' -and $_.IPv4DefaultGateway } |
    ForEach-Object { $_.IPv4Address.IPAddress } |
    Where-Object { $_ -match '^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[01])\.)' } |
    Select-Object -Unique)

$lanIp = if ($candidates.Count -eq 1) { $candidates[0] } else { $null }

if ($lanIp -and $envHost -and $envHost -notin @('localhost', '127.0.0.1') -and $envHost -ne $lanIp) {
    Write-Host "  تم كشف تغيّر عنوان الشبكة: $envHost  ->  $lanIp" -ForegroundColor Yellow
    Write-Host '  تحديث الرابط وإعادة توليد الشهادة تلقائياً ...' -ForegroundColor Yellow

    # تحديث APP_URL في ملف .env (بترميز UTF-8 بدون BOM)
    $newLines = foreach ($l in $envLines) {
        if ($l -match '^APP_URL=') { "APP_URL=https://$lanIp/mat3am/public" } else { $l }
    }
    [System.IO.File]::WriteAllLines($EnvFile, $newLines, (New-Object System.Text.UTF8Encoding($false)))

    # إعادة توليد شهادة الخادم موقّعة من نفس الجذر، مع تضمين العنوان الجديد والقديم
    $sanList = @('DNS:localhost', 'IP:127.0.0.1', "IP:$lanIp", "IP:$envHost") | Select-Object -Unique
    $extFile = Join-Path $SslDir 'san.cnf'
    @"
[ext]
basicConstraints=CA:FALSE
keyUsage=digitalSignature,keyEncipherment
extendedKeyUsage=serverAuth
subjectAltName=$($sanList -join ',')
"@ | Set-Content -LiteralPath $extFile -Encoding ASCII

    $openssl = "$Xampp\apache\bin\openssl.exe"
    Push-Location $SslDir
    & $openssl req -new -newkey rsa:2048 -nodes -keyout server.key -out server.csr `
        -subj "/C=DZ/O=Zawia/CN=$lanIp" -config "$Xampp\apache\conf\openssl.cnf" *> $null
    & $openssl x509 -req -in server.csr -CA rootCA.crt -CAkey rootCA.key -CAcreateserial `
        -out server.crt -days 3650 -extfile $extFile -extensions ext *> $null
    Pop-Location

    # إبطال كاش الإعدادات حتى يُقرأ الرابط الجديد
    $phpBin = (Get-Command php -ErrorAction SilentlyContinue).Source
    if (-not $phpBin) { $phpBin = "$Xampp\php\php.exe" }
    Push-Location $AppDir
    & $phpBin artisan optimize:clear *> $null
    Pop-Location

    $certChanged = $true
    Write-Host '  تم تحديث الرابط والشهادة بنجاح' -ForegroundColor Green
}

# ==================== 1/4 : قاعدة البيانات MySQL ====================
if (Get-Process mysqld -ErrorAction SilentlyContinue) {
    Step '1/4' 'قاعدة البيانات MySQL تعمل بالفعل' 'Green'
} else {
    Step '1/4' 'تشغيل قاعدة البيانات MySQL ...' 'Yellow'
    Start-Process -FilePath "$Xampp\mysql\bin\mysqld.exe" `
        -ArgumentList '--defaults-file="C:\xampp\mysql\bin\my.ini"', '--standalone' `
        -WorkingDirectory "$Xampp\mysql" -WindowStyle Minimized
}

# ==================== 2/4 : خادم الويب Apache ====================
if (Get-Process httpd -ErrorAction SilentlyContinue) {
    if ($certChanged) {
        Step '2/4' 'إعادة تشغيل خادم الويب Apache لتطبيق الشهادة الجديدة ...' 'Yellow'
        Get-Process httpd -ErrorAction SilentlyContinue | Stop-Process -Force
        Start-Sleep -Seconds 2
        Start-Process -FilePath "$Xampp\apache\bin\httpd.exe" `
            -ArgumentList '-d', 'C:/xampp/apache' -WindowStyle Minimized
    } else {
        Step '2/4' 'خادم الويب Apache يعمل بالفعل' 'Green'
    }
} else {
    Step '2/4' 'تشغيل خادم الويب Apache ...' 'Yellow'
    Start-Process -FilePath "$Xampp\apache\bin\httpd.exe" `
        -ArgumentList '-d', 'C:/xampp/apache' -WindowStyle Minimized
}

# ==================== 3/4 : انتظار جاهزية الخادم ====================
Step '3/4' 'التحقق من جاهزية الخادم ...' 'Yellow'

$ready    = $false
$deadline = (Get-Date).AddSeconds(25)

while (-not $ready -and (Get-Date) -lt $deadline) {
    try {
        $client = New-Object System.Net.Sockets.TcpClient
        $client.Connect('127.0.0.1', 443)
        $client.Close()
        $ready = $true
    } catch {
        Start-Sleep -Milliseconds 500
    }
}

if ($ready) {
    Write-Host '         الخادم جاهز' -ForegroundColor Green
} else {
    Write-Host '         تحذير: الخادم لم يستجب على المنفذ 443' -ForegroundColor Red
    Write-Host '         راجع السجل: C:\xampp\apache\logs\error.log' -ForegroundColor Red
}

# ==================== 4/4 : مجدول تنبيهات السدادات ====================
$running = Get-CimInstance Win32_Process -Filter "Name='php.exe'" -ErrorAction SilentlyContinue |
           Where-Object { $_.CommandLine -like '*schedule:work*' }

if ($running) {
    Step '4/4' 'مجدول التنبيهات يعمل بالفعل' 'Green'
} else {
    Step '4/4' 'تشغيل مجدول تنبيهات انتهاء السدادات ...' 'Yellow'

    $php = (Get-Command php -ErrorAction SilentlyContinue).Source
    if (-not $php) { $php = "$Xampp\php\php.exe" }

    Start-Process -FilePath $php -ArgumentList 'artisan', 'schedule:work' `
        -WorkingDirectory $AppDir -WindowStyle Minimized
}

# ==================== الرابط ====================
$url     = 'https://192.168.1.5/mat3am/public'
$envFile = Join-Path $AppDir '.env'

if (Test-Path $envFile) {
    $line = Get-Content $envFile | Where-Object { $_ -match '^APP_URL=' } | Select-Object -First 1
    if ($line) { $url = $line.Substring(8).Trim() }
}

$lanHost = ([Uri]$url).Host

Write-Host ''
Write-Host '  ------------------------------------------------------------' -ForegroundColor Cyan
Write-Host '    من هذا الجهاز      : https://localhost/mat3am/public'
Write-Host "    من أجهزة الشبكة    : $url"
Write-Host '  ------------------------------------------------------------' -ForegroundColor Cyan

# تنبيه احتياطي إذا تعذّر تحديد عنوان الشبكة تلقائياً
if (-not $certChanged -and $lanHost -ne 'localhost' -and $lanHost -ne '127.0.0.1' -and $lanIp -ne $lanHost) {
    Write-Host ''
    Write-Host "  تنبيه: تعذّر تحديد عنوان الشبكة تلقائياً، والرابط الحالي ($lanHost) قد لا يكون صحيحاً." -ForegroundColor Red
    Write-Host '        تحقّق من اتصال الشبكة، أو ثبّت عنوان IP ثابتاً للجهاز.' -ForegroundColor Red
}

Write-Host ''
Write-Host '  جارٍ فتح المتصفح ...' -ForegroundColor Yellow
Start-Process $url

Write-Host ''
Write-Host '  المنصة تعمل الآن. يمكنك إغلاق هذه النافذة.' -ForegroundColor Green
Write-Host ''

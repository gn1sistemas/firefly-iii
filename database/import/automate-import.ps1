#
# Execução para realizar a importação
#
# php artisan firefly:create-import file.csv config.json --start --token=d474b2e93e1351e6471f1081146ae793

$initialBasePath = "D:\gn1\qif2csv\result-2020-02"

$paths = Get-ChildItem -Path "$initialBasePath\csv" | Sort-Object -Property Length

#$fileNameAccepteds = @(
#    "16.csv"
#)

$stopWatch = New-Object System.Diagnostics.Stopwatch
$stopWatch.Start()

foreach($path in $paths) {
    #if(-not $fileNameAccepteds.Contains($path.Name)){        
    #    continue
    #}

    $fileName = $path.Name.ToString().Replace(".csv", ".json")
    $logFileName = $path.Name.ToString().Replace(".csv", ".txt")
    $cvsPath = $path.FullName
    
    $pathJson = "$initialBasePath\json\$fileName"

    $phpArguments =  "artisan firefly:create-import $cvsPath $pathJson --start --token=3d673dde2f65bd579c2dd175d93e2a89"

    Write-Host("php $phpArguments")

    $internalStopWatch = New-Object System.Diagnostics.Stopwatch
    $internalStopWatch.Start()
    
    $ps = new-object System.Diagnostics.Process
    $ps.StartInfo.Filename = "php"
    $ps.StartInfo.Arguments = $phpArguments
    $ps.StartInfo.RedirectStandardOutput = $True
    $ps.StartInfo.UseShellExecute = $false
    $ps.start()
    $ps.WaitForExit()

    $internalStopWatch.Stop()

    $internalSecondsElapsed = $internalStopWatch.Elapsed.TotalSeconds
    
    Write-Host("Tempo: $internalSecondsElapsed segundos")
    Write-Host("========================================")
    
    $Out = $ps.StandardOutput.ReadToEnd();

    Set-Content -Path "$initialBasePath\$logFileName" -Value "$Out"
}

$stopWatch.Stop()

$secondsElapsed = $stopWatch.Elapsed.TotalSeconds

Write-Host("Fim. Tempo levado: $secondsElapsed segundos")
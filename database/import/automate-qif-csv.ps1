# 
# Algoritmo para transformar todos os arquivos QIF da ABORL-CCF em formato CSV
# 

Import-Module -Name ".\Convert-QifToCsv.psm1"

$initialBasePath = "D:\gn1\qif2csv\result-2020-02"
$qifPath = "D:\gn1\qif2csv\aborlccf\qif"

New-Item -Path "$initialBasePath\csv" -ItemType "directory"
New-Item -Path "$initialBasePath\json" -ItemType "directory"

$paths = Get-ChildItem -Path $qifPath

foreach($path in $paths) {
    
    # QIF
    $fileName = $path.Name.ToString().Replace(".qif", ".csv")
    $outputPath = "$initialBasePath\csv\$fileName"    

    # Generate the CSV file
    Convert-QifToCsv -i $path.FullName -o $outputPath

    # JSON     
    $pathTemplate = ".\template.json"

    $content = Get-Content -Path $pathTemplate

    $id = $path.Name.ToString().Replace(".qif", "")
    $content = $content.Replace("1", $id)

    $jsonFile = $path.Name.ToString().Replace(".qif", ".json")
    
    # Generate the JSON file
    Set-Content -Path "$initialBasePath\json\$jsonFile" -Value "$content"
}
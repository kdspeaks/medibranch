<?php

$file = 'C:/Users/kdutt/Downloads/Sanjiban Medicine Master Latest.xlsx';

$zip = new ZipArchive();
if ($zip->open($file) === true) {
    // Read shared strings
    $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
    $sharedStrings = [];
    if ($sharedStringsXml) {
        $xml = simplexml_load_string($sharedStringsXml);
        foreach ($xml->si as $si) {
            $sharedStrings[] = (string) $si->t;
        }
    }

    // Read first sheet
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetXml) {
        $xml = simplexml_load_string($sheetXml);
        $headers = [];
        $firstRow = [];
        
        $rowCount = 0;
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $v = (string) $cell->v;
                if (isset($cell['t']) && (string)$cell['t'] === 's') {
                    $v = $sharedStrings[(int)$v] ?? $v;
                }
                $rowData[] = $v;
            }
            if ($rowCount === 0) {
                $headers = $rowData;
            } elseif ($rowCount === 1) {
                $firstRow = $rowData;
            } else {
                break;
            }
            $rowCount++;
        }

        echo "HEADERS:\n";
        print_r($headers);
        echo "\nFIRST ROW:\n";
        print_r($firstRow);
    } else {
        echo "Could not find sheet1.xml\n";
    }
    $zip->close();
} else {
    echo "Failed to open zip file\n";
}

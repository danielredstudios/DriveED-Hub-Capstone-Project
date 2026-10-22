<?php

namespace App\Support;

use ZipArchive;

class SessionReportWorkbook
{
    private function xml(string $text): string
    {
        $text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text) ?? '';
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    public function build(array $data): string
    {
        $headers = ['Date & Time', 'Student', 'Course', 'Instructor', 'Lesson Type', 'Hours', 'Status', 'Attendance', 'Grade', 'Notes', 'Instructor Feedback', 'Student Feedback', 'Skills Practiced', 'Record Type'];
        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0" showGridLines="0"><pane ySplit="6" topLeftCell="A7" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>';
        foreach ([14, 26, 32, 26, 18, 10, 16, 18, 12, 50, 50, 50, 36, 24] as $i => $width) {
            $sheet .= '<col min="'.($i + 1).'" max="'.($i + 1).'" width="'.$width.'" customWidth="1"/>';
        }
        $sheet .= '</cols><sheetData>';
        $rowNumber = 0;
        $write = function (array $values, int $style, bool $numericHours = false) use (&$sheet, &$rowNumber) {
            $rowNumber++;
            $height = $rowNumber === 1 ? 32 : ($rowNumber <= 5 ? 22 : 32);
            if ($numericHours) {
                $widths = [14, 26, 32, 26, 18, 10, 16, 18, 12, 50, 50, 50, 36, 24];
                foreach ($values as $i => $value) {
                    $lines = 0;
                    foreach (explode("\n", (string) $value) as $line) {
                        $lines += max(1, (int) ceil(mb_strlen($line) / ($widths[$i] - 2)));
                    }
                    $height = max($height, min(409, $lines * 15 + 8));
                }
            }
            $sheet .= '<row r="'.$rowNumber.'" ht="'.$height.'" customHeight="1">';
            foreach ($values as $i => $value) {
                $column = chr(65 + $i);
                if ($numericHours && $i === 5) {
                    $sheet .= '<c r="'.$column.$rowNumber.'" s="4"><v>'.(float) $value.'</v></c>';
                } else {
                    // Inline strings preserve user-entered notes without executing Excel formulas.
                    $sheet .= '<c r="'.$column.$rowNumber.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $value).'</t></is></c>';
                }
            }
            $sheet .= '</row>';
        };
        $write([$data['school']->name.' | Training Session History'], 1);
        $write(['Prepared for: '.$data['preparedFor']], 0);
        $write(['Period: '.$data['period'].' | Generated: '.$data['generatedAt']->timezone($data['school']->timezone ?? 'Asia/Manila')->format('Y-m-d H:i T')], 0);
        $summary = [];
        foreach ($data['summary'] as $label => $value) { $summary[] = $label.': '.$value; }
        $write([implode('   |   ', $summary)], 0);
        $write(['Shows booked driving lessons and completed training sessions. Hours are shown separately for each record.'], 0);
        $write($headers, 2);
        foreach ($data['rows'] as $i => $row) {
            $write(array_values($row), $i % 2 === 0 ? 0 : 3, true);
        }
        $sheet .= '</sheetData><autoFilter ref="A6:N'.max(6, $rowNumber).'"/><mergeCells count="5">';
        for ($i = 1; $i <= 5; $i++) { $sheet .= '<mergeCell ref="A'.$i.':N'.$i.'"/>'; }
        $sheet .= '</mergeCells><pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/><pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/></worksheet>';
        $styles = '<?xml version="1.0"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="3"><font><sz val="11"/><name val="Calibri"/><color rgb="FF243247"/></font><font><b/><sz val="18"/><name val="Calibri"/><color rgb="FF173C54"/></font><font><b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font></fonts><fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF173C54"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF0F5F8"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="5"><xf fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/><xf fontId="2" fillId="2" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment wrapText="1"/></xf><xf fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf fontId="0" fillId="0" borderId="0" numFmtId="2" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
        $files = [
            '[Content_Types].xml' => '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Session report" sheetId="1" r:id="rId1"/></sheets><definedNames><definedName name="_xlnm.Print_Titles" localSheetId="0">\'Session report\'!$1:$6</definedName></definedNames></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => $styles, 'xl/worksheets/sheet1.xml' => $sheet,
        ];
        $path = tempnam(sys_get_temp_dir(), 'session-report-');
        try {
            $zip = new ZipArchive;
            if ($zip->open($path, ZipArchive::OVERWRITE) !== true) { throw new \RuntimeException('Unable to create workbook.'); }
            foreach ($files as $name => $xml) { $zip->addFromString($name, $xml); }
            $zip->close();
            return file_get_contents($path);
        } finally {
            if ($path && is_file($path)) { unlink($path); }
        }
    }
}

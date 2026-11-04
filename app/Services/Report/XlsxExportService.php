<?php

namespace App\Services\Report;

use RuntimeException;
use XMLWriter;
use ZipArchive;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class XlsxExportService
{
    public function download(string $filename, string $sheetName, array $rows): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'foodflix-report-');
        $zip = new ZipArchive();

        if ($path === false || $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível preparar o arquivo Excel.');
        }

        try {
            $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
            $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheetName));
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($rows));
            $zip->close();
        } catch (\Throwable $exception) {
            $zip->close();
            @unlink($path);
            throw $exception;
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbookXml(string $sheetName): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8', 'yes');
        $writer->startElement('workbook');
        $writer->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $writer->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $writer->startElement('sheets');
        $writer->startElement('sheet');
        $writer->writeAttribute('name', substr($sheetName, 0, 31));
        $writer->writeAttribute('sheetId', '1');
        $writer->writeAttribute('r:id', 'rId1');
        $writer->endElement();
        $writer->endElement();
        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>';
    }

    private function worksheetXml(array $rows): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8', 'yes');
        $writer->startElement('worksheet');
        $writer->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $writer->startElement('sheetViews');
        $writer->startElement('sheetView');
        $writer->writeAttribute('workbookViewId', '0');
        $writer->startElement('pane');
        $writer->writeAttribute('ySplit', '1');
        $writer->writeAttribute('topLeftCell', 'A2');
        $writer->writeAttribute('activePane', 'bottomLeft');
        $writer->writeAttribute('state', 'frozen');
        $writer->endElement();
        $writer->endElement();
        $writer->endElement();

        $writer->startElement('sheetData');
        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $writer->startElement('row');
            $writer->writeAttribute('r', (string) $excelRow);

            foreach (array_values($row) as $columnIndex => $value) {
                $coordinate = $this->columnName($columnIndex + 1).$excelRow;
                $writer->startElement('c');
                $writer->writeAttribute('r', $coordinate);

                if (is_int($value) || is_float($value)) {
                    $writer->writeElement('v', (string) $value);
                } else {
                    $writer->writeAttribute('t', 'inlineStr');
                    $writer->startElement('is');
                    $writer->writeElement('t', (string) ($value ?? ''));
                    $writer->endElement();
                }

                $writer->endElement();
            }

            $writer->endElement();
        }
        $writer->endElement();

        if (count($rows) > 0 && count($rows[0]) > 0) {
            $writer->startElement('autoFilter');
            $writer->writeAttribute('ref', 'A1:'.$this->columnName(count($rows[0])).count($rows));
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }

    private function columnName(int $column): string
    {
        $name = '';
        while ($column > 0) {
            $column--;
            $name = chr(65 + ($column % 26)).$name;
            $column = intdiv($column, 26);
        }

        return $name;
    }
}

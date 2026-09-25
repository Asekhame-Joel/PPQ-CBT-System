<?php

namespace App\QuestionImports;

use RuntimeException;
use ZipArchive;

class QuestionImportTemplate
{
    public function aikenText(): string
    {
        return <<<'TEXT'
What is the capital of Nigeria?
A. Lagos
B. Abuja
C. Kano
D. Ibadan
ANSWER: B
EXPLANATION: Abuja is the capital city of Nigeria.

Which data structure follows First In First Out?
A. Stack
B. Tree
C. Queue
D. Graph
ANSWER: C
EXPLANATION: A queue processes items in first-in, first-out order.
TEXT;
    }

    public function wordDocument(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'question-template-');

        if ($path === false) {
            throw new RuntimeException('Unable to create the Word template.');
        }

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            @unlink($path);

            throw new RuntimeException('Unable to create the Word template archive.');
        }

        try {
            $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $zip->addFromString('_rels/.rels', $this->packageRelationshipsXml());
            $zip->addFromString('docProps/core.xml', $this->corePropertiesXml());
            $zip->addFromString('docProps/app.xml', $this->appPropertiesXml());
            $zip->addFromString('word/document.xml', $this->documentXml());
            $zip->addFromString('word/styles.xml', $this->stylesXml());
            $zip->addFromString('word/_rels/document.xml.rels', $this->documentRelationshipsXml());
        } finally {
            $zip->close();
        }

        try {
            $contents = file_get_contents($path);

            if ($contents === false) {
                throw new RuntimeException('Unable to read the Word template.');
            }

            return $contents;
        } finally {
            @unlink($path);
        }
    }

    private function documentXml(): string
    {
        $rows = [
            ['QUESTION', 'What is the capital of Nigeria?'],
            ['A', 'Lagos'],
            ['B', 'Abuja'],
            ['C', 'Kano'],
            ['D', 'Ibadan'],
            ['ANSWER', 'B'],
            ['EXPLANATION', 'Abuja is the capital city of Nigeria.'],
        ];
        $tableRows = '';

        foreach ($rows as [$label, $value]) {
            $tableRows .= $this->tableRowXml($label, $value);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'
            .'<w:p><w:pPr><w:pStyle w:val="Title"/></w:pPr><w:r><w:t>Question Import Template</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Use one table for each question. Keep the field names in the left column unchanged. Duplicate the complete table to add another question.</w:t></w:r></w:p>'
            .'<w:tbl><w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders>'
            .'<w:top w:val="single" w:sz="4" w:color="D9D9D9"/><w:left w:val="single" w:sz="4" w:color="D9D9D9"/>'
            .'<w:bottom w:val="single" w:sz="4" w:color="D9D9D9"/><w:right w:val="single" w:sz="4" w:color="D9D9D9"/>'
            .'<w:insideH w:val="single" w:sz="4" w:color="D9D9D9"/><w:insideV w:val="single" w:sz="4" w:color="D9D9D9"/>'
            .'</w:tblBorders><w:tblCellMar><w:top w:w="120" w:type="dxa"/><w:left w:w="140" w:type="dxa"/>'
            .'<w:bottom w:w="120" w:type="dxa"/><w:right w:w="140" w:type="dxa"/></w:tblCellMar></w:tblPr>'
            .'<w:tblGrid><w:gridCol w:w="2600"/><w:gridCol w:w="6400"/></w:tblGrid>'
            .$tableRows.'</w:tbl>'
            .'<w:p><w:pPr><w:spacing w:before="240"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Rules</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Each table requires QUESTION, at least two lettered options, and ANSWER. EXPLANATION is optional. ANSWER must contain only the correct option letter.</w:t></w:r></w:p>'
            .'<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1080" w:right="1080" w:bottom="1080" w:left="1080"/></w:sectPr>'
            .'</w:body></w:document>';
    }

    private function tableRowXml(string $label, string $value): string
    {
        return '<w:tr>'
            .'<w:tc><w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:fill="EAF2F8"/><w:vAlign w:val="center"/></w:tcPr>'
            .'<w:p><w:r><w:rPr><w:b/></w:rPr><w:t>'.htmlspecialchars($label, ENT_XML1).'</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="6400" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>'
            .'<w:p><w:r><w:t>'.htmlspecialchars($value, ENT_XML1).'</w:t></w:r></w:p></w:tc>'
            .'</w:tr>';
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'</Types>';
    }

    private function packageRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private function documentRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="22"/><w:color w:val="000000"/></w:rPr></w:rPrDefault>'
            .'<w:pPrDefault><w:pPr><w:spacing w:after="160" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults>'
            .'<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/>'
            .'<w:pPr><w:spacing w:after="240"/></w:pPr><w:rPr><w:b/><w:sz w:val="32"/><w:color w:val="000000"/></w:rPr></w:style>'
            .'</w:styles>';
    }

    private function corePropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            .'xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>Question Import Template</dc:title><dc:creator>Exam Practice</dc:creator></cp:coreProperties>';
    }

    private function appPropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
            .'<Application>Exam Practice</Application></Properties>';
    }
}

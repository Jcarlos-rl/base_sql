<?php
    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;

    class LibraryPhpSpreadSheet{

        public function readFile($archivo, $pag){
            $spreadSheet = IOFactory::load($archivo);
            $hoja = $spreadSheet->getSheet($pag);

            $count = 0;
            foreach($hoja->getRowIterator() as $fila){
                foreach($fila->getCellIterator() as $celda){
                    $data[$count][] = $celda->getValue();
                } 
                $count++;
            }

            return $data;
        }
    }
?>
<?php
    class ResourcesFunctions{

        public function saveFile($file, $pathSave){
            $fileName = $file['name'];
            $type = pathinfo($file['name'], PATHINFO_EXTENSION);

            if(!is_dir($pathSave)){
                mkdir($pathSave, 0777, true);
            }

            $uploadedFilename = $pathSave .'/'. $fileName;
            $fileInfoArr = [
                'name' => $fileName,
                'type' => $type,
                'size' => $file['size'],
                'path' => $uploadedFilename
            ];
            if(move_uploaded_file($file['tmp_name'], $uploadedFilename)){
                $response = [
                    'status' => true,
                    'data' => $fileInfoArr
                ];
            }else{
                $response = [
                    'status' => false,
                    'errorMessage' => 'Lo sentimos, no se pudo guardar el archivo.'
                ];
            }
    
            return $response;
        }

        public function createSlug($text){
            $slug = trim($text);
            $slug = $this->removeAccents($slug);
            $slug = preg_replace('/[^a-zA-Z0-9\- ]/', '', $slug);
            $slug = preg_replace('/[ ]/', '_', $slug);
            $slug = strtolower($slug);
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            return $slug;
        }

        public function removeAccents($text){
            $replace = array(
                'á' => 'a',
                'é' => 'e',
                'í' => 'i',
                'ó' => 'o',
                'ú' => 'u',
                'ñ' => 'n',
                'ü' => 'u',
                'ç' => 'c',
                'Á' => 'A',
                'É' => 'E',
                'Í' => 'I',
                'Ó' => 'O',
                'Ú' => 'U',
                'Ñ' => 'N',
                'Ü' => 'U',
                'Ç' => 'C'
            );
            $str = strtr($text, $replace);
            return $str;
        }
    }
?>
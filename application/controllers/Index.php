<?php
    class Index extends Controller{

        private $rulesHeadFle = [
            'folio_de_solicitud' => [
                'required' => false,
                'type' => 'number',
                'name' => 'Folio de Solicitud'
            ],
            'referencia' => [
                'required' => true,
                'type' => 'number',
                'name' => 'Referencia'
            ],
            'boleta' => [
                'required' => false,
                'type' => 'string',
                'name' => 'Boleta'
            ],
            'correo' => [
                'required' => true,
                'type' => 'string',
                'name' => 'Correo'
            ]
        ];

        public function __construct(){
            $this->Resources = new ResourcesFunctions();
            $this->SpreadSheet = new LibraryPhpSpreadSheet();
        }
        
        public function index(){
            $path = BASE_PATH . 'public/media/files';
            $file = $_FILES['file'];
            $pag = (isset($_REQUEST['pag']) && is_numeric($_REQUEST['pag'])) ? $_REQUEST['pag'] : 1;
            $folio_seguimiento = intval($_REQUEST['folioSeguimiento']);

            $saveFile = $this->Resources->saveFile($file, $path);
            
            if(!$saveFile['status']){
                http_response_code(500);
                echo json_encode($saveFile['errorMessage']);
                return;
            }

            $dataFile = $this->SpreadSheet->readFile($saveFile['data']['path'], $pag);
            $validateHeadFile = $this->validateHeadFile($dataFile[0]);

            if(!$validateHeadFile['status']){
                http_response_code(400);
                echo json_encode($validateHeadFile['errorMessage']);
                return;
            }

            $validateBodyFile = $this->validateBodyFile($validateHeadFile['data'], $dataFile);

            if(!$validateBodyFile['status']){
                http_response_code(400);
                echo json_encode(
                    [
                        'error' => $validateBodyFile['errorMessage'],
                        'errors' => $validateBodyFile['errors']
                    ]
                );
                return;
            }

            foreach ($validateBodyFile['data'] as $key => $row) {
                $sqlQuery = $this->sqlQuery(12231);
                if(count($sqlQuery)>1){
                    foreach ($sqlQuery as $key => $row) {
                        if(isset($data[$row['codigoActivacion']])){
                            $data[$row['codigoActivacion']] += $row['cantidad'];
                        }else{
                            $data[$row['codigoActivacion']] = $row['cantidad'];
                        }
                    }

                    foreach ($data as $key => $value) {
                        $arr[] = [
                            "codigo" => $key,
                            "valor" => "1"
                        ];

                        $arr[] = [
                            "codigo" => $sqlQuery[0]['codigoCantidad'],
                            "valor" => strval($value)
                        ];
                    }
                }else{
                    $arr = [
                        [
                            "codigo" => $sqlQuery[0]['codigoActivacion'],
                            "valor" => "1"
                        ],
                        [
                            "codigo" => $sqlQuery[0]['codigoCantidad'],
                            "valor" => strval($sqlQuery[0]['cantidad'])
                        ]
                    ];
                }

                $num_servicio = $sqlQuery[0]['num_servicio'];

                $cadenaNombre = explode(',',$sqlQuery[0]['nombreC']);
                $nombre = trim(str_replace('"','',str_replace('nombre: ','',$cadenaNombre[0])));
                $apaterno = trim(str_replace('"','',str_replace('"apaterno": ','',$cadenaNombre[1])));
                $amaterno = trim(str_replace('"','',str_replace('"amaterno": ','',$cadenaNombre[2])));

                $fecha = Date("d/m/Y");

                $json = [
                    "token" => "309F1E78-E02E-44D7-BADF-200521D1E716",
                    "folioSeguimiento" => $folio_seguimiento+1,
                    "num_servicio" => $num_servicio,
                    "nombre" => $nombre, 
                    "apaterno" => $apaterno, 
                    "amaterno" => $amaterno,
                    "rfc" => "",
                    "curp" => "",
                    "estado" => "PUEBLA",
                    "municipio" => "",
                    "poblacion" => "",
                    "colonia" => "",
                    "calle" => "",
                    "numero" => "",
                    "tipoPersona" => 1,
                    "cp" => "",
                    "fecha" => $fecha,
                    "fechaVigencia" => "2024-06-30",
                    "regimenFiscal" => "",
                    "usoCFDI" => "",
                    "cpFiscal" => 0,
                    "beneficiario" => "",
                    "variables" => $arr
                ];

                $fetchJSON = $this->fetchJSON($json);

                if(!$fetchJSON['status']){
                    http_response_code(500);
                    echo json_encode($fetchJSON['errorMessage']);
                    return;
                }

                $file_save = $this->saveFileInDirectory($fetchJSON['data']['lineaCaptura'], $row['correo'], $fetchJSON['data']['ordenCobro']);

                $data[$key] = $file_save;
            }

            echo json_encode($data);
            return;
        }

        protected function validateHeadFile($head){
            $notInArray = [];
            $orderHead = [];

            foreach ($head as $value) {
                $headSlug[] = ($value == NULL) ? $value : $this->Resources->createSlug($value);
            }

            foreach ($this->rulesHeadFle as $key => $value) {
                if($value['required'] && !in_array($key, $headSlug)){
                    $notInArray[] = $value['name'];
                }else{
                    $index = array_search($key, $headSlug);

                    if(is_int($index)){
                        $orderHead[$key] = $index;
                    }
                }
            }

            if(count($notInArray)>0){
                $response = [
                    'status' => false,
                    'errorMessage' => 'Lo sentimos, el archivo no contine la columna: ' . implode(', ', $notInArray) . ' que son obligatorios.'
                ];

                return $response;
            }

            $response = [
                'status' => true,
                'data' => $orderHead
            ];

            return $response;
        }

        protected function validateBodyFile($order, $data){
            unset($data[0]);
            $errors = [];

            $count = 0;
            foreach ($data as $row) {
                foreach ($order as $key => $value) {
                    if($this->rulesHeadFle[$key]['required']){
                        if($row[$value] == null || $row[$value] == '' || trim($row[$value]) == ''){
                            $errors[$count][] = 'El campo ' . $this->rulesHeadFle[$key]['name'] . ' no puede estar vacio.';
                            continue;
                        }
                    }
                    if($row[$value] != null){
                        switch ($this->rulesHeadFle[$key]['type']) {
                            case 'number':
                                if(is_numeric($row[$value])){
                                    $dataRows[$count][$key] = $row[$value];
                                }else{
                                    $errors[$count][] = 'El campo ' . $this->rulesHeadFileExcel[$key]['name'] . ' debe de ser un valor númerico.';
                                }
                                break;
                            default:
                                $dataRows[$count][$key] = (is_numeric($row[$value])) ? $row[$value] : trim($row[$value]);
                                break;
                        }
                    }
                }
                $count++;
            }
            
            if(count($errors) > 0){
                $response = [
                    'status' => false,
                    'errorMessage' => 'Lo sentimos, el documentos contiene valores erroneos',
                    'errors' => $errors
                ];

                return $response;
            }

            $response = [
                'status' => true,
                'data' => $dataRows
            ];

            return $response;
        }

        protected function sqlQuery($referencia){

            $database = [
                [
                    [
                        'dsReferencia' => 13325372607939500000,
                        'dsClave' => 23124000529046400,
                        'CONCEPTO' => 'DIF. POR INSCRIPCION CONSTITUCION INDIVIDUALIZACION CREDITO O DIVISION DE CREDITO',
                        'cantidad' => 1,
                        'CTA# ORIGEN' => 13779,
                        'CTA# DIF#' => 16324,
                        'DIFERENCIA' => 150,
                        'codigoActivacion' => 'V4406',
                        'codigoCantidad' => 'V0001',
                        'num_servicio' => 1378,
                        'nombreC' => 'nombre: "ATZAYACATL", "apaterno": "GUERRA", "amaterno": "MORALES",',
                        'dsRFC' => NULL,
                        'fnIdTipoPersonaFiscal' => NULL,
                        'fnIdEntidad_Federativa' => NULL,
                        'dsColonia' => NULL,
                        'dsCalle' => NULL,
                        'dsNo_Exterior' => NULL,
                        'dsCP' => NULL
                    ]
                ],
                [
                    [
                        'dsReferencia' => 13325372607939500000,
                        'dsClave' => 23124000529046400,
                        'CONCEPTO' => 'DIF. POR INSCRIPCION CONSTITUCION INDIVIDUALIZACION CREDITO O DIVISION DE CREDITO',
                        'cantidad' => 1,
                        'CTA# ORIGEN' => 13779,
                        'CTA# DIF#' => 16324,
                        'DIFERENCIA' => 150,
                        'codigoActivacion' => 'V4406',
                        'codigoCantidad' => 'V0001',
                        'num_servicio' => 1378,
                        'nombreC' => 'nombre: "ATZAYACATL", "apaterno": "GUERRA", "amaterno": "MORALES",',
                        'dsRFC' => NULL,
                        'fnIdTipoPersonaFiscal' => NULL,
                        'fnIdEntidad_Federativa' => NULL,
                        'dsColonia' => NULL,
                        'dsCalle' => NULL,
                        'dsNo_Exterior' => NULL,
                        'dsCP' => NULL
                    ],
                    [
                        'dsReferencia' => 13325372607939500000,
                        'dsClave' => 23124000529046400,
                        'CONCEPTO' => 'DIF. POR INSCRIPCION CONSTITUCION INDIVIDUALIZACION CREDITO O DIVISION DE CREDITO',
                        'cantidad' => 1.05,
                        'CTA# ORIGEN' => 13779,
                        'CTA# DIF#' => 16324,
                        'DIFERENCIA' => 150,
                        'codigoActivacion' => 'V4406',
                        'codigoCantidad' => 'V0001',
                        'num_servicio' => 1378,
                        'nombreC' => 'nombre: "ATZAYACATL", "apaterno": "GUERRA", "amaterno": "MORALES",',
                        'dsRFC' => NULL,
                        'fnIdTipoPersonaFiscal' => NULL,
                        'fnIdEntidad_Federativa' => NULL,
                        'dsColonia' => NULL,
                        'dsCalle' => NULL,
                        'dsNo_Exterior' => NULL,
                        'dsCP' => NULL
                    ],
                    [
                        'dsReferencia' => 13325372607939500000,
                        'dsClave' => 23124000529046400,
                        'CONCEPTO' => 'DIF. POR INSCRIPCION CONSTITUCION INDIVIDUALIZACION CREDITO O DIVISION DE CREDITO',
                        'cantidad' => 1,
                        'CTA# ORIGEN' => 13779,
                        'CTA# DIF#' => 16324,
                        'DIFERENCIA' => 150,
                        'codigoActivacion' => 'V3567',
                        'codigoCantidad' => 'V0001',
                        'num_servicio' => 1378,
                        'nombreC' => 'nombre: "ATZAYACATL", "apaterno": "GUERRA", "amaterno": "MORALES",',
                        'dsRFC' => NULL,
                        'fnIdTipoPersonaFiscal' => NULL,
                        'fnIdEntidad_Federativa' => NULL,
                        'dsColonia' => NULL,
                        'dsCalle' => NULL,
                        'dsNo_Exterior' => NULL,
                        'dsCP' => NULL
                    ]
                ],
                [
                    [
                        'dsReferencia' => 13325372607939500000,
                        'dsClave' => 23124000529046400,
                        'CONCEPTO' => 'DIF. POR INSCRIPCION CONSTITUCION INDIVIDUALIZACION CREDITO O DIVISION DE CREDITO',
                        'cantidad' => 1,
                        'CTA# ORIGEN' => 13779,
                        'CTA# DIF#' => 16324,
                        'DIFERENCIA' => 150,
                        'codigoActivacion' => 'V4406',
                        'codigoCantidad' => 'V0001',
                        'num_servicio' => 1378,
                        'nombreC' => 'nombre: "ATZAYACATL", "apaterno": "GUERRA", "amaterno": "MORALES",',
                        'dsRFC' => NULL,
                        'fnIdTipoPersonaFiscal' => NULL,
                        'fnIdEntidad_Federativa' => NULL,
                        'dsColonia' => NULL,
                        'dsCalle' => NULL,
                        'dsNo_Exterior' => NULL,
                        'dsCP' => NULL
                    ],
                    [
                        'dsReferencia' => 13325372607939500000,
                        'dsClave' => 23124000529046400,
                        'CONCEPTO' => 'DIF. POR INSCRIPCION CONSTITUCION INDIVIDUALIZACION CREDITO O DIVISION DE CREDITO',
                        'cantidad' => 100.05,
                        'CTA# ORIGEN' => 13779,
                        'CTA# DIF#' => 16324,
                        'DIFERENCIA' => 150,
                        'codigoActivacion' => 'V4406',
                        'codigoCantidad' => 'V0001',
                        'num_servicio' => 1378,
                        'nombreC' => 'nombre: "ATZAYACATL", "apaterno": "GUERRA", "amaterno": "MORALES",',
                        'dsRFC' => NULL,
                        'fnIdTipoPersonaFiscal' => NULL,
                        'fnIdEntidad_Federativa' => NULL,
                        'dsColonia' => NULL,
                        'dsCalle' => NULL,
                        'dsNo_Exterior' => NULL,
                        'dsCP' => NULL
                    ]
                ]
            ];

            //return $database[rand(0, 2)];
            return $database[0];
        }

        protected function fetchJSON($json){

            $dummy_api = [
                'servicio' => '1378 - COMPLEMENTARIAS POR DIFERENCIA RPP',
                'folioSeguimiento' => '6002842569',
                'lineaCaptura' => strval(rand()),
                'fechaVencimiento' => '2024-06-30',
                'montoTotal' => 150.0,
                /* 'ordenCobro' => 'https://serviciospue.puebla.gob.mx/SialWsEpuebla/rest/recaudacion/ordenCobro/fc0b2eedb29e90396ce2a8cb8f3830ba', */
                'ordenCobro' => base_url . 'public/media/files/formatoDePago.pdf',
                'numFol' => 50804585,
                'codigo' => 0,
                'mensaje' => 'Tramite calculaco correctamente',
                'tramites' => [
                    [
                        'ejercicio' => 2024,
                        'conceptos' => [
                            'cuenta' => '16324 - 0TF TNSCRTPCTON. CONsTTTucTON, nTVTSTON De crentTO GapaNTTa htpoTcapTa',
                            'monto' => 150.0,
                            'tarifa' => 'Numero de copias*150',
                            'condicion_activacion' => 'RPP_16324==1',
                            'codigo' => 0
                        ]
                    ]
                ]
            ];

            $response = [
                'status' => true,
                'data' => $dummy_api
            ];

            return $response;

            $api_url = 'https://serviciospue.puebla.gob.mx/SialWsEpuebla/rest/recaudacion/calculo/';

            $options = [
                CURLOPT_URL => $api_url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($json),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($json))
                ]
            ];

            $curl = curl_init();
            curl_setopt_array($curl, $options);
            $response_api = curl_exec($curl);
            curl_close($curl);

            if ($response_api === false) {
                $response = [
                    'status' => false,
                    'errorMessage' => curl_error($curl)
                ];
            } else {
                $response = [
                    'status' => true,
                    'data' => $response_api
                ];
            }

            return $response;
        }

        protected function saveFileInDirectory($lineaCaptura, $email, $ordenCobro){
            $date = Date('d-m-Y');
            $path = BASE_PATH . 'public/media/documentos/' . $date . '/' . $email;
            $nameFile = $lineaCaptura . '.pdf';

            if(!is_dir($path)){
                mkdir($path, 0777, true);
            }

            if(file_put_contents($path . '/' . $nameFile, file_get_contents($ordenCobro))){
                return true;
            }else{
                return false;
            }
        }
    }
?>
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LotesService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class LotesController extends Controller
{

    private $LotesService;

    function __construct(LotesService $LotesService)
    {
        $this->LotesService = $LotesService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     
    public function agregar_p1($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->LotesService->agregar_p1($id, $data['nombre'], $data['id_empresa'], $data['tipo_facturacion'], $data['id_campana'], $data['id_periodo'], $data['vencimiento1'], $data['vencimiento2'], $data['vencimiento3'], $data['id_usuario'], $data['interes']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El Lote que intenta generar, ya se encuentra activo en el sistema';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]);
               
            }

    }

    public function periodos_libres($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->periodos_libres($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }

    public function periodos($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->periodos($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }

    public function modificar_p1($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->modificar_p1($id, $data['id'], $data['nombre'], $data['id_empresa'], $data['tipo_facturacion'], $data['id_campana'], $data['id_periodo'], $data['vencimiento1'], $data['vencimiento2'], $data['vencimiento3'], $data['id_usuario'], $data['interes']);
        if($informe=='error')
            {
                $Mensaje='Atención: Los datos que intenta modificar son coincidentes con otro lote existente';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                if($informe=='error0')
                    {
                        $Mensaje='Atención: El lote ya se encuentra confirmado, por lo que no se pueden realizar modificaciones';
                        return response()->json([
                            'message' => $Mensaje,
                        
                        ], 300);
                    }
                else
                    {
                        return response()->json([
                            'success' => true,
                            'data'    => $informe,
                            'messages' => ''
                        ]);
                    }
                
                
                
               
            }

    }

    public function ver_p1($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->ver_p1($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }

    public function borrar($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->borrar($id, $data['id'], $data['id_usuario']);
        if($informe=='error')
            {
                $Mensaje='Atención: El Lote de Facturación no se puede eliminar por encontrarse cerrado y/o publicado. Contáctese con Mesa de ayuda.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]);
               
            } 

    }

    public function listado($id)
    {
        $data = $this->LotesService->listado($id);
        return response()->json([
            'success' => true,
            'data'    => $data,
            'messages' => ''
        ]);
    }

    /*
    public function agregar_p2($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->agregar_p2($id, $data['id_lote'], $data['conceptos']);
        if($informe=='error')
            {
                $Mensaje='Atención: La segunda etapa del lote de facturación ya se encuntra generada. Verifique';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]);
               
            }

    }
*/
    
    public function modificar_p2($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->modificar_p2($id, $data['id'], $data['conceptos']);
        if($informe=='error')
            {
                $Mensaje='Atención: Los datos que intenta modificar son coincidentes con otro lote existente';
                return response()->json([
                    'message' => mb_convert_encoding($Mensaje, 'UTF-8', 'UTF-8'),
                           
                   
                ], 300);
            }
        else
            {
                if($informe=='error0')
                    {
                        $Mensaje='Atención: El lote ya se encuentra confirmado, por lo que no se pueden realizar modificaciones';
                        return response()->json([
                            'message' => mb_convert_encoding($Mensaje, 'UTF-8', 'UTF-8'),
                           
                        
                        ], 300);
                    }
                else
                    {
                        return response()->json([
                            'success' => true,
                            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                            'messages' => ''
                        ]);
                    }
                
                
                
               
            }

    }
    
    public function ver_p2($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->ver_p2($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }
/*
    public function agregar_p3($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->agregar_p3($id, $data['id_lote'], $data['estudiantes']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }
*/
    public function modificar_p3($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->modificar_p3($id, $data['id'], $data['estudiantes']);
        if($informe=='error')
            {
                $Mensaje='Atención: Los datos que intenta modificar son coincidentes con otro lote existente';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                if($informe=='error0')
                    {
                        $Mensaje='Atención: El lote ya se encuentra confirmado, por lo que no se pueden realizar modificaciones';
                        return response()->json([
                            'message' => $Mensaje,
                        
                        ], 300);
                    }
                else
                    {
                        return response()->json([
                            'success' => true,
                            'data'    => $informe,
                            'messages' => ''
                        ]);
                    }
                
                
                
               
            }

    }

    public function ver_p3($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->ver_p3($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }

    public function confirmar($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->confirmar($id, $data['id']);
        if($informe=='error')
            {
                $Mensaje='Atención: El lote aún no tiene confirmadas la totalidad de sus etapas.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]); 
            }
        

    }

    public function generar($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->generar($id, $data['id'], $data['id_usuario']);
        if($informe=='error')
            {
                $Mensaje='Atención: El lote aún no ha sido confirmado, por lo que no puede generarse.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                    'messages' => ''

                ]); 
            }

    }

    public function publicar($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->publicar($id, $data['id'], $data['id_usuario']);
        if($informe=='error')
            {
                $Mensaje='Atención: El lote aún no ha sido generado, por lo que no puede publicarse.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]); 
            }

    }

    public function republicar_lote($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->republicar_lote($id, $data['id']);
        if($informe=='error')
            {
                $Mensaje='Atención: El lote no ha posido ser republicado. Consulte en mesa de ayuda.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]); 
            }

    }

    public function republicar_comprobante($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->republicar_comprobante($id, $data['id']);
        if($informe=='error')
            {
                $Mensaje='Atención: El comprobante no ha posido ser republicado. Consulte en mesa de ayuda.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]); 
            }

    }

    public function ver($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->ver($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }

    public function detalle($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->detalle($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]); 

    }

    public function detalle_comprobante($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->detalle_comprobante($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]); 

    }

    public function borrar_comprobante($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->borrar_comprobante($id, $data['id_item'], $data['id_usuario']);
        if($informe=='error')
            {
                $Mensaje='Atención: El comprobante no ha podido ser eliminado. Consulte en mesa de ayuda.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]); 
            }

    }

    public function borrar_concepto($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->borrar_concepto($id, $data['id_item'], $data['id_usuario']);
        if($informe=='error')
            {
                $Mensaje='Atención: El concepto no ha podido ser eliminado. Consulte en mesa de ayuda.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]); 
            }

    }

    public function modificar_concepto($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->modificar_concepto($id, $data['id_item'], $data['descripcion'], $data['importe'], $data['id_usuario']);
        if($informe=='error')
            {
                $Mensaje='Atención: El concepto no ha podido ser modificado. Consulte en mesa de ayuda.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]); 
            }

    }

    public function agregar_concepto($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->agregar_concepto($id, $data['id_item'], $data['id_concepto'], $data['id_tipo_concepto'], $data['descripcion'], $data['importe'], $data['id_usuario']);
        if($informe=='error1')
            {
                $Mensaje='Atención: El concepto no ha podido ser agregado. No existe la codificación. Consulte en mesa de ayuda.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                if($informe=='error2')
                    {
                        $Mensaje='Atención: El concepto no ha podido ser agregado. Ya se encuentra cancelado. Consulte en mesa de ayuda.';
                        return response()->json([
                            'message' => $Mensaje,
                        
                        ], 300);
                    }
                else
                    {
                        return response()->json([
                            'success' => true,
                            'data'    => $informe,
                            'messages' => ''
                        ]); 
                    }
            }
       

    }

    public function facturas_emitidas($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->facturas_emitidas($id, $data['id']);
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]); 

    }
    public function republicar_factura($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->LotesService->republicar_factura($id, $data['id']);
        if($informe=='error')
            {
                $Mensaje='Atención: La factura no ha posido ser republicada. Consulte en mesa de ayuda.';
                return response()->json([
                    'message' => $Mensaje,
                   
                ], 300);
            }
        else
            {
                return response()->json([
                    'success' => true,
                    'data'    => $informe,
                    'messages' => ''
                ]); 
            }

    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    

}

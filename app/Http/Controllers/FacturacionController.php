<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacturacionService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;
use Afip;


class FacturacionController extends Controller
{

    private $FacturacionService;

    function __construct(FacturacionService $FacturacionService)
    {
        $this->FacturacionService = $FacturacionService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     
     public function listado_notas($id)

     {
 
        $data = $this->FacturacionService->listado_notas($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]); 
        
        
 
     }

     public function listado($id)

     {
 
        $data = $this->FacturacionService->listado($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]); 
        
        
 
     }

     public function pendientes_facturacion($id)

     {
 
        $data = $this->FacturacionService->pendientes_facturacion($id);
        return response()->json([
            'success' => true,
            'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'messages' => ''
        ]); 
        
        
 
     }

     public function reenviar_factura($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->FacturacionService->reenviar_factura($id, $data['id']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El comprobante no ha podido ser enviado. Reintente';
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
    public function reenviar_recibo($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->FacturacionService->reenviar_recibo($id, $data['id']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El comprobante no ha podido ser enviado. Reintente';
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

    public function reenviar_nota_c($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->FacturacionService->reenviar_nota_c($id, $data['id']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El comprobante no ha podido ser enviado. Reintente';
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

    public function reenviar_nota_d($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->FacturacionService->reenviar_nota_d($id, $data['id']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El comprobante no ha podido ser enviado. Reintente';
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



    public function estadisticas($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->FacturacionService->estadisticas($id, $data['id_empresa'], $data['desde'], $data['hasta']);
         return response()->json([
             'success' => true,
             'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
             'messages' => ''
         ]); 
 
     }

     public function ver_nota_credito($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->FacturacionService->ver_nota_credito($id, $data['numero'], $data['empresa']);
         return response()->json([
             'success' => true,
             //'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
             'data'    => $informe,
             'messages' => ''
         ]); 
 
     }

     public function ver_factura_emitida($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->FacturacionService->ver_factura_emitida($id, $data['numero'], $data['empresa']);
         return response()->json([
             'success' => true,
             //'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
             'data'    => $informe,
             'messages' => ''
         ]); 
 
     }

     public function generar_nota_credito($id, Request $request)
        {
            $data = $request->all();
            $informe = $this->FacturacionService->generar_nota_credito($id, $data['id_comprobante'], $data['id_usuario']); 
            if($informe=='error')
            {
                $Mensaje='Atención: La nota de Crédito no ha podido ser generada. Reintente';
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

        public function generar_nota_debito($id, Request $request)
        {
            $data = $request->all();
            $informe = $this->FacturacionService->generar_nota_debito($id, $data['id_comprobante'], $data['id_usuario']); 
            if($informe=='error')
            {
                $Mensaje='Atención: La nota de Débito no ha podido ser generada. Reintente';
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

        public function nota_credito($id, Request $request)
        {
            $data = $request->all();
            $informe = $this->FacturacionService->nota_credito($id, $data['id_comprobante']);
            return response()->json([
                'success' => true,
                'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                'messages' => ''
            ]); 

                
        }
    
        public function ver_modelo_factura($id, Request $request)

        {
    
            $data = $request->all();
            $informe = $this->FacturacionService->ver_modelo_factura($id, $data['id_pago']);
            return response()->json([
                'success' => true,
                'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                'messages' => ''
            ]); 
    
        }

        public function ver_modelo_facturas_diarias($id, Request $request)

        {
    
            $data = $request->all();
            $informe = $this->FacturacionService->ver_modelo_facturas_diarias($id, $data['fecha']);
            return response()->json([
                'success' => true,
                'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                'messages' => ''
            ]); 
    
        }

        public function generar_factura($id, Request $request)
        {
            $data = $request->all();
            $informe = $this->FacturacionService->generar_factura($id, $data['id_responsable'], $data['id_empresa'], $data['id_pto_vta'], $data['id_periodo'], $data['id_operacion'], $data['importe'], $data['conceptos'], $data['id_usuario'], $data['id_alumno']); 
            if($informe=='error')
            {
                $Mensaje='Atención: La factura no ha podido ser generada. Reintente';
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

        public function generar_lote_facturas($id, Request $request)
        {
            $data = $request->all();
            $informe = $this->FacturacionService->generar_lote_facturas($id, $data['id_usuario'], $data['lote']); 
            if($informe=='error')
            {
                $Mensaje='Atención: EL lote de facturas no ha podido ser generado. Reintente';
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

        public function cerrar_factura($id, Request $request)

        {
    
            $data = $request->all();
            $informe = $this->FacturacionService->cerrar_factura($id, $data['id_operacion']);
            return response()->json([
                'success' => true,
                'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                'messages' => ''
            ]); 
    
        }

        public function lotes_intereses($id)

        {
    
            $data = $this->FacturacionService->lotes_intereses($id);
                return response()->json([
                    'success' => true,
                    'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
                    'messages' => ''
                ]); 
    
        }

        
        public function modelo_lote_intereses($id, Request $request)

        {
    
            $data = $request->all();
            $informe = $this->FacturacionService->modelo_lote_intereses($id, $data['id_periodo'], $data['orden']);
            return response()->json([
                'success' => true,
                'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                'messages' => ''
            ]); 
    
        }

        public function ver_lote_intereses($id, Request $request)

        {
    
            $data = $request->all();
            $informe = $this->FacturacionService->ver_lote_intereses($id, $data['id_item']);
            return response()->json([
                'success' => true,
                'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                'messages' => ''
            ]); 
    
        }

        public function generar_lote_intereses($id, Request $request)

        {
    
            $data = $request->all();
            $informe = $this->FacturacionService->generar_lote_intereses($id, $data['id_periodo'], $data['orden'], $data['fecha'], $data['interes'], $data['arreglo'], $data['id_usuario']);
            if($informe=='error')
            {
                $Mensaje='Atención: El lote de intereses no ha podido ser generado. Reintente';
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

        public function tipos_comprobante($id)

            {
    
                $data = $this->FacturacionService->tipos_comprobante($id);
                return response()->json([
                    'success' => true,
                    'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
                    'messages' => ''
                ]); 
                
                
        
            }
    
        public function consulta_libro_iva($id, Request $request)

            {
        
                $data = $request->all();
                $informe = $this->FacturacionService->consulta_libro_iva($id, $data['id_empresa']);
                return response()->json([
                    'success' => true,
                    'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                    'messages' => ''
                ]); 
        
            }
        public function generacion_libro_iva($id, Request $request)
            {
                $data = $request->all();
                $informe = $this->FacturacionService->generacion_libro_iva($id, $data['arreglo'], $data['id_usuario'], $data['id_empresa']);
                
                
                if($informe=='error')
                {
                    $Mensaje='Atención: La selección de comprobantes no ha podido ser generada. Reintente';
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
        public function generacion_libro_iva_alicuotas($id, Request $request)
            {
                $data = $request->all();
                $informe = $this->FacturacionService->generacion_libro_iva_alicuotas($id, $data['arreglo'], $data['id_usuario'], $data['id_empresa']); 
                if($informe=='error')
                {
                    $Mensaje='Atención: La selección de comprobantes no ha podido ser generada. Reintente';
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

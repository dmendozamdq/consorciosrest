<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CajaService;
use Illuminate\Support\Facades\Validator;
use App\Models\Home;


class CajaController extends Controller
{

    private $CajaService;

    function __construct(CajaService $CajaService)
    {
        $this->CajaService = $CajaService;
    }

    /**
     * Obtiene el informe general con los datos de todas las tablas necesarias
     *
     * @return Json Response [success, data, messages]
     */
     
     public function movimientos_diarios($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->movimientos_diarios($id, $data['id'], $data['fecha']);
         return response()->json([
             'success' => true,
             'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
             'messages' => ''
         ]); 
 
     }

     public function movimientos_historicos($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->movimientos_historicos($id, $data['id']);
         return response()->json([
             'success' => true,
             'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
             'messages' => ''
         ]); 
 
     }

     public function borrar_pago($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->CajaService->borrar_pago($id, $data['id'], $data['id_usuario']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El pago no pude ser eliminado. Reintente';
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

            $data = $this->CajaService->listado($id);
            return response()->json([
                'success' => true,
                'data'    => $data,
                'messages' => ''
            ]);

        }
        public function listado_abiertas($id, Request $request)

        {

            $data = $request->all();

            $informe = $this->CajaService->listado_abiertas($id, $data['id_caja']);
            return response()->json([
                'success' => true,
                'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
                'messages' => ''
            ]);

        }
     public function enviar_comprobante($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->CajaService->enviar_comprobante($id, $data['id']);
        
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

    public function nueva_cobranza($id)

    {

        $data = $this->CajaService->nueva_cobranza($id);
        return response()->json([
            'success' => true,
            'data'    => $data,
            'messages' => ''
        ]);

    }

    public function comprobantes_pendientes($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->comprobantes_pendientes($id, $data['id']);
         return response()->json([
             'success' => true,
             'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
             'messages' => ''
         ]); 
 
     }

    public function recibir_cobranza($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->CajaService->recibir_cobranza($id, $data['id_item'], $data['id_responsable'], $data['observaciones'], $data['id_medio_pago'], $data['importe'], $data['factura'], $data['detalle_medio_pago'], $data['detalle_imputaciones'], $data['id_usuario']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El pago no ha podido ser procesado. Reintente';
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
    public function recibir_cobranza_efectivo($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->CajaService->recibir_cobranza_efectivo($id, $data['id_item'], $data['fecha'], $data['id_responsable'], $data['observaciones'], $data['id_medio_pago'], $data['importe'], $data['factura'], $data['detalle_imputaciones'], $data['id_usuario'], $data['recibo']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El pago no ha podido ser procesado. Reintente';
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

    public function recibir_cobranza_transferencia($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->CajaService->recibir_cobranza_transferencia($id, $data['id_item'], $data['fecha'], $data['id_responsable'], $data['observaciones'], $data['banco'], $data['referencia'], $data['id_medio_pago'], $data['importe'], $data['factura'], $data['detalle_imputaciones'], $data['id_usuario'], $data['recibo']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El pago no ha podido ser procesado. Reintente';
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

    public function recibir_cobranza_cheque($id, Request $request)
    {

        $data = $request->all();
        $informe = $this->CajaService->recibir_cobranza_cheque($id, $data['id_item'], $data['fecha'], $data['id_responsable'], $data['observaciones'], $data['banco'], $data['referencia'], $data['id_medio_pago'], $data['importe'], $data['factura'], $data['detalle_imputaciones'], $data['id_usuario'], $data['recibo']);
        
        if($informe=='error')
            {
                $Mensaje='Atención: El pago no ha podido ser procesado. Reintente';
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

    public function test_facturante($id)

    {

        $data = $this->CajaService->test_facturante($id);
        return response()->json([
            'success' => true,
            //'data'    => mb_convert_encoding($data, 'UTF-8', 'UTF-8'),
            'data'    => $data,
            'messages' => ''
        ]);

    }

    public function detalle_cobranza($id, Request $request)

    {

        $data = $request->all();
        $informe = $this->CajaService->detalle_cobranza($id, $data['periodo']);
        
        return response()->json([
            'success' => true,
            'data'    => $informe,
            'messages' => ''
        ]);

    }

    public function detalle_medios_pago($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->detalle_medios_pago($id, $data['periodo']);
         return response()->json([
             'success' => true,
             'data'    => $informe,
             'messages' => ''
         ]); 
 
     }

     public function planilla_caja($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->planilla_caja($id, $data['id'], $data['fecha']);
         return response()->json([
             'success' => true,
             //'data'    => $informe,
             'data'    => mb_convert_encoding($informe, 'UTF-8', 'UTF-8'),
             'messages' => ''
         ]); 
 
     }

     public function apertura_caja($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->apertura_caja($id, $data['id'], $data['saldo_inicial'], $data['id_usuario']);
         return response()->json([
             'success' => true,
             'data'    => $informe,
             'messages' => ''
         ]); 
 
     }

     public function cierre_caja($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->cierre_caja($id, $data['id'], $data['id_usuario']);
         return response()->json([
             'success' => true,
             'data'    => $informe,
             'messages' => ''
         ]); 
 
     }
     public function egreso_caja($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->egreso_caja($id, $data['id'], $data['descripcion'], $data['importe'], $data['id_usuario']);
         return response()->json([
             'success' => true,
             'data'    => $informe,
             'messages' => ''
         ]); 
 
     }

     public function borrar_egreso_caja($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->borrar_egreso_caja($id, $data['id'], $data['id_usuario']);
         return response()->json([
             'success' => true,
             'data'    => $informe,
             'messages' => ''
         ]); 
 
     }

     public function cambio_estado_facturacion($id, Request $request)

     {
 
         $data = $request->all();
         $informe = $this->CajaService->cambio_estado_facturacion($id, $data['id']);
         return response()->json([
             'success' => true,
             'data'    => $informe,
             'messages' => ''
         ]); 
 
     }



}

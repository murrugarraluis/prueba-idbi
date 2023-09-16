<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Articulo;
use App\Models\User;
use App\Mail\ComprobanteUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use SimpleXMLElement;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComprobanteController extends Controller
{
    public function index()
    {
        return Comprobante::paginate(10); // Devuelve comprobantes paginados.
    }

    public function store(Request $request)
    {
        // Validar que el archivo XML esté presente
        $request->validate(['xml_file' => 'required|array']);
        $xmlFiles = $request->file('xml_file');

        // Si solo hay un archivo, conviértelo en un array
        if (!is_array($xmlFiles)) {
            $xmlFiles = [$xmlFiles];
        }

        $comprobantes = [];

        try {
            foreach ($xmlFiles as $xmlFile) {
                $xml_content = file_get_contents($xmlFile->getRealPath());

                // Convertir el contenido XML en un objeto
                $xml = new SimpleXMLElement($xml_content);

                // Extraer datos del XML
                $issuer_name = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0];
                $issuer_document_type = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
                $issuer_document_number = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

                $receiver_name = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
                $receiver_document_type = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
                $receiver_document_number = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

                $total_amount = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];

                // Crear un array con los datos extraídos
                $data = [
                    'issuer_name' => $issuer_name,
                    'issuer_document_type' => $issuer_document_type,
                    'issuer_document_number' => $issuer_document_number,
                    'receiver_name' => $receiver_name,
                    'receiver_document_type' => $receiver_document_type,
                    'receiver_document_number' => $receiver_document_number,
                    'total_amount' => $total_amount,
                    'user_id' => auth()->id(), // Establecer el usuario autenticado
                ];

                $comprobante = Comprobante::create($data);

                // Registro de sus artículos
                foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
                    $nombre = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0];
                    $cantidad = (float) $invoiceLine->xpath('cbc:InvoicedQuantity')[0];
                    $precio = (float) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0];

                    $articulo = new Articulo([
                        'nombre' => $nombre,
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                    ]);

                    $comprobante->articulos()->save($articulo);
                }
                $comprobante->load('articulos');
                $comprobantes[] = $comprobante;
            }
        } catch (\Exception $e) {
            // Manejar errores durante el procesamiento
            return response()->json(['mensaje' => 'Error en el procesamiento del archivo XML'], 400);
        }

        try {
            // Enviar notificación por correo electrónico
            Mail::to(auth()->user()->email)->send(new ComprobanteUploaded($comprobantes, auth()->user()));
            $correo_enviado = true;
        } catch (\Exception $e) {
            // Si hay un error al enviar el correo, puedes manejarlo aquí
            $correo_enviado = false;
        }

        $respuesta = [
            'comprobantes' => $comprobantes,
            'correo_enviado' => $correo_enviado,
            'mensaje' => $correo_enviado ? 'Comprobantes registrados y correo enviado con éxito' : 'Comprobantes procesados, pero ocurrió un error al enviar el correo'
        ];

        return response()->json($respuesta, 201);
    }



    public function show($id)
    {
        try {
            return Comprobante::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['mensaje' => 'Comprobante no encontrado'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $comprobante = Comprobante::findOrFail($id);
            $comprobante->delete();
        } catch (ModelNotFoundException $e) {
            return response()->json(['mensaje' => 'Comprobante no encontrado'], 404);
        }
        return response()->json(['mensaje' => 'Comprobante eliminado con éxito'], 200);
    }

    public function getTotalAmountByArticle($nombreArticulo)
    {
        $user = auth()->user();
        $userId = $user->id;
        // Obtener todos los comprobantes del usuario
        $comprobantes = Comprobante::where('user_id', $userId)->with('articulos')->get();

        if ($comprobantes->isEmpty()) {
            return response()->json(['mensaje' => 'No se encontraron comprobantes para este usuario'], 404);
        }

        $montoTotal = 0;

        // Iterar a través de los comprobantes y los artículos
        foreach ($comprobantes as $comprobante) {
            foreach ($comprobante->articulos as $articulo) {
                // Si el nombre del artículo coincide, sumar al monto total
                if ($articulo->nombre == $nombreArticulo) {
                    $montoTotal += $articulo->cantidad * $articulo->precio;
                }
            }
        }

        if ($montoTotal == 0) {
            return response()->json(['mensaje' => "No se encontraron artículos con el nombre '{$nombreArticulo}'"], 404);
        }

        // Obtener el nombre del usuario
        $user = User::find($userId);

        // Crear un mensaje personalizado
        $mensaje = "El monto total para el artículo '{$nombreArticulo}' del usuario '{$user->name}' es: {$montoTotal}";

        // Devolver la respuesta
        return response()->json(['mensaje' => $mensaje, 'montoTotal' => $montoTotal]);
    }


    public function getTotalAmount()
    {
        // Obtener el nombre del usuario
        $user = auth()->user();
        $userId = $user->id;
        
        if (!$user) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $totalAmount = Comprobante::where('user_id', $userId)->sum('total_amount');

        if ($totalAmount == 0) {
            return response()->json(['mensaje' => "No se encontraron comprobantes para el usuario '{$user->name}'"], 404);
        }

        // Crear un mensaje personalizado
        $mensaje = "El monto total acumulado por todos los comprobantes del usuario '{$user->name}' es: {$totalAmount}";

        return response()->json(['mensaje' => $mensaje, 'total_amount' => $totalAmount]);
    }
}

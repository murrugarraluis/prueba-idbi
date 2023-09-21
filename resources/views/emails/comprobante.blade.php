<!DOCTYPE html>
<html>
<head>
    <title>Comprobantes Subidos</title>
</head>
<body>
    <p>Hola</p>
    <h1>Estimado {{ $user->name }},</h1>
    <p>Hemos recibido tus comprobantes con los siguientes detalles:</p>
    @if(count($comprobantes) > 0)
    <div>
        <h2>Cargados Correctamente</h2>
        @foreach ($comprobantes as $comprobante)
            <ul>
                <li>Nombre del Emisor: {{ $comprobante->issuer_name }}</li>
                <li>Tipo de Documento del Emisor: {{ $comprobante->issuer_document_type }}</li>
                <li>Número de Documento del Emisor: {{ $comprobante->issuer_document_number }}</li>
                <li>Nombre del Receptor: {{ $comprobante->receiver_name }}</li>
                <li>Tipo de Documento del Receptor: {{ $comprobante->receiver_document_type }}</li>
                <li>Número de Documento del Receptor: {{ $comprobante->receiver_document_number }}</li>
                <li>Monto Total: {{ $comprobante->total_amount }}</li>
            </ul>
        @endforeach
    </div>
    @endif
    @if(count($comprobantes_failed) > 0)
    <div>
        <h2>No Cargados</h2>
        @foreach ($comprobantes_failed as $comprobante)
            <ul>
                <li>Nombre del Emisor: {{ $comprobante->issuer_name }}</li>
                <li>Tipo de Documento del Emisor: {{ $comprobante->issuer_document_type }}</li>
                <li>Número de Documento del Emisor: {{ $comprobante->issuer_document_number }}</li>
                <li>Nombre del Receptor: {{ $comprobante->receiver_name }}</li>
                <li>Tipo de Documento del Receptor: {{ $comprobante->receiver_document_type }}</li>
                <li>Número de Documento del Receptor: {{ $comprobante->receiver_document_number }}</li>
                <li>Monto Total: {{ $comprobante->total_amount }}</li>
                <li>Razón de falla al cargar: {{$comprobante->failed_message}}</li>
            </ul>
        @endforeach
    </div>
    @endif
    <p>¡Gracias por usar nuestro servicio!</p>
</body>
</html>

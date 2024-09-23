<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo mensaje de contacto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #51545e;
            margin: 0;
            padding: 0;
            background-color: #f4f4f7;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #eaeaec;
        }
        .email-wrapper {
            width: 90%;
            margin: 0 auto;
            background-color: #f4f4f7;
            padding: 20px;
            border-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
        .text-center {
            text-align: center;
        }
        .email-content {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            margin: 0 auto;
            border: 1px solid #eaeaec;
        }
        .email-header {
            background-color: #333333;
            padding: 20px;
            text-align: center;
            color: #ffffff;
        }
        .email-body {
            padding: 20px;
        }
        .email-body p {
            margin: 0 0 15px;
        }
        .email-footer {
            text-align: center;
            padding: 20px;
            background-color: #f4f4f7;
            color: #a8aaaf;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <table class="email-wrapper" style="width: 90%; margin: 0 auto;">
        <tr>
            <td>
                <table class="email-content">
                    <!-- Header -->
                    <tr>
                        <td class="email-header text-center" style="text-align: center;" center>
                            <img src="{{ asset('logo.svg') }}" alt="Logo" width="400">
                            <h2>Nuevo Mensaje de Contacto</h2>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td class="email-body">
                            <p><strong style="width: 150px;">Nombre:</strong> {{ $data['name'] }} <br />
                            <strong style="width: 150px;">Teléfono:</strong> {{ $data['phone'] }}<br />
                            <strong style="width: 150px;">Correo electrónico:</strong> {{ $data['email'] }}<br />
                            <strong style="width: 150px;">Mensaje:</strong><br />
                            {{ $data['message'] }}</p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p>Este es un correo generado automáticamente. Por favor, no respondas a este mensaje.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

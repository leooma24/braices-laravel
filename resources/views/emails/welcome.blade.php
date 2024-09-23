<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
</head>
<body>
    <h1>¡Bienvenido, {{ $user->name }}!</h1>
    <p>Gracias por registrarte en nuestra plataforma. Esperamos que disfrutes de nuestros servicios.</p>
    <p>Haz clic en el siguiente enlace para verificar tu correo electrónico:</p>
    <a href="{{ $verificationUrl }}">Verificar mi correo</a>
    <p>Saludos,</p>
    <p>El equipo de {{ config('app.name') }}</p>
</body>
</html>

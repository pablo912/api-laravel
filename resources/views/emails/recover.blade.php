@component('mail::message')
# Hola {{$user->name}}

Está recibiendo este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para su cuenta.

@component('mail::button', ['url' => 'http://localhost:4200/reset-password/'.$user->remember_token  ])

    restablecer contraseña

@endcomponent

Este enlace de restablecimiento de contraseña caducará en 60 minutos.

Si no solicitó un restablecimiento de contraseña, no se requiere ninguna otra acción.


Gracias,<br>
{{ config('app.name') }}
@endcomponent
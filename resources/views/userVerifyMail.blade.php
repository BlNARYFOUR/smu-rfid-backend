<div style="font-family: sans-serif">
    Dear {{ $name }},
    <br />
    <br />
    Please click the link below to verify your registration.
    <br />
    <br />
    <br />
    <a style="padding: 10px; background-color: #0c5460; border-radius: 5px; color: white; text-decoration: none; text-transform: uppercase;" href="{{env('FRONTEND_URL')}}verify?token={{$verificationCode}}">Verify</a>
    <br />
    <br />
    <br />
    If the button doesn't work, you can try copying following link into your browser:
    <br />
    <br />
    <em>{{env('FRONTEND_URL')}}verify?token={{$verificationCode}}</em>
    <br />
    <br />
    <br />
    Thank you for choosing our services,
    <br />
    <br />
    SMU RFID VMS
</div>
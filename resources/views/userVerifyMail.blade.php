<h2>Hello </h2>

<p>
    Dear {{ $name }}
    <br />
    <br />
    Please click the link below to verify your registration.
    <br />
    <br />
    <a href="{{env('FRONTEND_URL')}}?verify=1&token={{$verificationCode}}">here</a>
</p>
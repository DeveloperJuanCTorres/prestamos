@extends('layouts.app')

@section('content')


    <!-- Start wrapper-->
    <div id="wrapper">

        <div class="loader-wrapper d-flex justify-content-center align-items-center" style="height: 100vh;">
            <!-- <div class="lds-ring"><div></div><div></div><div></div><div></div></div></div> -->
            <div class="card card-authentication1 d-flex m-auto my-5">
                <div class="card-body">
                    <div class="card-content p-2">
                        <div class="text-center">
                            <img src="assets/images/credianro.png" width="100" alt="logo icon">
                        </div>
                    <div class="card-title text-uppercase text-center">CREDI-ANRO</div>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group">
                            <label for="exampleInputUsername" class="sr-only">Usuario</label>
                            <div class="position-relative has-icon-right">
                                <input type="email" id="email" class="form-control input-shadow @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Ingresar Usuario">
                               
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                                <div class="form-control-position">
                                    <i class="icon-user"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword" class="sr-only">Password</label>
                            <div class="position-relative has-icon-right">
                                <input type="password" id="password" name="password" class="form-control input-shadow @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="Ingresar Password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                                <div class="form-control-position" style="cursor: pointer;" onclick="togglePassword()">
                                    <i class="fa fa-eye" id="toggleIcon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <div class="icheck-material-white">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }} />
                                    <label for="remember">Recordarme</label>
                                </div>
                            </div>
                            <!-- <div class="form-group col-6 text-right">
                                <a href="reset-password.html">Olvide contraseña</a>
                            </div> -->
                        </div>
                        <button type="submit" class="btn btn-light btn-block">Iniciar Sesión</button>
                    
                    </form>
                </div>
            </div>
            <!-- <div class="card-footer text-center py-3">
                <p class="text-warning mb-0">Do not have an account? <a href="register.html"> Sign Up here</a></p>
            </div> -->
        </div>
        
       
        
    </div>
    <!--wrapper-->
    
    @push('script')
    <script>
        function togglePassword() {
            const input = document.getElementById("password");
            const icon = document.getElementById("toggleIcon");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
    @endpush

@endsection

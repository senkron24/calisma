<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Luxetv - Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <!-- App css -->
		<link href="assets/css/icons.css" rel="stylesheet" type="text/css" />
        		<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.css" rel="stylesheet" type="text/css" />
        		<style>
			.g-recaptcha {
				display: inline-block;
			}
		</style>
    </head>
    <body class="authentication-bg authentication-bg-pattern">
        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        						<br>
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-0 text-center"> WELCOME </h4>
								<br><br>
								                                <form action="./login.php" method="POST" data-parsley-validate="" id="login_form">
                                    <input type="hidden" name="referrer" value="/dashboard.php" />
                                                                        <div class="form-group mb-3" id="username_group">
                                        <label for="username">Username</label>
                                        <input class="form-control" autocomplete="off" type="text" id="username" name="username" required="" data-parsley-trigger="change" placeholder="Enter Your Username">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="password">Password</label>
                                        <input class="form-control" autocomplete="off" type="password" required data-parsley-trigger="change" id="password" name="password" placeholder="Enter Your Password">
                                    </div>
									<div class="form-group mb-0">
										<label for="show-password" class="field__toggle">
				                            <input type="checkbox" id="show-password" class="field__toggle-input" />
				                            Show password
			                            </label>
                                    </div>
																        <br>
                                    <div class="form-group mb-0 text-center">
                                        <button class="btn_login btn-block btn-success" type="submit" id="login_button">LOGIN</button>
                                    </div>
                                </form>
							</div>
						</div>	
								                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/parsleyjs/parsley.min.js"></script>
        <script src="assets/js/app.min.js?rid=0"></script>
		        <script>
        $(document).ready(function() {
            if (window.location.hash.substring(0,1) == "#") {
                $("#username_group").hide();
                $("#username").val(window.location.hash.substring(1));
                $("#login_form").attr('action', './login.php#' + window.location.hash.substring(1));
                $("#login_button").html("LOGIN AS " + window.location.hash.substring(1).toUpperCase());
            }
        });
        </script>
		<script type="text/javascript">
		var toggle = document.querySelector( "#show-password" );
		toggle.addEventListener( "click", handleToggleClick, false );

		function handleToggleClick( event ) {
 
			if ( this.checked ) {
 
				console.warn( "Change input 'type' to: text" );
				password.type = "text";
 
			} else {
 
				console.warn( "Change input 'type' to: password" );
				password.type = "password";
 
			}
 
		}
 
	</script>
    </body>
</html>
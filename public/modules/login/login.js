disableDocumentKeyboardInput();

$(() => {
	hideLoadingGifOverlay();

	$("#emailLogin").focus();

	// --------------------------------------------------

	$("#signup").click((event) => {
		event.preventDefault();

		$("#loginSection").fadeOut("fast", () => {
			$(".alert").addClass("d-none");
			$("#registerSection").fadeIn("fast", () => {
				$("#firstnameRegister").focus();
			});
		});
	});

	// --------------------------------------------------

	$("#signin").click(function () {
		$("#registerSection").fadeOut("fast", () => {
			$("#loginSection").fadeIn("fast", () => {
				$("#emailLogin").focus();
			});
		});
	});

	// --------------------------------------------------

	$("form[name='login']").validate({
		rules: {
			emailLogin: {
				required: true,
				email: true,
			},
			passwordLogin: {
				required: true,
			},
		},
		messages: {
			emailLogin: "Please enter a valid email address",

			passwordLogin: {
				required: "Please enter password",
			},
		},
		submitHandler: function (form) {
			form.submit();
		},
	});

	// --------------------------------------------------

	$.validator.addMethod(
		"passwordMatch",
		function (value, element) {
			let password = $("#passwordRegister").val();
			let passwordConfirmation = $("#confirmPasswordRegister").val();

			return password === passwordConfirmation;
		},
		"The passwords do not match"
	);

	// --------------------------------------------------

	$("form[name='registration']").validate({
		rules: {
			firstnameRegister: "required",
			lastnameRegister: "required",
			emailRegister: {
				required: true,
				email: true,
				remote: {
					url: "modules/login/handlers/check_unique_email.php",
					type: "GET",
					data: {
						Code: function () {
							return $("#emailRegister").val();
						},
					},
				},
			},
			passwordRegister: {
				required: true,
				minlength: 5,
			},
			confirmPasswordRegister: {
				required: true,
				passwordMatch: true,
			},
		},

		messages: {
			firstnameRegister: "Please enter your firstname",
			lastnameRegister: "Please enter your lastname",
			emailRegister: {
				email: "Please enter a valid email address",
				remote: "An account already exists linked to this email address",
			},
			passwordRegister: {
				required: "Please provide a password",
				minlength: "Your password must be at least 5 characters long",
			},
			confirmPasswordRegister: {
				required: "Please provide a password",
				passwordMatch: "The passwords do not match",
			},
		},

		submitHandler: function (form) {
			form.submit();
		},
	});
});

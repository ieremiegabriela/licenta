disableDocumentKeyboardInput();

$(() => {
	hideLoadingGifOverlay();

	// --------------------------------------------------

	$("#signup").click((event) => {
		event.preventDefault();

		$("#loginSection").fadeOut("fast", () => {
			$(".alert").addClass("d-none");
			$("#registerSection").fadeIn("fast");
		});
	});

	$("#signin").click(function () {
		$("#registerSection").fadeOut("fast", () => {
			$("#loginSection").fadeIn("fast");
		});
	});

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

	$.validator.addMethod(
		"passwordMatch",
		function (value, element) {
			let password = $("#passwordRegister").val();
			let passwordConfirmation = $("#confirmPasswordRegister").val();

			return password === passwordConfirmation;
		},
		"The passwords do not match"
	);

	$.validator.addMethod(
		"uniqueEmail",
		function (value, element) {
			checkUniqueEmail(email).success((data) => {
				let jsonObj = $.parseJSON(data);
			});

			return false;
		},
		"An account already exists linked to this email address"
	);

	$("form[name='registration']").validate({
		rules: {
			firstnameRegister: "required",
			lastnameRegister: "required",
			emailRegister: {
				required: true,
				email: true,
				uniqueEmail: true,
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
				uniqueEmail: "An account already exists linked to this email address",
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

// BEGIN - AJAX CALLS -------------------------------

function checkUniqueEmail(email) {
	let request = $.ajax({
		url: "modules/login/handlers/check_unique_email.php",
		data: {
			email: email,
		},
		type: "POST",
		success: () => {},
	});

	return request;
}

// END - AJAX CALLS ---------------------------------
